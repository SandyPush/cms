<?php

/** @see Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Session.php';

class BaseController extends Zend_Controller_Action
{
	protected $_user = null;
	protected $_errors = array();
	protected $_acl;
	protected $_session;
	
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
	{
		parent::__construct($request, $response, $invokeArgs);
				
		// do something for initialization

		// global session
		$globalNS = new Zend_Session_Namespace('global');
		$this->_session = $globalNS;
		
		//Zend_Session::rememberMe();
		
		// current user
		if (Zend_Session::namespaceIsset('user')) {
		    $this->_user = Zend_Session::namespaceGet('user');
		    $this->_createAcl();

			// channel config
			Zend_Registry::set('channel_config', new Zend_Config_Ini(ROOT_PATH . 'config.' . $this->_user['channel'] . '.ini'));
		}		
		
		$this->view->headScript()->appendFile('/scripts/jquery/jquery.js');
		$this->view->headLink()->setStylesheet('/theme/default/common.css');
		$this->view->headTitle()->setSeparator(' / ');
		$this->view->headTitle('V1.CN内容管理系统', 'PREPEND');
	}
	
	/**
	 * Shortcut of this->_request->isPost()
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		return $this->_request->isPost();
	}
	
	/**
	 * recode error message
	 *
	 * @param string $message
	 * @param boolean $throw_exception
	 */
	public function error($message, $throw_exception = false)
	{
		if ($throw_exception) {
			throw new UserException($message);
		}
		
		array_push($this->_errors, $message);
		$this->view->error_message = $message;
	}
	
	/**
	 * Show flash message and redirect
	 *
	 * @param string $message
	 * @param string $url
	 * @param integer $secs
	 */
    public function flash($message, $url, $secs = 0)
    {
        $this->view->layout()->disableLayout();
        $this->getResponse()->clearBody();
        $this->view->setScriptPath(MODULES_PATH . 'system/views/scripts/');
        
        $this->view->message = $message;
        $this->view->url = $url;
        $this->view->secs = $secs;
        
        echo $this->renderScript('flash.phtml');
    }

	/**
	 * Show javascript message and redirect
	 *
	 * @param string $str
	 * @param string $url
	 * @param integer $flag
	 */
	public function location($str='', $url='', $flag=1){
		 echo "<script>";
		 if($str)echo "alert('{$str}');";
		 if($url)echo "window.location.href='{$url}'";
		 echo "</script>"; 
		 if($flag)exit;
	}

    /**
     * get main db adapter
     *
     * @return zend_db object
     */
	public function getMainDbAdapter()
	{		    
	    $db = Util::getDbAdapter(Zend_Registry::get('env_config')->db);	    
		return $db;
	}

	public function getChannelDbAdapter($channel=NULL)
	{
	    $channel_db = $db = Zend_Db::factory('PDO_MYSQL', $this->getChannelConfig($channel)->db->toArray());
		$db->query("SET NAMES 'utf8'");
		return $channel_db;
	}

	public function getCommentDbAdapter()
	{
		$comment_config= new Zend_Config_Ini(ROOT_PATH . 'config.comment.ini');   
		$comment_db = $db = Zend_Db::factory('PDO_MYSQL', $comment_config->db->toArray());
		$db->query("SET NAMES 'utf8'");
		return $comment_db;
	}

	public function getVoteDbAdapter()
	{
		$vote_config= new Zend_Config_Ini(ROOT_PATH . 'config.vote.ini');   
		$vote_db = $db = Zend_Db::factory('PDO_MYSQL', $vote_config->db->toArray());
		$db->query("SET NAMES 'utf8'");
		return $vote_db;
	}

	//获取栏目配置文件对象
	public function getChannelConfig($channel=NULL)
	{
		$user= Zend_Session::namespaceGet('user');
		if(empty($channel))$channel=$user['channel'];
	    $channel_config=new Zend_Config_Ini(ROOT_PATH . 'config.'.$channel.'.ini');
	    return $channel_config;
	}

    public function preDispatch()
    {
        $extraNS = new Zend_Session_Namespace('extra');
        
        $extraNS->uid = $this->_user ? $this->_user['uid'] : 0;
        $extraNS->module = $this->_request->getModuleName();
        $extraNS->controller = $this->_request->getControllerName();
        $extraNS->action = $this->_request->getActionName();
        $extraNS->ip = $_SERVER['HTTP_X_REAL_IP'] ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];

		$logs= array(
			'uid'=> $extraNS->uid,
			'module'=> $extraNS->module,
			'controller'=> $extraNS->controller,
			'action'=> $extraNS->action,
            'operateURL'=> $_SERVER['REQUEST_URI'],
			'ip'=> ip2long($extraNS->ip),
			'time'=> time(),
			'useragent'=> $_SERVER['HTTP_USER_AGENT'],
			);
	
		if($extraNS->uid){
			$db= Zend_Registry::get('channel_db');			
			//$te= $db->fetchRow("DESCRIBE article insert_time");			
			//if(empty($te)){		
				//$db->query("ALTER TABLE `article` ADD `insert_time` VARCHAR( 30 ) CHARACTER SET gbk COLLATE gbk_chinese_ci NOT NULL COMMENT '抓取的时间' AFTER `postdate` ;");
			//}else{
				//$db->query("ALTER TABLE `article` CHANGE `insert_time` `insert_time` VARCHAR( 30 ) CHARACTER SET gbk COLLATE gbk_chinese_ci NULL");
			//}
						
			$logTable= $db->fetchRow("SHOW TABLES like 'user_logs'");	
			if(empty($logTable)){		
				$db->query("CREATE TABLE `user_logs` (
							`id` INT( 10 ) UNSIGNED NOT NULL auto_increment,
							`time` INT( 10 ) UNSIGNED NOT NULL ,
							`ip` BIGINT( 11 ) NOT NULL ,
							`module` CHAR( 30 ) NOT NULL ,
							`controller` CHAR( 30 ) NOT NULL ,
							`action` CHAR( 30 ) NOT NULL ,
                            `operateURL` VARCHAR( 255 ) NOT NULL ,
							`uid` INT( 10 ) UNSIGNED NOT NULL ,
							`useragent` CHAR( 255 ) NOT NULL ,
							PRIMARY KEY ( `id` ) ,
							INDEX ( `uid` ) 
							) ENGINE = MYISAM ;");				
			}
			//if($db->fetchRow("SHOW INDEX FROM user_logs where Key_name =  'uid'")){
				//$db->query("ALTER TABLE `user_logs` DROP INDEX `uid` ");
			//}
			//if(!$db->fetchRow("SHOW INDEX FROM user_logs where Key_name =  'time'")){
				//$db->query("ALTER TABLE `user_logs` ADD INDEX ( `time` )  ");	
			//}
			$db->insert('user_logs', $logs);
        }
        // ajax encoding gbk->utf8
        if ($this->_request->isXmlHttpRequest()) {
            $params = array();
            foreach ($this->_request->getParams() as $k => $v) {
                $params[$k] = $v;
            }
            $this->_request->setParams($params);
        }
//		$controlerName=$this->_request->getControllerName();
//		$actionName=$this->_request->getActionName();
//      $this->_checkPermission($controlerName, $actionName);
    }
    
    public function postDispatch()
    {
        if ($this->_request->isXmlHttpRequest()) {
            foreach ($this->view->getVars() as $k => $v) {
                $this->view->{$k} = $v;
            }
        }
    }
    
    protected function _checkPermission($resource, $action)
    {
        if (!$this->_acl->isAllowed($this->_user['usergroup'], $resource, $action)) {
            throw new UserException('你没有执行此操作的权限');        
        }
    }
    
    public function _createAcl()
    {
        // acl
        $acl = new Zend_Acl();
        $this->_acl = $acl;
        
        //$db = $this->getMainDbAdapter();
        $db = $this->getChannelDbAdapter();
        $this->_user['usergroup']=$db->fetchOne($db->select()->from('users', 'usergroup')->where('uid=?',$this->_user['uid']));
        $this->_user['transcodeDir']=$db->fetchOne($db->select()->from('users', 'transcodeDir')->where('uid=?',$this->_user['uid']));     
        $this->_user['uploadDir']=$db->fetchOne($db->select()->from('users', 'uploadDir')->where('uid=?',$this->_user['uid']));   
        !empty($this->_user['usergroup']) OR $this->_user['usergroup']=0;
        
        // roles
        $groups = array();
        $roles = array();
        foreach ($db->fetchAll($db->select()->from('user_groups', array('gid', 'name', 'inherit_from'))) as $g) {
            $groups[$g['gid']] = $g;
        }
        foreach ($groups as $g) {
            $this->_createAclRole($groups, $g['gid']);
        }
        
        // resources
        $resources = Zend_Registry::get('settings')->resources->toArray();
        $resources = array_keys($resources);
        foreach ($resources as $res) {
            $acl->add(new Zend_Acl_Resource($res));
        }
        
        // rules
        foreach ($db->fetchAll($db->select()->from('acl')) as $item) {
            if (!$acl->has($item['resource']) || !$acl->hasRole($item['role'])) {
                continue;
            }
            
            $params = array($item['role'], $item['resource']);
            if ($item['permissions'] != '') {
                array_push($params, explode(', ', $item['permissions']));      
            }
            
            call_user_func_array(array($acl, $item['aord']), $params);
        }
        
        // administrator
        $acl->allow(1);
        // undefined
		//$acl->allow(0,null,'pwd');

    }
    
    private function _createAclRole($groups, $gid)
    {
        $group = $groups[$gid];
        if ($this->_acl->hasRole($gid)) {
            return true;
        }
        
        if ($group['inherit_from'] != 0) {
            $this->_createAclRole($groups, $group['inherit_from']);
            $this->_acl->addRole(new Zend_Acl_Role($gid), $group['inherit_from']);
        } else {
            $this->_acl->addRole(new Zend_Acl_Role($gid));
        }
        
        return true;
    }
}
