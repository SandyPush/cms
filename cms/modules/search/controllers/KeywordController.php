<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'search/models/Keyword.php';
require_once MODULES_PATH . 'search/models/Category.php';

class Search_KeywordController extends BaseController
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
		$this->_obj = new Keyword($db);	    	
	}
	//列表页
	public function indexAction()
	{
        $this->_checkPermission('search', 'index');    
		$page = $this->_getParam('page', 1);
		$pagenum= 20;
    	$cid = $this->_getParam('cid')? $this->_getParam('cid'): $this->_getParam('cid2');
		$getChannel = $cid? '' : $this->_getParam('channel');
		$k_word = trim($this->_getParam('k_word'));
		if($k_word){
			$getChannel= $cid= null;
		}
		$where= $k_word? " and k_word='$k_word'" : "";
		$param= $getChannel? "&channel={$getChannel}" :"";
		$param.= $cid ?"&cid={$cid}" :"";
		$param.= $k_word ?"&k_word={$k_word}" :"";
    	$all_list= $this->_obj->getAllList($pagenum, $page, $cid, $getChannel, $where);
    	$this->view->all_list= $all_list;
		$cid_search= Category::getOptions($this->_db,'请选择分类', 0);
		$cid_options= Category::getOptions($this->_db,'请选择分类', 1, $this->channel);
		$this->view->cid_search= $cid_search; 
    	$this->view->cid_options= $cid_options;  	
		$total = $this->_obj->count($cid, $getChannel, $where);
        $pagebar = Util::buildPagebar($total, $pagenum, $page, '?page=__page__'.$param);
		$this->view->pagebar = $pagebar;	
		$channels=Zend_Registry::get('settings')->channels->toArray();
		$channels=  array_merge(array('0'=>'请选择频道'), $channels);
		$this->view->cid = $cid;
		$this->view->k_word = $k_word;
		$this->view->channels = $channels;	
		$this->view->channel =  $this->channel;	
		$this->view->getChannel= $getChannel;		
	}

	//执行添加
	public function createAction()
	{
        $this->_checkPermission('search', 'keyword');
		$data= array();
		$data['cid']= $this->_getParam('cid');      	
    	$data['k_word']= $k_word= $this->_getParam('k_word');
		$data['channel']= $this->channel; 
		$data['rank']= 0; 
		$data['hits']= 0; 
		$data['status']= 1; 
		if($word= $this->_obj->getOne(" and (k_word='$k_word') and channel!=''")){
			$this->flash('对不起,已存在同名的关键词', '/search/keyword/', 3);
			return false;
		}	
		if($word= $this->_obj->getOne(" and (k_word='$k_word')")){
			$this->_obj->modify($data, $word['k_id']);	
		}else{
    		$this->_obj->add($data);
		}  	
		$this->flash('添加关键词成功', '/search/keyword/', 0);
	}

	//执行添加关联词
	public function addawordAction()
	{
        $this->_checkPermission('search', 'associate');
		$data= array();
		$data['k_id']= $k_id= $this->_getParam('id');      	
    	$getAword= trim($this->_getParam('a_word'));
		$getAword= str_replace(' ',',',$getAword);
		$channel= $this->_obj->getChannel($k_id);
		if($channel!= $this->channel){
			$this->flash('没有该频道的权限', '/search/keyword/', 2);
			return false;
		}
		if(!trim($getAword) || !intval($k_id)){
			$this->flash('参数错误', '/search/keyword/', 2);
			return false;
		}
		$aword= $this->_obj->getAword($k_id);
		$awords= array_diff(explode(',', $getAword), explode(',', $aword));
		foreach($awords as $a){
			$data['a_word']= $a;
			$data['rank']= 0; 
			$data['hits']= 0; 
			$data['status']= 1; 
			$this->_obj->addAword($data);
		}
		$this->flash('添加关联词成功', '/search/keyword/', 1);
	}
	//删除关联词
	public function delawordAction()
	{
        $this->_checkPermission('search', 'del');
		$kid= intval($this->_getParam('id'));  
		$aword= trim($this->_getParam('aword'));  
		$returnUrl= trim($this->_getParam('returnUrl')); 
		$row= $this->_obj->getOne(" and (k_id='$kid')");
		if($row){		
			if($row['channel']!= $this->channel){
				$this->flash('该关联词不是当前频道建立的,无权删除!', '/search/keyword/',3);
				return false;
			}	
		}
    	if($this->_obj->delAword($kid, $aword)){  
			$this->flash('删除成功!', $returnUrl, 0);
			return true;
		}else{
			$this->flash('删除失败!', $returnUrl, 3);
			return false;
		}
	}

	//删除
	public function delAction()
	{
        $this->_checkPermission('search', 'del');
		$kid= $this->_getParam('kid');  	
		$row= $this->_obj->getOne(" and (k_id='$kid')");
		if($row){		
			if($row['channel']!= $this->channel){
				$this->flash('该关键词不是当前频道建立的,无权删除!', '/search/keyword/',3);
				return false;
			}
			if($row['a_word']!= ''){
				$this->flash('该关键词含有关联词,不能删除,请先清空关联词!', '/search/keyword/',3);
				return false;
			}
		}
    	$this->_obj->delete($kid);      
		$this->flash('删除成功', '/search/keyword/',1);
	}
	
	//清除关联词
	public function clearAction()
	{
        $this->_checkPermission('search', 'del');
		$kid= $this->_getParam('kid');  	
		$row= $this->_obj->getOne(" and (k_id='$kid')");
		if($row){		
			if($row['channel']!= $this->channel){
				$this->flash('该关键词不是当前频道建立的,无权删除!', '/search/keyword/',3);
				return false;
			}
		}
    	$this->_obj->clear($kid);      
		$this->flash('清除成功', '/search/keyword/',1);
	}
}