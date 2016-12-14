<?php
require_once 'Zend/Db/Table.php';

class PageTable extends Zend_Db_Table
{
	const TABLE = 'page';//主数据表
    protected $_name = 'page';
    protected $_primary = 'pid';
    public $error = '';
    private $_fields = array (
        'name' => '名称',
        'parent' => '父栏目',
        'template' => '模板',
        'url' => '地址',
        'title' => '标题',
        'keywords' => '关键字',
        'description' => '简介',
    );
    
    public function __construct($db=NULL)
    {
    	if(isset($db))
    	{
    		Zend_Db_Table_Abstract::setDefaultAdapter($db);
    		$this->_db=$db;
    	}
    }
    public function insert(array $data)
    {
        foreach ($data as $key=> $field) {				
            if ('' === $data[$key]) {
                $this->error = $this->_fields[$key] . ' 不能为空';			
                return false;
            }
        }
        
        if (!Util::checkUrl($data['url'])) {
            $this->error = 'url地址不合法，请不要包含styles,scripts,images,img等关键字';
            return false;
        }
        
        return parent::insert($data);
    }
    
    public function edit(array $data, $where)
    {
		foreach ($data as $key=> $field) {				
            if ('' === $data[$key]) {
                $this->error = $this->_fields[$key] . ' 不能为空';			
                return false;
            }
        }
        
        if (!Util::checkUrl($data['url'])) {
            $this->error = 'url地址不合法，请不要包含styles,scripts,images,img等关键字';
            return false;
        }
                
        return parent::update($data, $where);
    }
    
    public function getTree($parent = 0)
    {
        $pages = $this->fetchAll(null, 'pid ASC')->toArray();

        $tree = array();
        $p    = array();        
        foreach ($pages as $page) {
            $_id  = $page['pid'];
            $_pid  = $page['parent'];
            $_data = $page;

            if (isset($p[$_id]) && !$p[$_id]['data']) {           
                $p[$_id]['data'] = $_data;
            }
            
            if ($_pid == 0) {
                $n = count($tree);
                
                if (isset($p[$_id])) {               
                    $tree[$n] = & $p[$_id];
                } else {
                    $tree[$n] = array ('data' => $_data, 'childs' => array());              
                }
                
                $p[$_id] = & $tree[$n];

                continue;
            }
            
            if ($_pid && !isset($p[$_pid])) {
                $p[$_pid] = array ('data' => null, 'childs' => array());
            }
            
            $n = count($p[$_pid]['childs']);
            $childs = isset($p[$_id]) ? $p[$_id]['childs'] : array();
            $p[$_pid]['childs'][$n] = array ('data' => $_data, 'childs' => $childs);

            $p[$_id] = & $p[$_pid]['childs'][$n];
        }
        
        if ($parent) {
            return $p[ $parent ]['childs'];
        }

        return $tree;
    }
    
    /**
     * Get flat tree with depth
     *
     * @param reference $tree
     * @param array $items
     * @param integer $depth
     */
    public function getFlatTree(&$tree, $items, $depth = 0)
    {
        if (!is_array($tree)) {
            $tree = array();
        }
        
        foreach ($items as $item) {
            $item['data']['_depth'] = $depth;
            array_push($tree, $item['data']);
            if ($item['childs']) {
                $this->getFlatTree($tree, $item['childs'], $depth + 1);
            }
        }
    }
    
    /**
     * Get pages tree as select options array
     *
     * @return array
     */
    public function toSelectOptions($tips = '')
    {
        $tree = $this->getTree();
        $this->getFlatTree($flat_tree, $tree);
        
        $options = array();
        if ($tips) {
            $options[0] = $tips;
        }
        
        foreach ($flat_tree as &$item) {
            $label = str_repeat('--', $item['_depth']) . ($item['_depth'] ? ' ' : '') . $item['name'];
            $options[$item['pid']] = $label;
        }        
        
        return $options;
    }
    
    //
    public function findWithName($name)   
    {
        $db = $this->getChannelDbAdapter();
        $where = $db->quoteInto("name = ?", $name);
        
        return $this->fetchRow($where);
    }
    #add by youshixing 2009-03-05 按级别获取栏目列表
	public static function getAllList($db,$parent_id=0,$level=0)
	{
		$stmt=$db->query('SELECT * FROM '.self::TABLE.' WHERE parent='.$parent_id);
		$arr=array();
		while($row=$stmt->fetch())
		{
			$row['level']=$level;
			$arr[]=$row;
			$arr2=self::getAllList($db,$row['pid'],($level+1));
			$arr=array_merge($arr,$arr2);
		}
		return $arr;
	}
	//获取用于select的列表
	public static function getOptions($db,$firstOption='请选择栏目')
	{
		$options=array();
		if(!empty($firstOption))$options=array(0 => $firstOption);
		$all_list=self::getAllList($db);
		foreach($all_list as $value)
		{
			$options[$value['pid']]=str_repeat('---',$value['level']).$value['name'].'('.$value['pid'].')';
		}
		return $options;
	}
    public function checkUrlExist($url,$id=NULL)
    {
        $select = $this->select()->from($this->_name, 'COUNT(*)')->where('url=?',$url);
        !isset($id) OR $select->where('pid!='.$id);
        return $this->_db->fetchOne($select);
    }
}