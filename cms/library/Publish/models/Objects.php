<?php
require_once 'Zend/Db/Table.php';

class ObjectsTable extends Zend_Db_Table
{
    protected $_name = 'objects';
    protected $_primary = 'oid';
        
    //
    public function findWithName($name)   
    {
        $db = $this->getAdapter();
        $where = $db->quoteInto("name = ?", $name);
        
        return $this->fetchRow($where);
    }
}