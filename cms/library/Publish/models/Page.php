<?php
require_once 'Zend/Db/Table.php';

class PageTable extends Zend_Db_Table
{
    protected $_name = 'page';
    protected $_primary = 'pid';


    public function findWithName($name)   
    {
        $db = $this->getAdapter();
        $where = $db->quoteInto("name = ?", $name);
        
        return $this->fetchRow($where);
    }
}