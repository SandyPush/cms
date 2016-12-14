<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'template/models/Templates.php';
require_once MODULES_PATH . 'page/models/Page.php';
require_once MODULES_PATH . 'video/models/set.php';
require_once MODULES_PATH . 'object/models/Objects.php';
require_once MODULES_PATH . 'object/models/ObjContents.php';
require_once MODULES_PATH . 'category/models/Category.php';

class Video_SetController extends BaseController
{
    protected $_db;
	protected $_channel_db;
	protected $_tbl_templates;
	protected $_tbl_set;
	protected $_tbl_photos;
    protected $categoryObj;

    public function init()
    {
	    $channel_db = $this->getChannelDbAdapter();
	    $this->_channel_db = $channel_db;
	    $this->_db = $channel_db;
        
        Zend_Db_Table::setDefaultAdapter($channel_db);
        
        $this->_tbl_set = new SetTable($channel_db);
		$this->_tbl_templates = new TemplatesTable($channel_db);
        $this->categoryObj=new Category($channel_db);
    }
    
    public function indexAction()
    {
        $this->_checkPermission('set', 'index');
        $perpage = 20;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
		
		$total = $this->_tbl_set->count();
		$result = $this->_tbl_set->fetch('',$perpage, ($page - 1) * $perpage);
		
		$pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__');
        $all=array();
        foreach($result as $value){
            $this->categoryObj->init($value['cid']);
            $value['categoryName']=$this->categoryObj->getName() ? $this->categoryObj->getName() : '顶级分类';
            $all[]=$value;
        }
    	$this->view->all = $all;
		$this->view->pagebar = $pagebar;
    	$this->view->channel = $this->_user['channel'];
    }

    public function createAction()
    {
        $this->_checkPermission('set', 'create');
        $this->view->categories = Category::getOptions($this->_channel_db, '顶级类别');
        
        if ($this->isPost()) {
            $video = array(
                'title' => $this->_getParam('title'),
                'cid' => $this->_getParam('cid'),
                'intro' => $this->_getParam('intro', ''),
                'uid' => $this->_user['uid']
            );
			
            $this->categoryObj->init($this->_getParam('cid'));
            if(!$this->categoryObj->getChannelId()){
                $this->error('此分类未绑定频道，请选择其他常规分类', 1);
            }
            
			if (false === ($sid = $this->_tbl_set->insert($video))) {
				$this->error($this->_tbl_set->error);
			} else {
				$this->flash('视频集创建成功', '/video/set?sid=' . $sid);
				return true;
			}
        }
    }
    	
    public function editAction()
    {
        $this->_checkPermission('set', 'edit');
        $sid = (int) $this->_getParam('sid', 0);
        if (!$sid || false === $set = $this->_tbl_set->find($sid)->current()) {
            $this->error('请指定视频集', true);
        }

		$this->view->set = $set->toArray();
    	$this->view->categories = Category::getOptions($this->_channel_db, '顶级类别');
        
        if ($this->isPost()) {
            $set = array(
                'title' => $this->_getParam('title'),
                'cid' => (int) $this->_getParam('cid', 0),
                'intro' => $this->_getParam('intro', ''),
            );

            if (false === $this->_tbl_set->edit($set, "sid = $sid")) {
				$this->error($this->_tbl_set->error);
            }
			
			$this->_tbl_set->updateTags($vid, $this->_getParam('keywords'));

            $this->flash('视频集修改成功', '/video/set/', 2);
        }
    }
   
    public function disableAction()
    {
        $this->_checkPermission('set', 'disable');
        $sid = (int) $this->_getParam('sid', 0);
        if (!$sid || false === $set = $this->_tbl_set->find($sid)->current()) {
            $this->error('请指定视频集', true);
        }
        
        $this->_tbl_set->disable($sid);
        
        $this->flash('视频集删除成功', '/video/set');
    }

     /*
    public function previewAction()
    {
        //$this->_checkPermission('albums', 'preview');
        $aid = (int) $this->_getParam('aid', 0);
        if (!$aid || false === $album = $this->_tbl_albums->find($aid)->current()) {
            $this->error('请指定图集', true);
        }

        $url_workplace =  Util::concatUrl($this->getChannelConfig()->url->workplace, $this->_tbl_albums->getUrl($album->createdate, $aid));
        header('Location:'.$url_workplace);
        exit;
    }
    */
    
}