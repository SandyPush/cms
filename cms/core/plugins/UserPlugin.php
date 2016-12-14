<?php
/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

class UserPlugin extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        // check user
        if (!Zend_Session::namespaceIsset('user')) {
            $request->setModuleName('default')
            ->setControllerName('index')
            ->setActionName('login');
            
            if ($request->isXmlHttpRequest()) {
                $request->setActionName('loginx');
            }
            return false;
        }
        
        //$user = Zend_Session::namespaceGet('user');	
				
		//$this->getResponse()->append('user_name', $user['username']);
        //$this->getResponse()->append('user_realname', $user['realname']);	
        
        //Zend_Registry::set('user', array('uid' => 1, 'username' => 'titan', 'realname' => '体坛'));
    }
}