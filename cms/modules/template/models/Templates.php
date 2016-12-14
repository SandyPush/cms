<?php
require_once 'Zend/Db/Table.php';

class TemplatesTable extends Zend_Db_Table
{
    protected $_name = 'templates';
    protected $_primary = 'id';
    public $error = '';
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
        if (empty($data['name']) || empty($data['content'])) {
            $this->error = '请填写完整';
            return false;
        }
        
        if ($this->findWithName($data['name'])) {
            $this->error = '数据库中已存在一个名为' . $data['name'] . '的模板';
            return false;
        }
                
        return parent::insert($data);
    }
    
    public function edit(array $data, $where)
    {
        if (empty($data['name']) || empty($data['content'])) {
            $this->error = '请填写完整';
            return false;
        }
        
        $db = $this->getAdapter();
        if ($this->fetchRow($db->quoteInto("name = ?", $data['name']) . ' AND id != ' . $data['id'])) {
            $this->error = '数据库中已存在一个名为"' . $data['name'] . '"的模板';
            return false;
        }

        unset($data['id']);
        
        return parent::update($data, $where);
    }
    
    public function count()
    {
        return $this->_db->fetchOne($this->select()->from($this->_name, 'COUNT(*)'));
    }
    
    public function toSelectOptions($tips = '', $order = 'id DESC')
    {
        $options = array();
        if ($tips) {
            $options[0] = $tips;
        }
                
        $templates = $this->fetchAll(null, $order);
        foreach ($templates as $tpl) {
            $options[$tpl->id] = $tpl->name;
        }
        
        return $options;
    }
    
    //
    public function findWithName($name)   
    {
        $db = $this->getAdapter();
        $where = $db->quoteInto("name = ?", $name);
        
        return $this->fetchRow($where);
    }
    public function typeArray()
    {
    	return array(1=>'文章',2=>'专题',3=>'栏目');
    }
    public function fetchByType($type)
    {
    	$templates=array();
    	$sql=$this->_db->select()->from($this->_name,array('id','name'))->where('type=?',$type)->__toString();
    	$query=$this->_db->query($sql);
    	while($row=$query->fetch())$templates[$row['id']]=$row['name'];
    	return $templates;
    }
}