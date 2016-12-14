<?php
require_once 'Zend/Db/Table.php';

class UserGroupsTable extends Zend_Db_Table
{
    protected $_name = 'user_groups';
    protected $_primary = 'gid';
    public $error = '';

    public function __construct($db=NULL)
    {
    	parent::__construct();
    	if(isset($db))
    	{
    		Zend_Db_Table_Abstract::setDefaultAdapter($db);
    		$this->_db=$db;
    	}
    }
    public function insert(array $data)
    {
        if ($data['name'] === '') {
            $this->error = '请指定组名';
            return false;
        }

        $db = $this->getAdapter();
        if ($this->fetchRow($db->quoteInto("name = ?", $data['name']))) {
            $this->error = '已存在名为"' . $data['name'] . '"的用户组';
            return false;
        }

        $data['type'] = 'custom';
        $data['operator'] = '';

        return parent::insert($data);
    }

    public function edit(array $data, $where)
    {
        if ($data['name'] === '') {
            $this->error = '请指定组名';
            return false;
        }
        
        $db = $this->getAdapter();
        if ($this->fetchRow($db->quoteInto("name = ?", $data['name']) . ' AND gid != ' . $data['gid'])) {
            $this->error = '已存在名为"' . $data['name'] . '"的用户组';
            return false;
        }

        unset($data['gid']);
        
        return parent::update($data, $where);
    }
}