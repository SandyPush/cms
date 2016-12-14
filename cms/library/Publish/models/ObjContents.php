<?php
require_once 'Zend/Db/Table.php';

class ObjContentsTable extends Zend_Db_Table
{
    protected $_name = 'obj_contents';
    protected $_primary = 'oid';
    
    public function fetchContents($oid, $pageid)
    {
        return $this->fetchRow("oid = $oid AND pageid = '$pageid'");
    }
}