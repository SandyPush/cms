<?php
require_once 'Zend/Db/Table.php';

class ContentareaTable extends Zend_Db_Table
{
    protected $_name = 'contentarea';
    protected $_primary = 'aid';
    
    function fetchData($oid, $pid='')
    {
        $data = array();

        $fields = $this->fetchAll("oid = $oid AND pid = '".$pid."'");
        if (!$fields) {
            return $data;
        }
       
        foreach ($fields->toArray() as $f) {
            $data[$f['name']] = $f['value'];
        }
        
        return $data;
    }
}