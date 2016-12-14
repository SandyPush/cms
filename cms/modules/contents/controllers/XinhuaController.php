<?php
/** @see BaseController */
require_once 'BaseController.php';

class Contents_XinhuaController extends BaseController
{
	const TABLE='article';
	private $db;
	private $channel_db;
	private $obj;
	private $publisherObj;
	private $categoryObj;
	private $resourceObj;
	private $templateObj;
	private $base_path;//资源所在绝对路径
	private $base_url;//资源所在URL地址
	private $push_url;//发布URL
	public function init()
	{
	    $channel_db = $this->getChannelDbAdapter();
	    $this->channel_db=$channel_db;
	    $db=$this->getMainDbAdapter();
	    $this->db=$db;
	}
	public function indexAction()
	{
        $this->_checkPermission('xinhua', 'list');
		$this->view->layout()->disableLayout();
        $perpage = 30;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
		$select=$this->db->select();
		$select->from(self::TABLE,array('aid','title','postdate','author','inchannel'));
		$select->order("postdate DESC");
		$select->limitPage($page,$perpage);
		$result=$this->db->fetchAll($select);
    	$this->view->result=$result;
        $total = $this->db->fetchOne("SELECT COUNT(*) FROM ".self::TABLE);
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__&'.$search_para.'&order='.$order);
        $this->view->pagebar = $pagebar;
	}
	public function pushAction()
	{
        $aid = $this->_getParam('aid', 0);
		$select=$this->db->select();
		$select->from(self::TABLE,array('aid','title','tags','postdate','author','contents'));
		$select->where("aid=?",$aid);
		$result=$this->db->fetchRow($select);
    	$this->view->result=$result;
    	$channels=Zend_Registry::get('settings')->channels->toArray();
    	$channel_name=$channels[$this->_user['channel']];
    	$this->db->update(self::TABLE,array('inchannel'=>$channel_name),"aid=".$aid);
	}
	public function previewAction()
	{
        $aid = $this->_getParam('aid', 0);
		$select=$this->db->select();
		$select->from(self::TABLE,array('aid','title','tags','postdate','author','contents'));
		$select->where("aid=?",$aid);
		$result=$this->db->fetchRow($select);
    	$this->view->result=$result;
	}
	
}