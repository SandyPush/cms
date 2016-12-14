<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'vote/models/vote.php';


class Vote_IndexController extends BaseController
{
	private $channel;
	private $user;
	private $obj;
	private $interface;


	public function init(){
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];
		$this->user= $user['username'];
		$vote_db = $this->getVoteDbAdapter();
    	$this->obj=new Vote($vote_db);	
		$config= new Zend_Config_Ini(ROOT_PATH . 'config.vote.ini');
		$this->interface= $config->url->interface;	
	}

	public function indexAction(){
		$this->_checkPermission('vote', 'list');
		$title= trim($this->_getParam('title'));	
		$starttime= strtotime($this->_getParam('starttime'));
		$endtime= strtotime($this->_getParam('endtime'));
		$status= intval($this->_getParam('status'));
		$user= trim($this->_getParam('user'));
		$page= $this->_getParam('page',1);
		$perpage= $this->_getParam('perpage',20);	
		$this->view->data= $this->obj->getVotetList($this->channel,$title,$starttime, $endtime, $status ,$user,$page,$perpage);	
		$total = $this->obj->getCount($this->channel,$title,$starttime, $endtime, $status ,$user);
		$search_para='&title='.$title.'&user='.$user.'&starttime='.$starttime.'&endtime='.$endtime.'&status='.$status;
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__'.$search_para);
		$select[$status]="selected=\"selected\"";
        $this->view->title = $title;		
		$this->view->starttime = $this->_getParam('starttime');
		$this->view->endtime = $this->_getParam('endtime');			
		$this->view->select = $select;		
		$this->view->user  = $user;
		$this->view->pagebar = $pagebar;
		$this->view->headScript()->appendFile('/scripts/date/WdatePicker.js');
	}

	public function addAction(){
		$this->_checkPermission('vote', 'add');
		$this->view->headScript()->appendFile('/scripts/date/WdatePicker.js');
	}

	public function editAction(){
		$this->_checkPermission('vote', 'edit');
		$vid= trim($this->_getParam('vid'));
		$this->view->data = $this->obj->getVote($vid);
		$status= $this->view->data['status'];
		$select[$status]="selected=\"selected\"";
		$this->view->select = $select;	
		if(!$vid)$this->flash('操作有误', '/vote/index/');	
		$this->view->headScript()->appendFile('/scripts/date/WdatePicker.js');
	}

	public function updateAction(){
		$this->_checkPermission('vote', 'edit');
		$vid= trim($this->_getParam('vid'));
		$title= trim($this->_getParam('title'));	
		$starttime= strtotime($this->_getParam('starttime'));
		$endtime= strtotime($this->_getParam('endtime'));
		$status= intval($this->_getParam('status'));
		$user= $this->user;
		if(!$title || !$starttime || !$endtime){
			$this->flash('请检查输入是否完整', "/vote/index/edit/vid/$vid",2);
			return false;
		}
		if($this->obj->updateVote($vid,$title,$starttime, $endtime, $status ,$user)){	
			file_get_contents($this->interface.$vid);
			$this->flash('操作已成功', '/vote/index/');	
		}
		else{
			$this->flash('操作失败', "/vote/index/edit/vid/$vid");
		}
	}

	public function createAction(){
		$this->_checkPermission('vote', 'add');
		$title= trim($this->_getParam('title'));	
		$starttime= strtotime($this->_getParam('starttime'));
		$endtime= strtotime($this->_getParam('endtime'));
		$status= intval($this->_getParam('status'));
		$author= $this->user;
		if(!$title || !$starttime || !$endtime){
			$this->flash('请检查输入是否完整', '/vote/index/add',2);
			return false;
		}
		if($vid= $this->obj->createVote($this->channel,$title,$starttime, $endtime, $status ,$author)){		
			file_get_contents($this->interface.$vid);
			$this->flash('操作已成功', '/vote/index/');	
		}else{
			$this->flash('操作失败', '/vote/index/add');	
		}
	}

	public function delAction(){
		$this->_checkPermission('vote', 'del');
		$ids= (array)$this->_getParam('vid');		
		if(!$ids){
			$this->flash('error!', '/vote/index/');	
			return false;
		}
		$ids= implode(',',$ids);			
		$this->obj->delVote($ids);	
		file_get_contents($this->interface.$this->_getParam('vid'));
		$this->flash('操作已成功', '/vote/index/');
	}

	public function codeAction(){
		$this->_checkPermission('vote', 'list');
		$vid= trim($this->_getParam('vid'));
		$this->view->data = $this->obj->getVote($vid);
	}
}