<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'comment/models/comment.php';


class Comment_IndexController extends BaseController
{
	private $channel;
	private $ctypeid;
	private $obj;

	public function init(){
		$user= Zend_Session::namespaceGet('user');
		$channel= $user['channel'];
		$this->channel= $channel;
		$comment_db = $this->getCommentDbAdapter();
    	$this->obj=new Comment($comment_db);	
		$comment_config=new Zend_Config_Ini(ROOT_PATH . 'config.comment.ini');   
		$channels = $comment_config->channels->toArray();				
		$this->ctypeid= $channels[$channel]?$channels[$channel] :0;	
		//$this->ctypeid= 0;	
	}

	public function indexAction(){
		$this->_checkPermission('comment', 'index');
		$tid= $this->_getParam('tid');	
		$author= $this->_getParam('author');
		$keyword= $this->_getParam('keyword');
		$page= $this->_getParam('page',1);
		$perpage= $this->_getParam('pagenum',30);		
		$this->view->data= $this->obj->getCommentList($this->ctypeid,$tid, $author, $keyword ,$page ,$perpage);	
		$total = $this->obj->getCount($this->ctypeid,$tid, $author, $keyword);
		$search_para='&tid='.$tid.'&author='.$author.'&keyword='.$keyword;
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__'.$search_para);
        $this->view->tid    = $tid;
		$this->view->author = $author;
		$this->view->keyword= $keyword;
		$this->view->pagebar= $pagebar;
		$this->view->pagenum= $perpage;
	}

	public function deleteAction(){
		$this->_checkPermission('comment', 'del');
		$ids= $this->_getParam('ids');
		if(!$ids){
			$this->flash('请勾选要删除的项', '/comment/index/');	
			return false;
		}
		foreach($ids as $id){
			list($tid,$ctype,$location)= array_values($this->obj->getCommentById($id));		
			file_get_contents("http://comment.titan24.com/action.php?action=del&tid=$tid&ctype=$ctype&location=$location");
		}
		$ids= implode(',',$ids);	
		$this->obj->delComment($ids);	    
		$this->flash('操作已成功', '/comment/index/');
	}
}