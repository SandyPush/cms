<?php
require_once 'Zend/Session/SaveHandler/DbTable.php';

class Titan_Session_SaveHandler_DbTable extends Zend_Session_SaveHandler_DbTable
{
    public function write($id, $data)
    {
        $extra = $_SESSION['extra'];
        unset($_SESSION['extra']);
        $data = session_encode();
        $return = parent::write($id, $data);
        // update extra data
        $this->update($extra, $this->_getPrimary($id, self::PRIMARY_TYPE_WHERECLAUSE));
        return $return;
    }
}
