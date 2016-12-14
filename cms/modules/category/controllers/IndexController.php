<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'category/models/Category.php';
require_once MODULES_PATH . 'page/models/Page.php';


class Category_IndexController extends BaseController
{
	private $channel_db;
	private $obj;
    protected $_page_table;
	public function init()
	{
    	$this->channel_db = $this->getChannelDbAdapter();
    	$this->obj=new Category($this->channel_db);
        Zend_Db_Table::setDefaultAdapter($this->channel_db);
        $this->_page_table = new PageTable();
	}
	//列表页
	public function indexAction()
	{
        $this->_checkPermission('category', 'index');
    	$parent_id=$this->_getParam('cid') ? $this->_getParam('cid') : 0;
    	$this->view->parent_id=$parent_id;
    	$all_list=Category::getAllList($this->channel_db);
    	$this->view->type_select=$this->obj->getTypeSelect();
    	$this->view->all_list=$all_list;
	}
	//显示添加页面
	public function addAction()
	{
        $this->_checkPermission('category', 'add');
    	#接收父类别ID
		$parent_cid=$this->_getParam('parent_cid');
    	$this->view->parent_cid=$parent_cid;
    	#类别列表
		$parent_options=Category::getOptions($this->channel_db,'顶级类别');
    	$this->view->parent_options=$parent_options;
    	#类型列表
    	$this->view->type_select=$this->obj->getTypeSelect();
    	#栏目列表
    	$this->view->page_options=PageTable::getOptions($this->channel_db);
	}
	//执行添加
	public function createAction()
	{
        $this->_checkPermission('category', 'add');
		$publisher_dir=$this->_getParam('publisher_dir');
    	#创建目录
    	if(!empty($publisher_dir))
    	{
	        if (!preg_match('/^\/[a-z0-9\-\_\/]+\/$|^\/$/i', $publisher_dir)) {
	            die('目录名称只能由字母或数字短横线或下划线组成且必须由"/"开头和结尾');
	        }
	    	$publisher_path=$this->getChannelConfig()->path->published;
	    	$workplace_path=$this->getChannelConfig()->path->workplace;
    		@mkdir($publisher_path.$publisher_dir,0775,true);
    		@mkdir($workplace_path.$publisher_dir,0775,true);
    	}
    	$this->obj->setId($this->_getParam('cid'));
    	$this->obj->setParentId($this->_getParam('parent_id'));
    	$this->obj->setName($this->_getParam('name'));
    	$this->obj->setPublisherDir($publisher_dir);
    	$this->obj->setType($this->_getParam('type'));
    	$this->obj->setChannelId($this->_getParam('channel_id'));
    	$this->obj->setStatus(1);
    	$this->obj->add();
    	#刷新列表排序
    	Category::refresh($this->channel_db);
    	#生成推送分类列表文件
    	Category::makePushCategoryFile($this->channel_db,$this->getChannelConfig()->path->push_category_file);
		$this->flash('添加类别成功', '/category/');
	}
	//显示编辑页面
	public function editAction()
	{
        $this->_checkPermission('category', 'edit');
    	#接收类别ID
		$cid=$this->_getParam('cid');
		#初始化类别
		$this->obj->init($cid);
    	$this->view->parent_cid=$this->obj->getParentId();
    	$this->view->cid=$cid;
    	$this->view->name=$this->obj->getName();
    	$this->view->publisher_dir=$this->obj->getPublisherDir();
    	$this->view->type=$this->obj->getType();
    	$this->view->channel_id=$this->obj->getChannelId();
    	$this->view->status=$this->obj->getStatus();
    	$this->view->type_select=$this->obj->getTypeSelect();
    	#类别列表
    	$this->view->parent_options=Category::getOptions($this->channel_db,'顶级类别');
    	#栏目列表
    	$this->view->page_options=PageTable::getOptions($this->channel_db);
	}
	//编辑保存
	public function saveAction()
	{
        $this->_checkPermission('category', 'edit');
		$publisher_dir=$this->_getParam('publisher_dir');
    	#创建目录
    	if(!empty($publisher_dir))
    	{
	        if (!preg_match('/^\/[a-z0-9\-\_\/]+\/$|^\/$/i', $publisher_dir)) {
	            die('目录名称只能由字母或数字短横线或下划线组成且必须由"/"开头和结尾');
	        }
	    	$publisher_path=$this->getChannelConfig()->path->published;
	    	$workplace_path=$this->getChannelConfig()->path->workplace;
    		@mkdir($publisher_path.$publisher_dir,0775,true);
    		@mkdir($workplace_path.$publisher_dir,0775,true);
    	}
    	$this->obj->setParentId($this->_getParam('parent_id'));
    	$this->obj->setId($this->_getParam('cid'));
    	$this->obj->setName($this->_getParam('name'));
    	$this->obj->setPublisherDir($publisher_dir);
    	$this->obj->setType($this->_getParam('type'));
    	$this->obj->setChannelId($this->_getParam('channel_id'));
    	$this->obj->modify();
    	#刷新列表排序
    	Category::refresh($this->channel_db);
    	#生成推送分类列表文件
    	Category::makePushCategoryFile($this->channel_db,$this->getChannelConfig()->path->push_category_file);
		$this->flash('修改类别成功', '/category/');
	}
	//删除
	public function deleteAction()
	{
        $this->_checkPermission('category', 'delete');
		$cid=$this->_getParam('cid');
    	$this->obj->setId($cid);
    	$this->obj->delete();
    	#刷新列表排序
    	Category::refresh($this->channel_db);
    	#生成推送分类列表文件
    	Category::makePushCategoryFile($this->channel_db,$this->getChannelConfig()->path->push_category_file);
		$this->flash('删除类别成功', '/category/');
	}
	//隐藏分类
	public function hideAction()
	{
        $this->_checkPermission('category', 'hide');
		$cid=$this->_getParam('cid');
    	$this->obj->setId($cid);
    	$this->obj->hide();
		$this->flash('隐藏类别成功', '/category/');
	}
	//显示分类
	public function displayAction()
	{
        $this->_checkPermission('category', 'display');
		$cid=$this->_getParam('cid');
    	$this->obj->setId($cid);
    	$this->obj->display();
		$this->flash('显示类别成功', '/category/');
	}
}