<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'vote/models/debate.php';


class Vote_DebateController extends BaseController
{
	private $channel;
	private $user;
	private $obj;
	private $db;

	public function init(){
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];
		$this->user= $user['username'];
		$vote_db = $this->getVoteDbAdapter();
		$this->db= $vote_db ;
		Zend_Db_Table::setDefaultAdapter($vote_db);   
		$this->obj=new Debate($vote_db);
		$this->obj->base_path= $this->getChannelConfig($this->channel)->path->images.'/debate';
    	$this->obj->base_url= $this->getChannelConfig($this->channel)->url->images.'debate'; 		
	}

	public function indexAction(){
		$this->_checkPermission('debate', 'list');	
		$pagenum = 15;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        $start =($page - 1) * $pagenum;     
		    	
		$total = $this->obj->count($id);
        $pagebar = Util::buildPagebar($total, $pagenum, $page, '?page=__page__');
		$this->view->pagebar = $pagebar;		
		$this->view->data=$this->obj->getList($this->channel,$start,$pagenum);
		$url= $this->getChannelConfig($this->channel)->url->published.'/pk';
		foreach($this->view->data as &$var)$var['url']= $url."/".$var[id].".html";
	}

	public function addAction(){
		$this->_checkPermission('debate', 'add');	
		$this->view->headScript()->appendFile('/scripts/date/WdatePicker.js');
	}

	public function editAction(){
		$this->_checkPermission('debate', 'edit');	
		$id = $this->_getParam('id','');	
		$where = $this->db->quoteInto('id = ?', $id );
		$order = 'id';
		$data = $this->obj->fetchRow($where, $order)->toArray();
		$checked[$data['level']]="checked";
		$select[$data['status']]="selected=\"selected\"";
		$data['checked']= $checked;
		$data['select']= $select;
		$data['postdate']= date('Y-m-d H:i:s',$data['postdate']);
		$this->view->data = $data;		
		$this->view->headScript()->appendFile('/scripts/date/WdatePicker.js');
	}

	public function updateAction(){
		$this->_checkPermission('debate', 'edit');
		$id= trim($this->_getParam('id'));
		$name= trim($this->_getParam('name'));	
		$photo= $this->obj->upload($_FILES['photo']);
		$intro= trim($this->_getParam('intro'));	
		$level= intval($this->_getParam('level'));	
		$postdate= strtotime($this->_getParam('postdate'));

		$z_name= trim($this->_getParam('z_name'));	
		$z_intro= trim($this->_getParam('z_intro'));	
		$z_photo= $this->obj->upload($_FILES['z_photo']);
		$z_url= trim($this->_getParam('z_url'));
		$z_count= intval($this->_getParam('z_count'));
		$z_name2= trim($this->_getParam('z_name2'));
		
		$f_name= trim($this->_getParam('f_name'));	
		$f_intro= trim($this->_getParam('f_intro'));	
		$f_photo= $this->obj->upload($_FILES['f_photo']);
		$f_url= trim($this->_getParam('f_url'));
		$f_count= intval($this->_getParam('f_count'));
		$f_name2= trim($this->_getParam('f_name2'));

		$status= intval($this->_getParam('status'));

		$author= $this->user;
		$channel= $this->channel;
	
		if(!$name ||  !$intro || !$postdate || !$z_name || !$z_intro || !$z_name2 || !$f_name || !$f_intro || !$f_name2){
			$this->flash('请检查输入是否完整', '/vote/debate/edit/id/'.$id,2);
			return false;
		}

		$data= compact("author","channel","name","photo","intro","level","postdate","z_name","z_intro","z_photo","z_url","z_count","z_name2","f_name","f_intro","f_photo","f_url","f_count","f_name2","status");		
		if(!$photo)  unset($data['photo']);
		if(!$z_photo)unset($data['z_photo']);
		if(!$f_photo)unset($data['f_photo']);
		
		$where = $this->db->quoteInto('id = ?', $id);		
		
		if(false === $this->obj->update($data, $where)) {
            $this->error($this->_table->error);
        }else{ 
			$this->_redirector = $this->_helper->getHelper('Redirector');
			$this->_redirector->gotoUrl('/vote/debate/publish/id/'.$id);
			//$this->flash('修改成功', '/vote/debate/index/');
		}	
	}

	public function createAction(){		
		$this->_checkPermission('debate', 'add');
		$name= trim($this->_getParam('name'));	
		$photo= $this->obj->upload($_FILES['photo']);
		$intro= trim($this->_getParam('intro'));	
		$level= intval($this->_getParam('level'));	
		$postdate= strtotime($this->_getParam('postdate'));

		$z_name= trim($this->_getParam('z_name'));	
		$z_intro= trim($this->_getParam('z_intro'));	
		$z_photo= $this->obj->upload($_FILES['z_photo']);
		$z_url= trim($this->_getParam('z_url'));
		$z_count= intval($this->_getParam('z_count'));
		$z_name2= trim($this->_getParam('z_name2'));
		
		$f_name= trim($this->_getParam('f_name'));	
		$f_intro= trim($this->_getParam('f_intro'));	
		$f_photo= $this->obj->upload($_FILES['f_photo']);
		$f_url= trim($this->_getParam('f_url'));
		$f_count= intval($this->_getParam('f_count'));
		$f_name2= trim($this->_getParam('f_name2'));		

		$author= $this->user;
		$channel= $this->channel;
		if(!$z_photo || !$f_photo){
			$this->flash('上传图片有误', '/vote/debate/add',2);
			return false;
		}
		if(!$name ||  !$intro || !$postdate || !$z_name || !$z_intro || !$z_name2 || !$f_name || !$f_intro || !$f_name2){
			$this->flash('请检查输入是否完整', '/vote/debate/add',2);
			return false;
		}

		$data= compact("author","channel","name","photo","intro","level","postdate","z_name","z_intro","z_photo","z_url","z_count","z_name2","f_name","f_intro","f_photo","f_url","f_count","f_name2");
		$return= $this->obj->insert($data);
		
		if(false === $return) {			
            $this->error($this->_table->error);
        }else{				
			$this->flash('添加成功', '/vote/debate/publish/id/'.intval($return));
		}		
	}

	public function delAction(){
		$this->_checkPermission('debate', 'del');
		$id= $this->_getParam('id');		
		//$this->obj->delete('id = ' . $id);
		if($this->obj->delete($id)){
			$this->flash('删除成功', '/vote/debate/');	
		}else{
			$this->flash('删除失败', '/vote/debate/');	
		}
	}

	public function publishAction(){
		$this->_checkPermission('debate', 'publish');
		$id= $this->_getParam('id');	
		$where = $this->db->quoteInto('id = ?', $id );
		$order = 'id';
		$data = $this->obj->fetchRow($where, $order)->toArray();
		$checked[$data['level']]="checked";
		$select[$data['status']]="selected=\"selected\"";
		$data['checked']= $checked;
		$data['select']= $select;
		$data['postdate']= date('Y-m-d H:i:s',$data['postdate']);
		$this->view->data = $data;	
		$path= $this->getChannelConfig($this->channel)->path->published.'pk';
		$this->obj->makedir($path);
		$path.= "/$id.html";
		$html= $this->view->render('/debate/publish.phtml');		
		if(file_put_contents($path,$html)){				
			$this->flash('发布成功', '/vote/debate/',2);	
		}else{
			$this->flash('发布失败', '/vote/debate/',2);	
		}
	}
}