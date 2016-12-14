<?php
require_once 'Zend/Db/Table.php';

class TemplatesTable extends Zend_Db_Table
{
    protected $_name = 'templates';
    protected $_primary = 'id';
    public $error = '';

    public function findWithName($name)   
    {
        $db = $this->getAdapter();
        $where = $db->quoteInto("name = ?", $name);
        
        return $this->fetchRow($where);
    }
}