<?php
require_once 'Zend/Controller/Action.php';

class LoginController extends Zend_Controller_Action
{
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
	{
		parent::__construct($request, $response, $invokeArgs);
		// global session
		$globalNS = new Zend_Session_Namespace('global');
		$this->_session = $globalNS;
	}
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
	public function isPost()
	{
		return $this->_request->isPost();
	}
	public function getMainDbAdapter()
	{
	    $env_config = Zend_Registry::get('env_config');
	    /*
		$db = Zend_Db::factory('PDO_MYSQL', $env_config->db->toArray());
		$db->query("set names utf8");
		*/
	    
	    $db = Util::getDbAdapter(Zend_Registry::get('env_config')->db);
	    
		return $db;
	}
    public function flash($message, $url, $secs = 2)
    {
        $this->view->layout()->disableLayout();
        $this->getResponse()->clearBody();
        $this->view->setScriptPath(MODULES_PATH . 'system/views/scripts/');
        
        $this->view->message = $message;
        $this->view->url = $url;
        $this->view->secs = $secs;
        
        echo $this->renderScript('flash.phtml');
    }

    public function loginAction()
    {
        $this->view->layout()->disableLayout();
        $this->getResponse()->clearBody();
		$this->view->headScript()->appendFile('/scripts/jquery/jquery.js');
		$this->view->headLink()->setStylesheet('/theme/default/common.css');
		$this->view->channels=Zend_Registry::get('settings')->channels->toArray();
                
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

            if ($user['password'] != md5($password)) {
                $this->error('密码错误');
                return false;
            }
            
            $authNS = new Zend_Session_Namespace('user');
            //$authNS->setExpirationSeconds(72000);
            
            $authNS->uid = $user['uid'];
            $authNS->username = $user['username'];
            $authNS->realname = $user['realname'];
            $authNS->usergroup = $user['usergroup'];
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
         
        $this->flash('退出成功', '/');
    }
}