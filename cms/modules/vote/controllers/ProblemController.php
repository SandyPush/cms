<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'vote/models/vote.php';
require_once MODULES_PATH . 'vote/models/problem.php';


class Vote_ProblemController extends BaseController
{
	private $channel;
	private $user;
	private $vid;
	private $vote;
	private $obj;
	private $interface;

	public function init(){
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];
		$this->user= $user['username'];
		$vote_db = $this->getVoteDbAdapter();
		$this->vote=new Vote($vote_db);	
    	$this->obj=new Problem($vote_db);
		$config= new Zend_Config_Ini(ROOT_PATH . 'config.vote.ini');
		$this->interface= $config->url->interface;
	}

	public function indexAction(){	
		$this->_checkPermission('vote', 'list');
		$this->vid= intval($this->_getParam('vid'));		
		$this->view->vid = $this->vid;	
		$this->view->data= $this->obj->getProblemList($this->vid);	
		
		if(!$this->vid)$this->flash('error!', '/vote/index/');	
        $this->view->vote = $this->vote->getVote($this->vid);			
	}

	public function addAction(){
		$this->_checkPermission('vote', 'add');
		$this->vid= intval($this->_getParam('vid'));
		$this->view->vid = $this->vid;		
		$this->view->vote = $this->vote->getVote($this->vid);	
	}

	public function editAction(){
		$this->_checkPermission('vote', 'edit');
		$pid= trim($this->_getParam('pid'));
		$this->view->data = $this->obj->getProblem($pid);
		$status= $this->view->data['status'];
		$select[$status]="selected=\"selected\"";
		$this->view->select = $select;	
		if(!$pid)$this->flash('操作有误', '/vote/index/');	

		$this->vid= intval($this->_getParam('vid'));
		$this->view->vid = $this->vid;		
		$this->view->vote = $this->vote->getVote($this->vid);		
	}

	public function updateAction(){
		$this->_checkPermission('vote', 'edit');
		$pid= trim($this->_getParam('pid'));
		$vid= trim($this->_getParam('vid'));
		$title= trim($this->_getParam('title'));	
		$orderby= trim($this->_getParam('orderby'));
		$status= intval($this->_getParam('status'));
		if(!$pid || !$title){
			$this->flash('请检查输入是否完整', "/vote/problem/edit/pid/$pid",2);
			return false;
		}
		if($this->obj->updateProblem($pid,$title,$orderby,$status))	{
			file_get_contents($this->interface.$vid);
			$this->flash('操作已成功', '/vote/problem/index/vid/'.$vid,2);	
		}
		else{
			$this->flash('操作失败', "/vote/problem/edit/pid/$pid",2);
		}
	}

	public function createAction(){
		$this->_checkPermission('vote', 'add');
		$title= trim($this->_getParam('title'));	
		$orderby= intval($this->_getParam('orderby'));
		$vid= trim($this->_getParam('vid'));
	
		if(!$vid || !$orderby || !$title){
			$this->flash('请检查输入是否完整', "/vote/problem/add/vid/$vid",2);
			return false;
		}
		if($this->obj->createProblem($vid,$title,$orderby))	{		
			file_get_contents($this->interface.$vid);
			$this->flash('操作已成功', '/vote/problem/index/vid/'.$vid,2);	
		}else{
			$this->flash('操作失败', "/vote/problem/add/vid/$vid",2);	
		}
	}

	public function delAction(){
		$this->_checkPermission('vote', 'del');
		$ids= (array)$this->_getParam('pid');
		$vid= intval($this->_getParam('vid'));	
		if(!$ids){
			$this->flash('error!', '/vote/problem/index/vid/'.$vid,2);	
			return false;
		}
		$ids= implode(',',$ids);			
		$this->obj->delProblem($ids);	    
		file_get_contents($this->interface.$vid);
		$this->flash('操作已成功', '/vote/problem/index/vid/'.$vid,2);
	}
}