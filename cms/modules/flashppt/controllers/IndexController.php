<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'flashppt/models/Flashppt.php';

class Flashppt_IndexController extends BaseController
{
	private $obj;
	private $base_path;//资源所在绝对路径
	private $base_url;//资源所在URL地址
	private $db;
	public function init()
	{
    	$db = $this->getChannelDbAdapter();
    	$this->db=$db;
    	$this->obj=new Flashppt($db);
    	$this->base_path=$this->getChannelConfig()->path->images;
    	$this->base_url=$this->getChannelConfig()->url->images;
	}
	public function indexAction()
	{
        $pid = $this->_getParam('pid', 0);
        $oid = $this->_getParam('oid', 0);
        if(empty($pid))$this->error('参数错误', true);
		$this->view->pid=$pid;
        $this->view->oid=$oid;
		$data=$this->obj->listPage($pid,NULL,NULL,$oid);
        //$data=$this->obj->listPage($pid,1,15);
		$this->view->data=$data;
	}
	public function addAction()
	{
        $pid = $this->_getParam('pid', NULL);
        if(empty($pid))$this->error('参数错误', true);
		$this->view->pid=$pid;
        $this->view->oid=$this->_getParam('oid', 0);
		$this->view->level_select= array(0,1,2,3,4,5,6,7,8,9);
        $settings = Zend_Registry::get('settings');
        $this->view->iconStyle=$settings->iconStyle;
	}
	public function createAction()
	{
        $pid = $this->_getParam('pid', 0);
        if(empty($pid))$this->error('参数错误', true);
        $oid = $this->_getParam('oid', 0);
        if(empty($oid))$this->error('参数错误', true);
        $title=$this->_getParam('title', 0);
        $smallImage=$this->_getParam('image_small', '');
		$data=array(
			'fpid'=>0,
			'oid'=>$oid,
            'iconStyle'=>$this->_getParam('iconStyle', ''),
            'title'=>$title,
			'stitle' => $this->_getParam('stitle', $title),
			'description'=>$this->_getParam('description', $title),
			'url'=>$this->_getParam('url', 0),
			'image'=>$this->_getParam('image', $smallImage),
			'image_small' => $smallImage,
			'level'=>$this->_getParam('level', 0),
			'lastuid'=>$this->_user['uid'],
		);
		$this->obj->add($data,$pid);
		/*
        $xmlfilepath=$this->getChannelConfig()->path->flashppt;
		file_exists($xmlfilepath) OR mkdir($xmlfilepath,0755);
		$this->obj->makeXML($xmlfilepath,$pid.'_'.$oid);
        */
		$this->flash('添加头图成功', '/flashppt/index/index/oid/'.$oid.'/pid/'.$pid);
	}
	public function editAction()
	{
        $fpid = $this->_getParam('fpid', 0);
        if(empty($fpid))$this->error('参数错误', true);
        $pid = $this->_getParam('pid', NULL);
        $row=$this->obj->init($fpid,$pid);
		$this->view->fpid=$fpid;
		$this->view->pid=$pid;
        $this->view->oid=$this->_getParam('oid', 0);
		$this->view->data=$row;
		$this->view->level_select=array(0,1,2,3,4,5,6,7,8,9);
        $settings = Zend_Registry::get('settings');
        $this->view->iconStyle=$settings->iconStyle;
	}
	public function saveAction()
	{
        $pid = $this->_getParam('pid', 0);
        if(empty($pid))$this->error('参数错误', true);
        $fpid = $this->_getParam('fpid', 0);
        if(empty($fpid))$this->error('参数错误', true);
        $oid = $this->_getParam('oid', 0);
        if(empty($oid))$this->error('参数错误', true);
        //print_r(mysql_escape_string($this->_getParam('title', 0)));
        //exit;
		$data=array(
			'fpid'=>$fpid,
            'oid'=>$oid,
            'iconStyle'=>$this->_getParam('iconStyle', ''),
			'title'=>htmlspecialchars($this->_getParam('title', 0)),
			'stitle' => htmlspecialchars($this->_getParam('stitle', '')),
			'description'=>htmlspecialchars($this->_getParam('description', 0)),
			'url'=>$this->_getParam('url', 0),
			'image'=>$this->_getParam('image', 0),
			'image_small' => $this->_getParam('image_small', ''),
			'level'=>$this->_getParam('level', 0),
			'lastuid'=>$this->_user['uid'],
		);
		$this->obj->add($data,$pid);
		/*
        $xmlfilepath=$this->getChannelConfig()->path->flashppt;
		file_exists($xmlfilepath) OR mkdir($xmlfilepath,0755);
		$this->obj->makeXML($xmlfilepath,$pid.'_'.$oid);
        */
		$this->flash('添加头图成功', '/flashppt/index/index/oid/'.$oid.'/pid/'.$pid);
	}
	public function deleteAction()
	{
        $fpid = $this->_getParam('fpid', 0);
        if(empty($fpid))$this->error('参数错误', true);
        $pid = $this->_getParam('pid', 0);
        if(empty($pid))$this->error('参数错误', true);
        $oid = $this->_getParam('oid', 0);
        if(empty($oid))$this->error('参数错误', true);
        $this->obj->delete($pid,$fpid,$oid);
		/*
        $xmlfilepath=$this->getChannelConfig()->path->flashppt;
		file_exists($xmlfilepath) OR mkdir($xmlfilepath,0755);
		$this->obj->makeXML($xmlfilepath,$pid.'_'.$oid);
        */
		$this->flash('删除头图成功', '/flashppt/index/index/oid/'.$oid.'/pid/'.$pid);
	}
	public function pushAction()
	{
        $fpid = $this->_getParam('fpid', 0);
        if(empty($fpid))$this->error('参数错误', true);
    	$this->view->fpid=$fpid;
        $level = $this->_getParam('level', 0);
    	$this->view->level=$level;
		require_once MODULES_PATH . 'page/models/Page.php';
    	#栏目列表
    	$this->view->page_options=PageTable::getAllList($this->db);
    	#所属PID
    	$this->view->pids=$this->obj->getPids($fpid);
	}
	public function pushedAction()
	{
        $fpid = $this->_getParam('fpid', 0);
        if(empty($fpid))$this->error('参数错误', true);
        $level = $this->_getParam('level', 0);
        $pid_arr = $this->_getParam('pid_arr', array());
        $this->obj->deletePage($fpid);
        if(!empty($pid_arr))
        {
        	foreach($pid_arr as $pid)
        	{
        		$this->obj->addPage($fpid,$pid,$level);
        	}
        }
		$this->flash('推送头图成功', '/flashppt/index/index/pid/'.$pid);
	}
	public function orderAction()
	{
		$pid=$this->_getParam('pid', 0);
        if(empty($pid))$this->error('参数错误', true);
        $oid=$this->_getParam('oid', 0);
        if(empty($oid))$this->error('参数错误', true);
		$fpids=$this->_getParam('fpids', array());
		$levels=$this->_getParam('level', array());	
		$this->obj->deleteFp($pid,$oid);
        krsort($fpids);
        krsort($levels);

		foreach($fpids as $i => $fpid)
		{
			$level= $levels[$i];
			$this->obj->addPage($fpid,$pid,$level,$oid);
		}
		/*
        $xmlfilepath=$this->getChannelConfig()->path->flashppt;
		file_exists($xmlfilepath) OR mkdir($xmlfilepath,0755);
		$this->obj->makeXML($xmlfilepath,$pid.'_'.$oid);
        */
		$this->flash('保存排序成功', '/flashppt/index/index/oid/'.$oid.'/pid/'.$pid);

	}
}
