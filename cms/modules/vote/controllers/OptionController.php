<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'vote/models/vote.php';
require_once MODULES_PATH . 'vote/models/problem.php';
require_once MODULES_PATH . 'vote/models/option.php';


class Vote_OptionController extends BaseController
{
	private $channel;
	private $user;
	private $vid;
	private $pid;
	private $vote;
	private $problem;
	private $obj;
	private $interface;

	public function init(){
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];
		$this->user= $user['username'];
		$vote_db = $this->getVoteDbAdapter();
		$this->vote=new Vote($vote_db);	
		$this->problem=new Problem($vote_db);	
    	$this->obj=new Option($vote_db);
		$config= new Zend_Config_Ini(ROOT_PATH . 'config.vote.ini');
		$this->interface= $config->url->interface;
	}

	public function indexAction(){	
		$this->_checkPermission('vote', 'list');
		$this->vid= intval($this->_getParam('vid'));
		$this->view->vid = $this->vid;				
        $this->view->vote = $this->vote->getVote($this->vid);

		$this->pid= intval($this->_getParam('pid'));
		$this->view->pid = $this->pid;		
		$this->view->problem = $this->problem->getProblem($this->pid);

		if(!$this->vid ||!$this->pid)$this->flash('error!', '/vote/index/');	
		$this->view->data= $this->obj->getOptionList($this->pid);	
	}

	public function addAction(){
		$this->_checkPermission('vote', 'add');
		$this->vid= intval($this->_getParam('vid'));
		$this->view->vid = $this->vid;		
		$this->view->vote = $this->vote->getVote($this->vid);	

		$this->pid= intval($this->_getParam('pid'));
		$this->view->pid = $this->pid;		
		$this->view->problem = $this->problem->getProblem($this->pid);	
	}

	public function editAction(){
		$this->_checkPermission('vote', 'edit');
		$oid= intval($this->_getParam('oid'));
		$this->view->oid = $oid;	

		$this->view->data = $this->obj->getOption($oid);

		$status= $this->view->data['status'];
		$select[$status]="selected=\"selected\"";
		$this->view->select = $select;	

		$ctype= $this->view->data['ctype'];
		$checked[$ctype]="checked";
		$this->view->checked = $checked;
		if(!$oid)$this->flash('操作有误', '/vote/index/');		

		$this->vid= intval($this->_getParam('vid'));
		$this->view->vid = $this->vid;		
		$this->view->vote = $this->vote->getVote($this->vid);	

		$this->pid= intval($this->_getParam('pid'));
		$this->view->pid = $this->pid;		
		$this->view->problem = $this->problem->getProblem($this->pid);	

	
	}

	public function updateAction(){
		$this->_checkPermission('vote', 'edit');
		$title= trim($this->_getParam('title'));	
		$orderby= intval($this->_getParam('orderby'));
		$click= intval($this->_getParam('click'));	
		$other= addslashes($this->_getParam('other'));		
		$ctype= intval($this->_getParam('ctype'));
		$status= intval($this->_getParam('status'));

		$vid= intval($this->_getParam('vid'));
		$pid= intval($this->_getParam('pid'));
		$oid= intval($this->_getParam('oid'));
		if(!$title || !$orderby || !$pid){
			$this->flash('请检查输入是否完整', "/vote/problem/edit/pid/$pid",2);
			return false;
		}
		if($this->obj->updateOption($oid,$title,$orderby,$click,$other,$ctype,$status)){	
			file_get_contents($this->interface.$vid);
			$this->flash('操作已成功', "/vote/option/index/vid/$vid/pid/$pid",2);	
		}
		else{
			$this->flash('操作失败', "/vote/problem/edit/pid/$pid",2);
		}
	}

	public function createAction(){
		$this->_checkPermission('vote', 'add');
		$title= trim($this->_getParam('title'));	
		$orderby= intval($this->_getParam('orderby'));
		$click= intval($this->_getParam('click'));	
		$other= addslashes($this->_getParam('other'));		
		$ctype= intval($this->_getParam('ctype'));

		$vid= intval($this->_getParam('vid'));
		$pid= intval($this->_getParam('pid'));
	
		if(!$title || !$orderby || !$pid){
			$this->flash('请检查输入是否完整', "/vote/option/add/vid/$vid/pid/$pid",2);
			return false;
		}
		if($this->obj->createOption($pid,$title,$orderby,$click,$other,$ctype)){	
			file_get_contents($this->interface.$vid);
			$this->flash('操作已成功', "/vote/option/index/vid/$vid/pid/$pid");	
		}else{
			$this->flash('操作失败', "/vote/option/add/vid/$vid/pid/$pid",2);	
		}
	}

	public function delAction(){
		$this->_checkPermission('vote', 'del');
		$ids= (array)$this->_getParam('oid');
		$vid= intval($this->_getParam('vid'));	
		$pid= intval($this->_getParam('pid'));	
		if(!$ids){
			$this->flash('error!', "/vote/option/index/vid/$vid/pid/$pid",2);	
			return false;
		}
		$ids= implode(',',$ids);			
		$this->obj->delOption($ids);	
		file_get_contents($this->interface.$vid);
		$this->flash('操作已成功', "/vote/option/index/vid/$vid/pid/$pid",2);
	}
}