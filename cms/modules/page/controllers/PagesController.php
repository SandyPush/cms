<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'template/models/Templates.php';
require_once MODULES_PATH . 'page/models/Page.php';
require_once MODULES_PATH . 'object/models/Objects.php';
require_once MODULES_PATH . 'object/models/ObjContents.php';
require_once LIBRARY_PATH . 'Publish/Page.php';

class Page_PagesController extends BaseController
{
    protected $_db;
	protected $channel_db;
    protected $_templates_table;
    protected $_page_table;
    protected $_obj_contents_table;
    protected $_objects_table;
    
    public function init()
    {
	    $channel_db = $this->getChannelDbAdapter();
	    $this->channel_db=$channel_db;
	    $this->_db=$channel_db;
        
        Zend_Db_Table::setDefaultAdapter($channel_db);
        
        $this->_templates_table = new TemplatesTable($channel_db);
        $this->_page_table = new PageTable($channel_db);
        $this->_obj_contents_table = new ObjContentsTable();
        $this->_objects_table = new ObjectsTable();
    }
    
    public function indexAction()
    {
        $this->_checkPermission('pages', 'index');
    	$all_list=PageTable::getAllList($this->channel_db);
		foreach ($all_list as $k => $p) {
			$p['published_url'] = Util::concatUrl($this->getChannelConfig()->url->published, $p['url']);
			$all_list[$k] = $p;
		}

    	$this->view->all_list=$all_list;
    	$this->view->channel=$this->_user['channel'];
    }
    
    public function createAction()
    {
        $this->_checkPermission('pages', 'add');
        $tree = $this->_page_table->getTree();
        $this->_getFlatTree($flat_tree, $tree);
        
        $pages = array(0 => '无');
        foreach ($flat_tree as $item) {
            $pages[$item['pid']] = str_repeat('--', $item['_depth'] * 2) . ' ' . $item['name'];
        }

        $this->view->templates = $this->_templates_table->fetchByType(3);
        $this->view->articleTemplates = $this->_templates_table->fetchByType(1);
        $this->view->pages = $pages;
        
        if ($this->isPost()) {
            $page = array(
                'name' => $this->_getParam('name'),
                'parent' => (int) $this->_getParam('parent'),
                'template' => (int) $this->_getParam('template'),
                'articleTemplates' => (int) $this->_getParam('articleTemplates'),
                'url' => $this->_getParam('url'),
                'title' => $this->_getParam('title'),
                'keywords' => $this->_getParam('keywords'),
                'description' => $this->_getParam('desc'),
                'uid' => $this->_user['uid'],
            );
           $this->_page_table->insert($page);
            //if (false === $this->_page_table->insert($page)) {
				if($this->_page_table->error){
					echo "<script>alert('{$this->_page_table->error}');</script>";
					//$this->flash($this->_page_table->error, '/page/pages/create',2);                   
					return false;		
				}
            //}
            
            $this->flash('栏目创建成功', '/page/pages/');
        }
    }
    
    public function editAction()
    {
        $this->_checkPermission('pages', 'edit');
        $pid = (int) $this->_getParam('pid', 0);
        if (!$pid || false === $page = $this->_page_table->find($pid)->current()) {
            $this->error('请指定栏目页', true);
        }
        $tree = $this->_page_table->getTree();
        $this->_getFlatTree($flat_tree, $tree);
        
        $pages = array(0 => '无');
        foreach ($flat_tree as $item) {
            $pages[$item['pid']] = str_repeat('--', $item['_depth'] * 2) . ' ' . $item['name'];
        }
        
        $this->view->page = $page;
        $this->view->templates = $this->_templates_table->fetchByType(3);
        $this->view->articleTemplates = $this->_templates_table->fetchByType(1);
        $this->view->pages = $pages;        
        
        if ($this->isPost()) {
            $page = array(
                'name' => $this->_getParam('name'),
                'parent' => (int) $this->_getParam('parent'),
                'template' => (int) $this->_getParam('template'),
                'articleTemplates' => (int) $this->_getParam('articleTemplates'),
                'url' => $this->_getParam('url'),
                'title' => $this->_getParam('title'),
                'keywords' => $this->_getParam('keywords'),
                'description' => $this->_getParam('desc'),
            );

            if (false === $this->_page_table->edit($page, "pid = $pid")) {
				echo "<script>alert('{$this->_page_table->error}');</script>";
				//$this->flash($this->_page_table->error, '/page/pages/edit/pid/'. $pid ,2);              
                return false;
            }

            $this->flash('栏目修改成功', '/page/pages/');
        }
    }
    
    public function delAction()
    {
        $this->_checkPermission('pages', 'delete');
        $pid = (int) $this->_getParam('pid', 0);
        if (!$pid || false === $page = $this->_page_table->find($pid)->current()) {
            $this->error('请指定栏目', true);
        }
        
        $this->_page_table->delete('pid = ' . $pid);
        $this->_page_table->update(array('parent' => 0), "parent = $pid");
        
        $this->flash('栏目删除成功', '/page/pages/');
    }
    #预览
    public function previewAction()
    {
        $this->_checkPermission('pages', 'preview');
        $pid = (int) $this->_getParam('pid', 0);
        if (!$pid || false === $page = $this->_page_table->find($pid)->current()) {
            $this->error('请指定栏目', true);
        }
        $pub = new Publish_Page($this->getChannelConfig(), $pid);
        $published = $pub->publishWork();
        if($published===false)exit('预览失败');
        $url_workplace =  Util::concatUrl($this->getChannelConfig()->url->workplace, $page->url);
        header('Location:'.$url_workplace);
        exit;
    }
    #发布
    public function publishAction()
    {
        $this->_checkPermission('pages', 'publish');
        $pid = (int) $this->_getParam('pid', 0);
        if (!$pid || false === $page = $this->_page_table->find($pid)->current()) {
            $this->error('请指定栏目', true);
        }
        $this->rsyncImg();//刷新静态资源 add by huishuai 2015-12-03
        $pub = new Publish_Page($this->getChannelConfig(), $pid);
        $published = $pub->publish();
        if($published===false)exit('发布失败');
        $url_published = Util::concatUrl($this->getChannelConfig()->url->published, $page->url); 
		$user= Zend_Session::namespaceGet('user');		
		$channel= $user['channel'];
		if($channel=='www' || $channel=='lottery' || $channel=='qipai'){
			refresh_cdn_lx($url_published,$channel);
		}
        $this->flash('发布成功', $url_published);
    }

    private function rsyncImg(){
        exec("/VODONE/www/vodone.cms/publish/h5/uploadimg.sh");
    }
    
    private function _getFlatTree(&$tree, $items, $depth = 0)
    {
        if (!is_array($tree)) {
            $tree = array();
        }
        
        foreach ($items as $item) {
            $item['data']['_depth'] = $depth;
            array_push($tree, $item['data']);
            if ($item['childs']) {
                $this->_getFlatTree($tree, $item['childs'], $depth + 1);
            }
        }
    }
}