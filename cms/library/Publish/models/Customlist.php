<?php
require_once 'Zend/Db/Table.php';

class CustomlistTable extends Zend_Db_Table
{
    protected $_name = 'customlist';
    protected $_primary = 'id';
    
    public function fetchData($oid = 0, $pid = '')
    {
        $select = $this->select()->from($this->_name, '*')
            ->where("oid = $oid AND pid = '$pid'")
            ->order('id DESC')
            ->limit(1);
        
        return $this->fetchRow($select);
    }
}