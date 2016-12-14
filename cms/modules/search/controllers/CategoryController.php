<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'search/models/Category.php';

class Search_CategoryController extends BaseController
{
	private $channel;
	private $user;	

	protected $_db;
	protected $_obj; 

	public function init()
	{
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];
		$this->user= $user['username'];
		$config= new Zend_Config_Ini(ROOT_PATH . 'config.ini'); 		
		$this->_db = $db = Zend_Db::factory('PDO_MYSQL', $config->titansearchdb->toArray());
		$db->query("set names utf8");	
		Zend_Db_Table::setDefaultAdapter($this->_db); 
		$this->_obj = new Category($db);	    	
	}
	//列表页
	public function indexAction()
	{
        $this->_checkPermission('search', 'cate');
    	$parent_id= $this->_getParam('cid') ? $this->_getParam('cid') : 0;
    	$this->view->parent_id= $parent_id;
		$this->view->channel= $this->channel;	
    	$all_list= $this->_obj->getAllList($this->_db);
    	$this->view->all_list= $all_list;
		$parent_options= Category::getOptions($this->_db,'顶级类别', 1, $this->channel);
    	$this->view->parent_options= $parent_options;  		
	}

	//执行添加
	public function createAction()
	{
        $this->_checkPermission('search', 'cate');
		$data= array();
		$data['parent']= $parent= $this->_getParam('parent');      	
    	$data['name']= $this->_getParam('name');
		$data['channel']= $this->channel;		
    	$data['status']= 1;
		$cate= $this->_obj->getOne($parent);
		if($cate){
			if(!$cate['status']){
				$this->flash('不能在已删除的分类下建立子分类!', '/search/category/', 3);
				return false;
			}
			if($cate['channel']!= $this->channel){
				$this->flash('该分类不是当前频道建立的,无权建立子分类!', '/search/category/',3);
				return false;
			}
		}
    	$this->_obj->add($data);	
    	#刷新列表排序
    	Category::refresh($this->_db);    	
		$this->flash('添加类别成功', '/search/category/', 1);
	}

	//编辑保存
	public function updateAction()
	{
        $this->_checkPermission('search', 'cate');
		$cid= $this->_getParam('id');
		$data= array();
		$data['parent']= $parent= $this->_getParam('parent');      	
    	$data['name']= $this->_getParam('name');
		$data['channel']= $this->channel; 
		$cate= $this->_obj->getOne($cid);
		if($parent== $cid){
			$this->flash('父分类不正确!', '/search/category/',3);
			return false;
		}
		if($cate){
			if(!$cate['status']){
				$this->flash('不能修改已删除的分类!', '/search/category/',3);
				return false;
			}
			if($cate['channel']!= $this->channel){
				$this->flash('该分类不是当前频道建立的,无权修改!', '/search/category/',3);
				return false;
			}
		}
    	$this->_obj->modify($data, $cid);
    	#刷新列表排序
    	Category::refresh($this->_db);
		$this->flash('修改类别成功!', '/search/category/',1);
	}
	//隐藏或者显示
	public function deleteAction()
	{
        $this->_checkPermission('search', 'cate');
		$cid= $this->_getParam('id');  
		$status= $this->_getParam('status');  
		$cate= $this->_obj->getOne($cid);
		if($cate){		
			if($cate['channel']!= $this->channel){
				$this->flash('该分类不是当前频道建立的,无权修改!', '/search/category/',3);
				return false;
			}
		}
    	$this->_obj->delete($cid, $status);
    	#刷新列表排序
    	Category::refresh($this->_db);    
		$this->flash('操作类别成功!', '/search/category/',1);
	}

	//删除
	public function removeAction()
	{
        $this->_checkPermission('search', 'cate');
		$cid= $this->_getParam('id');  
		$cate= $this->_obj->getOne($cid);	
    	$this->_obj->remove($cid);
    	#刷新列表排序
    	Category::refresh($this->_db);    
		$this->flash('删除成功!', '/search/category/',1);
	}	
}