<?php

/** @see BaseController */
require_once 'BaseController.php';

require_once MODULES_PATH . 'system/models/Users.php';
require_once MODULES_PATH . 'system/models/UserGroups.php';

class UserController extends BaseController
{
    protected $_groups_table;    
	protected $_users_table;
	protected $_db;
	
    public function init()
    {		
    	$db = $this->getChannelDbAdapter();
    	$this->_db = $db;
    	
    	Zend_Db_Table_Abstract::setDefaultAdapter($db);
    	
    	$this->_groups_table = new UserGroupsTable();
        $this->_users_table = new UsersTable();        
    }
    
    public function indexAction()
    {
        $this->_checkPermission('user', 'index');
        $usergroups=$this->_db->fetchAssoc($this->_db->select()->from('user_groups',array('gid','name')));
		$username=$this->_getParam('username', '');
		$realname=$this->_getParam('realname', '');
        $this->view->username = $username;
        $this->view->realname = $realname;
        $perpage = 15;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
		//此操作于主数据库
		$select=$this->_db->select();
		$select->from('users','*');
		if(!empty($username))$select->where("username LIKE '%".$username."%'");
		if(!empty($realname))$select->where("realname LIKE '%".$realname."%'");
		$count_sql=strstr($select->__toString(),'FROM');
		$select->order('usergroup');
		$select->order('uid');
		$select->limitPage($page,$perpage);
        $users = $this->_db->fetchAll($select);
        foreach($users as &$row)
        {
        	$row['groupname']=$usergroups[$row['usergroup']]['name'];
        }
    	$this->view->users = $users;
        $total = $this->_db->fetchOne("SELECT COUNT(*) ".$count_sql);
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__');
        $this->view->pagebar = $pagebar;
    }
    public function editAction()
    {
        $this->_checkPermission('user', 'edit');
        $uid = (int) $this->_getParam('uid', 0);
        if (!$uid || false === $user = $this->_users_table->find($uid)) {
            $this->error('请指定用户', true);
        }
        
        $user = $user->current();
        $groups = array();
        foreach ($this->_groups_table->fetchAll() as $group) {
            $groups[$group->gid] = $group->name;
        }
        
        $this->view->groups = $groups;
        $this->view->user = $user;
        
        if ($this->isPost()) {
            $uploadDir=$this->_getParam('uploadDir');
            if($uploadDir and substr($uploadDir,-1)!='/') $uploadDir.="/";
            $transcodeDir=$this->_getParam('transcodeDir');
            if($transcodeDir and substr($transcodeDir,-1)!='/') $transcodeDir.="/";
            
            $new = array(
                'usergroup' => $this->_getParam('group', 0),
                'uploadDir' => $uploadDir,
                'transcodeDir' => $transcodeDir,
            );
           
            if (false == $this->_users_table->edit($new, 'uid = ' . $uid)) {
                $this->error($this->_users_table->error);
                return false;
            }
            
            // redirect
            $this->flash('用户编辑成功', '/user/');
        }
        
        
    }
    public function pwdAction()
    {
    	//此操作于主数据库
    	$db = $this->getMainDbAdapter();
    	$this->_db = $db;
    	Zend_Db_Table::setDefaultAdapter($db);
        $uid = $this->_user['uid'];
        $this->view->user=$db->fetchRow("SELECT * FROM users WHERE uid='".$uid."'");
        if ($this->isPost()) {
        	if($this->_getParam('password') == '')
        	{
                $this->location('密码不能为空','system/user/pwd/');
                return false;
        	}
            if ($this->_getParam('password') !== $this->_getParam('password_alt')) {
                $this->location('新密码两次输入不一致','system/user/pwd/');
                return false;
            }
            $new['password'] = md5($this->_getParam('password'));
			$new['weibo']    = trim($this->_getParam('weibo'));
			$new['nickname'] = trim($this->_getParam('nickname'));
			$this->_users_table->checkField($db);		
        	$db->update('users',$new,'uid='.$uid);		

            // redirect
            $this->flash('更改成功', '/user/');
        }
    	
    }

	public function logAction()
    {
    	$db = Zend_Registry::get('channel_db');    	
        $uid = $this->_user['uid'];
		$time= $this->_getParam('startTime') ? strtotime(trim($this->_getParam('startTime'))): strtotime(date('Y-m-d').' 00:00:00');
		$search_para='startTime='.urlencode($this->_getParam('startTime'))."&";
        $timeend= $this->_getParam('endTime') ? strtotime(trim($this->_getParam('endTime'))) : strtotime(date('Y-m-d').' 23:59:59');		
        $search_para='endTime='.urlencode($this->_getParam('endTime'))."&";
        
        $username=$this->_getParam('username');
        if($username){
            $setWhere=" and b.username='".$username."'";
            $search_para.='username='.urlencode($this->_getParam('username'));
        }
        
        $userAction=$this->_getParam('userAction');
        if($userAction){
            $setWhere=" and a.operateURL like '%".$userAction."%'";
            $search_para.='userAction='.urlencode($this->_getParam('userAction'));
        }
        
        $perpage = 30;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        
        $this->view->logs= $logs= $db->fetchAll("SELECT a.*,b.username,b.realname
		FROM `user_logs` a,users b WHERE a.uid=b.uid ".$setWhere." and time>='$time' and time<='$timeend' order by time desc limit ".($page - 1)*$perpage.",".$perpage);
		foreach($logs as $key=>&$log){
			if(!$log['username'])unset($logs [$key]);
			$log['ip']  = long2ip($log['ip']);
			$log['time']= date('Y-m-d H:i:s', $log['time']);
			//if(!$log['username'])echo $log['uid']."\n";
			unset($log);
		}   
		$this->view->logs = $logs;
		$this->view->headScript()->appendFile('/scripts/date/WdatePicker.js');
        
        
        $total = $db->fetchOne("select count(*) as nums from user_logs a,users b WHERE a.uid=b.uid ".$setWhere." and time>='$time' and time<='$timeend'");
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__&'.$search_para);
        $this->view->pagebar = $pagebar;
    }
}
