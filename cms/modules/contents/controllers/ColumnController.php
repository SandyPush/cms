<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'contents/models/column.php';


class Contents_ColumnController extends BaseController
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
		$qa_config=new Zend_Config_Ini(ROOT_PATH . 'config.search.ini'); 
		$this->db = $db = Zend_Db::factory('PDO_MYSQL', $qa_config->db->toArray());	
		Zend_Db_Table::setDefaultAdapter($this->db); 	
		$db->query("set names utf8");
		$this->obj= new Column($db);			
		$this->obj->base_path= $this->getChannelConfig($this->channel)->path->images.'/column';
    	$this->obj->base_url= $this->getChannelConfig($this->channel)->url->images.'column'; 
	}

	public function indexAction(){		
		$this->_checkPermission('column', 'list');	
		$pagenum= 15;
        $page= $this->_getParam('page', 1);
        $page= max($page, 1);
        $start=($page - 1) * $pagenum; 	
			
		
		$total = $this->obj->count('id>0');
        $pagebar = Util::buildPagebar($total, $pagenum, $page, '?page=__page__');
		$this->view->pagebar = $pagebar;		
		$this->view->data= $this->obj->getList('id>0', $start, $pagenum);	
		$this->view->persons = $this->obj->makeArray($this->person[$this->channel]);		
	}

	public function addAction(){		
		$this->_checkPermission('column', 'add');		
	}

	public function insertAction(){		
		$this->_checkPermission('column', 'add');
		$name= trim($this->_getParam('name'));	
		$title= trim($this->_getParam('title'));
		$icon= $this->obj->upload($_FILES['icon']);		
		$intro= trim($this->_getParam('intro'));		

		$homeUrl= trim($this->_getParam('homeUrl'));	
		$blogUrl= trim($this->_getParam('blogUrl'));
		$groupUrl= trim($this->_getParam('groupUrl'));
		$qaUrl= trim($this->_getParam('qaUrl'));		

		$author= $this->user;	

		if(!$icon){
			$this->flash('上传图片有误', '/contents/column/add',2);
			return false;
		}
		if(!$name ||  !$title || !$intro || !$homeUrl || !$blogUrl || !$groupUrl || !$qaUrl){
			$this->flash('请检查输入是否完整', '/contents/column/add',2);
			return false;
		}

		$data= compact("name","title","icon","intro","homeUrl","blogUrl","groupUrl","qaUrl","author");
		$return= $this->obj->insert($data);
		
		if(false === $return) {			
            $this->error($this->_table->error);
        }else{				
			$this->flash('添加成功', '/contents/column/');
		}		
	}

	public function editAction(){
		$this->_checkPermission('column', 'edit');	
		$id = $this->_getParam('id','');	
		$where = $this->db->quoteInto('id = ?', $id );
		$order = 'id';
		$data = $this->obj->fetchRow($where, $order)->toArray();		
		$this->view->data = $data;	
	}

	public function updateAction(){
		$this->_checkPermission('column', 'edit');
		$id= trim($this->_getParam('id'));
		$name= trim($this->_getParam('name'));	
		$title= trim($this->_getParam('title'));
		$icon= $this->obj->upload($_FILES['icon']);		
		$intro= trim($this->_getParam('intro'));		

		$homeUrl= trim($this->_getParam('homeUrl'));	
		$blogUrl= trim($this->_getParam('blogUrl'));
		$groupUrl= trim($this->_getParam('groupUrl'));
		$qaUrl= trim($this->_getParam('qaUrl'));

		$author= $this->user;		
	
		if(!$name ||  !$title || !$intro || !$homeUrl || !$blogUrl || !$groupUrl || !$qaUrl){
			$this->flash('请检查输入是否完整', '/contents/column/add',2);
			return false;
		}

		$data= compact("name","title","icon","intro","homeUrl","blogUrl","groupUrl","qaUrl","author");
		if(!$icon) unset($data['icon']);	
		
		$where = $this->db->quoteInto('id = ?', $id);		
		
		if(false === $this->obj->update($data, $where)) {
            $this->error($this->_table->error);
        }else{ 
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoUrl('/contents/column');	
		}	
	}

	public function delAction(){
		$this->_checkPermission('column', 'del');
		$id= $this->_getParam('id');
		$url= $this->_getParam('url','/contents/column');
		if(!$id){
			$this->flash('请勾选要删除的项', '/contents/column');	
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