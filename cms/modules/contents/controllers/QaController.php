<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'contents/models/qa.php';


class Contents_QaController extends BaseController
{
	private $channel;
	private $user;
	private $obj;
	private $db;
	private $person;

	public function init(){
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];
		$this->user= $user['username'];					
		$qa_config=new Zend_Config_Ini(ROOT_PATH . 'config.qa.ini'); 
		$this->db = $db = Zend_Db::factory('PDO_MYSQL', $qa_config->db->toArray());	
		Zend_Db_Table::setDefaultAdapter($this->db); 	
		$this->obj= new Qa($db);
		$this->person= $qa_config->person->toArray();			
		$db->query("set names utf8");		
	}

	public function indexAction(){		
		$this->_checkPermission('qa', 'list');	
		$pagenum= 15;
        $page= $this->_getParam('page', 1);
        $page= max($page, 1);
        $start=($page - 1) * $pagenum;  
		
		$person= $this->_getParam('person', 0);
		$this->view->person = $person;
		$person= $person? $person : $this->person[$this->channel];
		//$person= is_array($person)== FALSE? (array)$person :$person;		
		$where= $this->obj->makeWhere($person, 'author');		
		
		$total = $this->obj->count($where);
        $pagebar = Util::buildPagebar($total, $pagenum, $page, '?page=__page__');
		$this->view->pagebar = $pagebar;		
		$this->view->data= $this->obj->getList($where,$start,$pagenum);	
		$this->view->persons = $this->obj->makeArray($this->person[$this->channel]);		
	}

	public function delAction(){
		$this->_checkPermission('qa', 'del');
		$id= $this->_getParam('id');
		$url= $this->_getParam('url','/contents/qa');
		if(!$id){
			$this->flash('请勾选要删除的项', '/contents/qa');	
			return false;
		}
		
		$where= $this->obj->makeWhere(implode(",",$id), 'id');
		if($this->obj->delete($where)){
			$this->flash('删除成功', $url);	
		}else{
			$this->flash('删除失败', $url);	
		}
	}
}