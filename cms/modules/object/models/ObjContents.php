<?php
require_once 'Zend/Db/Table.php';

class ObjContentsTable extends Zend_Db_Table
{
    protected $_name = 'obj_contents';
    protected $_primary = 'oid';
    public $error = '';    
    
    public function fetchObjects($where = '')
    {
        $db = $this->getAdapter();
        $sql  = 'SELECT oc.*, o.name, u.realname  FROM obj_contents oc LEFT JOIN objects o ON o.oid = oc.oid LEFT JOIN users u ON u.uid = oc.lastuid';
        $sql .= $where ? ' WHERE ' . $where : '';
        
        return $db->fetchAll($sql);
    }

    public function fetchContents($oid, $pageid)
    {
        return $this->fetchRow("oid = $oid AND pageid = '$pageid'");
    }
}