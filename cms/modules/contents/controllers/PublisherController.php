<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'contents/models/Publisher.php';


class Contents_PublisherController extends BaseController
{
	private $channel_db;
	private $obj;
	public function init()
	{
    	$this->channeldb = $this->getChannelDbAdapter();
    	$this->obj=new Publisher($this->channeldb);
	}
	public function indexAction()
	{
        $this->_checkPermission('publisher', 'index');
    	$result=$this->obj->getList();
    	$this->view->result=$result;
	}
	public function addAction()
	{
        $this->_checkPermission('publisher', 'add');
	}
	public function createAction()
	{
        $this->_checkPermission('publisher', 'add');
    	$this->obj->setId($this->_getParam('cid'));
    	$this->obj->setName($this->_getParam('name'));
    	$this->obj->setIcon($this->_getParam('icon'));
    	$this->obj->setUrl($this->_getParam('url'));
    	$this->obj->add();
		$this->flash('添加文章来源成功', '/contents/publisher/');
	}
	public function editAction()
	{
        $this->_checkPermission('publisher', 'edit');
		$id=$this->_getParam('id');
		$this->obj->init($id);
    	$this->view->id=$id;
    	$this->view->name=$this->obj->getName();
    	$this->view->icon=$this->obj->getIcon();
    	$this->view->url=$this->obj->getUrl();
	}
	public function saveAction()
	{
        $this->_checkPermission('publisher', 'edit');
    	$this->obj->setId($this->_getParam('id'));
    	$this->obj->setName($this->_getParam('name'));
    	$this->obj->setIcon($this->_getParam('icon'));
    	$this->obj->setUrl($this->_getParam('url'));
    	$this->obj->modify();
		$this->flash('修改文章来源成功', '/contents/publisher/');
	}
	public function deleteAction()
	{
        $this->_checkPermission('publisher', 'delete');
    	$this->obj->setId($this->_getParam('id'));
    	$this->obj->delete();
		$this->flash('删除文章来源成功', '/contents/publisher/');
	}
}