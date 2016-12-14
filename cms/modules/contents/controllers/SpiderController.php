<?php
/** @see BaseController */
require_once 'BaseController.php';

class Contents_SpiderController extends BaseController
{
	const TABLE='news';
	private $channel;
	private $db;
	private $obj;
	public function init()
	{	
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];	    
	    $this->db= $spiderdb= Util::getDbAdapter(Zend_Registry::get('env_config')->spiderdb, 'spider');
		$spiderdb->query('set names utf8');
		
	}
	public function indexAction()
	{		
        $this->_checkPermission('spider', 'list');
		$this->view->layout()->disableLayout();
        $perpage = 30;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
		$select=$this->db->select();
		$select->from(self::TABLE,array('id','source','title','pub_date','author','flag'));
		$title = trim($this->_getParam('title'));
		$source = trim($this->_getParam('source'));
		if($title)$select->where("title like '%$title%'");
		if($source)$select->Where("source = '$source'");
		$add= ' where 1';
		$urladd= '';
		if($title)$add .= " and (title like '%$title%')";
		if($source)$add .= " and (source = '$source')";
		if($title)$urladd .= "&title={$title}";
		if($source)$urladd .= "&source={$source}";
		$this->view->title = $title;
		$this->view->source = $source;

		$select->order("pub_date DESC");
		$select->limitPage($page,$perpage);
		$result=$this->db->fetchAll($select);
    	$this->view->result=$result;
        $total = $this->db->fetchOne("SELECT COUNT(*) FROM ".self::TABLE.$add);
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__'.urlencode($urladd));
        $this->view->pagebar = $pagebar;
	}
	public function pushAction()
	{
        $id = $this->_getParam('id', 0);
		$select=$this->db->select();
		$select->from(self::TABLE,array('id','source','title','pub_date','author','flag','subtitle','summary','content','keywords'));
		$select->where("id=?",$id);
		$result=$this->db->fetchRow($select);
    	$this->view->result=$result;    	
    	$channel_name= $this->channel;	
		$flag = $this->db->fetchOne("SELECT flag FROM ".self::TABLE." where id='$id'");
		if(strpos($flag, $channel_name)===false){
			$flag= $flag? $flag.",".$channel_name: $channel_name;
			$where = $this->db->quoteInto('id = ?', $id);	
    		$this->db->update(self::TABLE, array('flag'=>$flag), $where);
		}
	}
	public function previewAction()
	{
        $id = $this->_getParam('id', 0);
		$select=$this->db->select();
		$select->from(self::TABLE,array('id','source','title','pub_date','author','flag','subtitle','summary','content','keywords'));
		$select->where("id=?",$id);
		$result=$this->db->fetchRow($select);
    	$this->view->result=$result;
	}
	
}