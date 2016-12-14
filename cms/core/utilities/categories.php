<?php
/**
 * unlimited categories class
 * 
 * @author legend
 * 
 * demo
   <?php
   require_once ROOT_PATH . 'core/utilities/categories.php';

   $db = Zend_Db::factory('PDO_MYSQL', $_DB_PARAMS);
        
   $params = array (
	   'db' => $db, // 数据库连接
	   'table' => 'categories', // 分类表名
	   'primary' => 'cid', // 主键
	   'parent' => 'pid', // 父分类字段
	   'childs' => 'childs', // 子孙列表，逗号分隔
	   'orderby' => 'position' // 排序依据，可省略   
   );
        
   $categories = new Categories($params);

   dump($categories->getTree());
 */
class Categories
{
	public $params = array();
	public $db;
	private $p;
	
	/**
	 * constructor
	 *
	 * @param array $params
	 */
	public function __construct($params = array())
	{
		$this->params = $params;
		
		$this->db = $params['db'];
	}
	
	public function getTree($parent = '')
	{
        $tree = array();
        $p    = array();

        $params = $this->params;
        
        $sql = "SELECT * FROM $params[table] ";
        //$sql .= $parent ? "WHERE params[parent] = '$parent' " : '';
        $sql .= "ORDER BY $params[orderby]";
        
        $query = $this->db->query($sql);
        while ($cate = $query->fetch()) {
            $_cid  = $cate[ $params['primary'] ];
            $_pid  = $cate[ $params['parent'] ];
            $_data = $cate;

            if (isset($p[$_cid]) && !$p[$_cid]['data']) {           
                $p[$_cid]['data'] = $_data;
            }
            
            if ($_pid == 0) {
                $n = count($tree);
                
                if (isset($p[$_cid])) {               
                    $tree[$n] = & $p[$_cid];
                } else {
                    $tree[$n] = array ('data' => $_data, 'childs' => array());              
                }
                
                $p[$_cid] = & $tree[$n];

                continue;
            }
            
            if ($_pid && !isset($p[$_pid])) {
                $p[$_pid] = array ('data' => null, 'childs' => array());
            }
            
            $n = count($p[$_pid]['childs']);
            $childs = isset($p[$_cid]) ? $p[$_cid]['childs'] : array();
            $p[$_pid]['childs'][$n] = array ('data' => $_data, 'childs' => $childs);

            $p[$_cid] = & $p[$_pid]['childs'][$n];
        }        
       
        if ($parent) {
            return $p[ $parent ]['childs'];
        }

        return $tree;
	}
	
	public function get($primary)
	{
	   return $this->db->fetchOne("SELECT * FROM $this->parms[table] WHERE $this->parmas[primary] = ?", $primary);
	}

}