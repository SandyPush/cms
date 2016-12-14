<?php

/** @see BaseController */
require_once 'BaseController.php';

class IndexController extends BaseController
{
    public function init()
    {
        /*
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('loginx', 'json')
                      ->initContext();
        */
        $ajaxContext = $this->_helper->getHelper("ajaxContext")
            ->addActionContext('loginx','json')
            //->setAutoJsonSerialization(false)
            ->setAutoDisableLayout(true)
            ->initContext('json');
    }

    public function indexAction()
    {
		$this->view->layout()->disableLayout();
        $this->view->user = $this->_user;
        #更改频道
		$channel = $this->_getParam('channel',0);
        if (!empty($channel)) {
	        $channels=Zend_Registry::get('settings')->channels->toArray();
			$channel = $this->_getParam('channel');
			if(!isset($channels[$channel]))
			{
				die('频道不存在');
				return false;
			}
			//$user=Zend_Session::namespaceGet('user');
			$user=new Zend_Session_Namespace('user');
			$user->channel=$channel;
			//Zend_Session::namespaceSet('user');
        	$this->flash('更改频道成功', '/');
        }
    }
    public function loginAction()
    {
        $this->view->layout()->disableLayout();
        $this->getResponse()->clearBody();
        $channels=Zend_Registry::get('settings')->channels->toArray();
        $this->view->channels=$channels;  
        if ($this->isPost()) {
            $username = $this->_getParam('username');
            $password = $this->_getParam('password');

            if ($username === '' || $password === '') {
                $this->error('请输入用户名及密码');
                return false;
            }

            $db = $this->getMainDbAdapter();
            $user = $db->fetchRow('SELECT * FROM users WHERE username = ' . $db->quote($username));
            if (!$user) {
                $this->error('无此用户');
                return false;
            }
          
            if ($user['active'] != 1) {
                $this->error('此用户未激活');
                return false;
            }

            if ($user['password'] != md5($password)) {
                $this->error('密码错误');
                return false;
            }
            
            $authNS = new Zend_Session_Namespace('user');
            //$authNS->setExpirationSeconds(72000);
            
            $authNS->uid = $user['uid'];
            $authNS->username = $user['username'];
            $authNS->realname = $user['realname'];
            //$authNS->usergroup = $user['usergroup'];
            $authNS->email = $user['email'];		
            $authNS->channel=$this->_getParam('channel');
            
            $url = $_SERVER['REQUEST_URI'];
            $this->flash('登录成功', $url);
        }
    }
    
    /**
     * Ajax Login Action
     *
     */
    public function loginxAction()
    {
        //$this->getResponse()->clearBody();
        //$this->_helper->viewRenderer->setNoRender();
         
        $this->view->login = false;
    }
    
    public function logoutAction()
    {
        if (Zend_Session::namespaceIsset('user')) {
            Zend_Session::namespaceUnset('user');
        }
        Zend_Session::destroy();
        $this->flash('退出成功', '/');
    }

	public function topAction(){
		$user=$this->_user;
		$uid= $user['uid'];
		$this->view->user_name=$user['username'];
		$this->view->user_realname=$user['realname'];
		$this->view->user_channel=$user['channel'];
		$channels=Zend_Registry::get('settings')->channels->toArray();
		/*
		foreach($channels as $channel=>$channelName ){
			${$channel.'db'} = $this->getChannelDbAdapter($channel);		
			if($nums=${$channel.'db'}->fetchOne("SELECT COUNT(*) FROM users WHERE uid=".$uid)){
				continue;
			}
			unset($channels[$channel]);
		}
		*/
		$this->view->channels= $channels;
		$this->view->layout()->disableLayout();
    }

	public function menuAction(){
		$this->view->layout()->disableLayout();
		$menu_config = new Zend_Config_Ini(MENU_CONFIG_FILE);
		$menu_array=$menu_config->toArray();
		$menu_array_count=count($menu_array);
		$tree_array=array();
		$id=100;
		foreach($menu_array as $module)
		{
			if($module['hide'])continue;
			$row['id']=$id;
			$row['pid']=0;
			#$row['name']=iconv('GBK','UTF-8',$module['name']);
			$row['name']=$module['name'];
			$tree_array[]=$row;
			//array_shift(&$module);
			$id1=$id;
// 			foreach($module as $item)
// 			{
// 				if(!is_array($item))continue;
// 				if($item['hide'])continue;
// 				if($item['index']['hide'])continue;
// 				$id1++;
// 				$row1['id']=$id1;
// 				$row1['pid']=$id;
// 				//$row1['name']=iconv('GBK','UTF-8',$item['index']['name']);
// 				$row1['name']=$item['index']['name'] ? $item['index']['name'] : $item['name'];
// 				$row1['link']=$item['index']['link'] ? $item['index']['link'] : $item['link'];
// 				$tree_array[]=$row1;
// 			}
			foreach($module as $item)
			{
				if(!is_array($item))continue;
				if($item['hide'])continue;
				
				if (isset($item['name']) && is_string($item['name'])) {
					if($item['hide'])continue;
					$id1++;
					$row1['id']=$id1;
					$row1['pid']=$id;
					//$row1['name']=iconv('GBK','UTF-8',$item['index']['name']);
					$row1['name']=$item['name'];
					$row1['link']=$item['link'];
					$tree_array[]=$row1;
				} else {
					foreach ($item as $c_item) {
						if(!is_array($c_item))continue;
						if($c_item['hide'])continue;
						$id1++;
						$row1['id']=$id1;
						$row1['pid']=$id;
						//$row1['name']=iconv('GBK','UTF-8',$item['index']['name']);
						$row1['name']=$c_item['name'];
						$row1['link']=$c_item['link'];
						$tree_array[]=$row1;
					}
				}
			}
			$id+=100;
		}
		$this->view->tree_json=json_encode($tree_array);
    }
    #登录用户管理
    public function userAction()
    {
        $admin_array=Zend_Registry::get('settings')->admins->toArray();
		if(!isset($admin_array[$this->_user['uid']]) OR array_search($this->_user['username'],$admin_array) !=$this->_user['uid'])
		{
			die('无权限操作');
		}
		$username=$this->_getParam('username', '');
		$realname=$this->_getParam('realname', '');
        $this->view->username = $username;
        $this->view->realname = $realname;
        $perpage = 15;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
		//此操作于主数据库
		$db = $this->getMainDbAdapter();
		$select=$db->select();
		$select->from('users','*');
		if(!empty($username))$select->where("username LIKE '%".$username."%'");
		if(!empty($realname))$select->where("realname LIKE '%".$realname."%'");
		$count_sql=strstr($select->__toString(),'FROM');
		$select->order('uid');
		$select->limitPage($page,$perpage);
        $users = $db->fetchAll($select);
    	$this->view->users = $users;
        $total = $db->fetchOne("SELECT COUNT(*) ".$count_sql);
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__');
        $this->view->pagebar = $pagebar;
		$this->view->layout()->disableLayout();
		$this->getResponse()->clearBody();
    }
    public function useraddAction()
    {
        $channels=Zend_Registry::get('settings')->channels->toArray();
        $this->view->channels=$channels;  
		$this->view->layout()->disableLayout();
		$this->getResponse()->clearBody();
    }
    public function useraddedAction()
    {
        $admin_array=Zend_Registry::get('settings')->admins->toArray();
		if(!isset($admin_array[$this->_user['uid']]) OR array_search($this->_user['username'],$admin_array) !=$this->_user['uid'])
		{
			die('无权限操作');
		}
    	//此操作于主数据库
    	$db = $this->getMainDbAdapter();
    	$channel_arr=$this->_getParam('channels');
    	if(!empty($channel_arr))$channel_str=implode(',',$channel_arr);else $channel_str='';
	    $user = array (
	        'username' => $this->_getParam('username'),
	        'realname' => $this->_getParam('realname'),
	        'password' => $this->_getParam('password'),	       
	        'email' => $this->_getParam('email'),
	        'operator' => $this->_user['uid'], 
	        'channels' => $channel_str,
	        );
        
	    if ($user['password'] !== $this->_getParam('password_alt')) {
    		echo "<script>alert('两次密码不一致');window.close();</script>";
	    }
	    $user['password']=md5($user['password']);
        $user['salt'] = substr(md5(uniqid()), 0, 6);
	    if (false == $db->insert('users',$user)) {
    		echo "<script>alert('数据库错误');window.close();</script>";
	    }
	    #把用户信息更新到各频道用户表中
	    $uid=$db->lastInsertId();
        $channels=Zend_Registry::get('settings')->channels->toArray();
    	foreach($channels as $channel => $name)
    	{
			$db = $this->getChannelDbAdapter($channel);
			$db->delete('users','uid='.$uid);
			if(array_search($channel,$channel_arr)!==false)
			{
    			$db->insert('users',array(
	    	        'uid' => $uid,
	    	        'username' => $user['username'],
	    	        'realname' => $user['realname'],
	    	        'email' => $user['email'],
	    	        'operator' => $this->_user['uid'], 
	    	        )
	    	    );
	    	}
    	}
    	echo "<script>alert('添加成功');window.close();</script>";
		exit;
    }
    public function usereditAction()
    {
        $channels=Zend_Registry::get('settings')->channels->toArray();
        $this->view->channels=$channels;
        $uid=$this->_getParam('uid',0);
	    if (empty($uid)) {
	        $this->error('参数错误');
	        return false;
	    }
    	//此操作于主数据库
    	$db = $this->getMainDbAdapter();
    	$user=$db->fetchRow("SELECT * FROM users WHERE uid=".$uid);
        $this->view->user = $user;
		$this->view->layout()->disableLayout();
		$this->getResponse()->clearBody();
    }
    public function usereditedAction()
    {
        $admin_array=Zend_Registry::get('settings')->admins->toArray();
		if(!isset($admin_array[$this->_user['uid']]) OR array_search($this->_user['username'],$admin_array) !=$this->_user['uid'])
		{
			die('无权限操作');
		}
        $uid = (int) $this->_getParam('uid', 0);
	    if (empty($uid)) {
	        $this->error('参数错误');
	        return false;
	    }
    	//此操作于主数据库
    	$db = $this->getMainDbAdapter();
    	$channel_arr=$this->_getParam('channels');
    	if(!empty($channel_arr))$channel_str=implode(',',$channel_arr);else $channel_str='';
	    $password=$this->_getParam('password');       
	    $user = array (
	        'username' => $this->_getParam('username'),
	        'realname' => $this->_getParam('realname'),
	        'email' => $this->_getParam('email'),
	        'operator' => $this->_user['uid'], 
	        'channels' => $channel_str,
	    );
	    if (!empty($password) AND ($password !== $this->_getParam('password_alt'))) {
	        $this->error('两次密码输入不一致');
	        return false;
	    }
        if(!empty($password))$user['password']=md5($password);

	    $db->update('users',$user,'uid='.$uid);
	    //die('aaa');
	    #把用户信息更新到各频道用户表中
        $channels=Zend_Registry::get('settings')->channels->toArray();
    	foreach($channels as $channel => $name)
    	{
			${$channel.'db'} = $this->getChannelDbAdapter($channel);
			if(array_search($channel,$channel_arr)===false)
			{
				${$channel.'db'}->delete('users','uid='.$uid);
			}
			else
			{
				$uid_nums=${$channel.'db'}->fetchOne("SELECT COUNT(*) FROM users WHERE uid=".$uid);
				if($uid_nums)
				{
	    			${$channel.'db'}->update('users',array(
		    	        'uid' => $uid,
		    	        'username' => $user['username'],
		    	        'realname' => $user['realname'],
		    	        'email' => $user['email'],
		    	        'operator' => $this->_user['uid'], 
		    	        ),
		    	        'uid='.$uid
		    	    );
		    	}
	    	    else
	    	    {
	    	    	$data=array(
		    	        'uid' => $uid,
		    	        'username' => $user['username'],
		    	        'realname' => $user['realname'],
		    	        'email' => $user['email'],
		    	        'operator' => $this->_user['uid']
		    	    );
		    	    $sql="INSERT INTO users SET ";
		    	    foreach($data as $key => $value)
		    	    {
		    	    	$sql.=$key."='".$value."',";
		    	    }
		    	    $sql=substr($sql,0,-1);
		    	    //$db->query($sql);
					$rows_affected=${$channel.'db'}->insert('users',$data);//不知为何不起作用
					/* 不起作用是因为insert方法有一个bug,不能返回影响的行数,这里返回的是空^_^ */
	    	    }
	    	}
    	}
    	echo "<script>alert('编辑成功');window.close();</script>";
		exit;
    }
    public function activeAction()
    {
        $admin_array=Zend_Registry::get('settings')->admins->toArray();
		if(!isset($admin_array[$this->_user['uid']]) OR array_search($this->_user['username'],$admin_array) !=$this->_user['uid'])
		{
			die('无权限操作');
		}
        $uid = (int) $this->_getParam('uid', 0);
	    if (empty($uid)) {
	        $this->error('参数错误');
	        return false;
	    }
        $aid = (int) $this->_getParam('aid', 0);
    	//此操作于主数据库
    	$db = $this->getMainDbAdapter();
	    $user = array('active' => $aid,);
	    $db->update('users',$user,'uid='.$uid);
    	echo "<script>alert('编辑成功');window.close();</script>";
		exit;
    }
}