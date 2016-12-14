<?php
require_once 'Zend/Db/Table.php';

class FocusTable extends Zend_Db_Table
{
    protected $_name = 'focus';
    protected $_primary = 'fid';

}