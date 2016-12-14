<?php
require_once 'Zend/Db/Table.php';

class ObjectsTable extends Zend_Db_Table
{
    protected $_name = 'objects';
    protected $_primary = 'oid';
    public $error = '';
    
    public function insert(array $data)
    {
        if (empty($data['name'])) {
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
        if (empty($data['name'])) {
            $this->error = '请填写完整';
            return false;
        }
        
        $db = $this->getAdapter();
        if ($this->fetchRow($db->quoteInto("name = ?", $data['name']) . ' AND oid != ' . $data['oid'])) {
            $this->error = '数据库中已存在一个名为"' . $data['name'] . '"的模板';
            return false;
        }

        unset($data['oid']);
        
        return parent::update($data, $where);
    }
    
    public function count($where = null)
    {
        $select = $this->select()->from($this->_name, 'COUNT(*)');
        if ($where) {
            $select = $select->where($where);
        }
        
        return $this->_db->fetchOne($select);
    }
        
    //
    public function findWithName($name)   
    {
        $db = $this->getAdapter();
        $where = $db->quoteInto("name = ?", $name);
        
        return $this->fetchRow($where);
    }
}