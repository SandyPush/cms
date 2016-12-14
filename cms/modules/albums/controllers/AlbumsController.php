<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'template/models/Templates.php';
require_once MODULES_PATH . 'page/models/Page.php';
require_once MODULES_PATH . 'albums/models/albums.php';
require_once MODULES_PATH . 'object/models/Objects.php';
require_once MODULES_PATH . 'object/models/ObjContents.php';
require_once MODULES_PATH . 'category/models/Category.php';
require_once LIBRARY_PATH . 'Publish/Album.php';

class Albums_AlbumsController extends BaseController
{
    protected $_db;
	protected $_channel_db;
	protected $_tbl_templates;
	protected $_tbl_albums;
	protected $_tbl_photos;

    public function init()
    {
	    $channel_db = $this->getChannelDbAdapter();
	    $this->_channel_db = $channel_db;
	    $this->_db = $channel_db;
        
        Zend_Db_Table::setDefaultAdapter($channel_db);
        
        $this->_tbl_albums = new AlbumsTable($channel_db);
		$this->_tbl_templates = new TemplatesTable($channel_db);
    }
    
    public function indexAction()
    {
        //$this->_checkPermission('albums', 'index');
        $cid=$this->_getParam('cid', 0);
        if(!empty($cid)) $where.=" and a.cid='".$cid."'";
        $this->view->cid=$cid;
        $search_para.='cid='.$cid.'&';
        
        $title=$this->_getParam('title', '');
        if(!empty($title)) $where.=" and title LIKE '%".$title."%'";
        $this->view->title=$title;
        $search_para.='title='.urlencode($title).'&';
        
        $realname=$this->_getParam('realname', '');
        if(!empty($realname)) $where.=" and uid IN(SELECT uid FROM users WHERE realname LIKE '%".$realname."%')";
        $this->view->realname=$realname;
        $search_para.='realname='.urlencode($realname).'&';
        
        $perpage = 20;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
		
		$total = $this->_tbl_albums->count($where." and status >= 1");
		$all = $this->_tbl_albums->fetch($perpage, ($page - 1) * $perpage,$where);
		foreach ($all as &$album) {
			$album['cover'] = Util::getPhotoPath($album['cover'], 's', Zend_Registry::get('channel_config')->url->photos);
			$album['preview_url'] = Util::concatUrl($this->getChannelConfig()->url->workplace, $this->_tbl_albums->getUrl($album['createdate'], $album['aid']));
			$album['publish_url'] = Util::concatUrl($this->getChannelConfig()->url->published, $this->_tbl_albums->getUrl($album['createdate'], $album['aid']));
		}
		
		$pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__'.$search_para);

        #分类列表
    	$this->view->category_options=Category::getOptions($this->_channel_db,'全部类别');

    	$this->view->all = $all;
		$this->view->pagebar = $pagebar;
    	$this->view->channel = $this->_user['channel'];
    }
    
    public function createAction()
    {
		//$obj_category = new Category($this->_channel_db);
    	$this->view->categories = Category::getOptions($this->_channel_db, '顶级类别');
        $this->view->templates = $this->_tbl_templates->fetchByType(1);
        
        if ($this->isPost()) {
            $album = array(
                'title' => $this->_getParam('title'),
				'stitle' => $this->_getParam('stitle'),
                'cid' => (int) $this->_getParam('cid', 0),
                'template' => (int) $this->_getParam('template'),
                'link' => $this->_getParam('link'),
                'author' => $this->_getParam('author', ''),
				'source' => $this->_getParam('source', ''),
                //'keywords' => $this->_getParam('keywords'),
                'intro' => $this->_getParam('intro', ''),
                'uid' => $this->_user['uid'],
				'createdate' => time(),
            );
			
			if (false === ($aid = $this->_tbl_albums->insert($album))) {
				$this->error($this->_tbl_albums->error);
			} else {
				$this->_tbl_albums->updateTags($aid, $this->_getParam('keywords'));
				
				$this->flash('图集创建成功', '/albums/photos?aid=' . $aid);
				return true;
			}
        }
    }
    	
    public function editAction()
    {
        //$this->_checkPermission('albums', 'edit');
        $aid = (int) $this->_getParam('aid', 0);
        if (!$aid || false === $album = $this->_tbl_albums->find($aid)->current()) {
            $this->error('请指定图集', true);
        }
		
		$this->view->album = $album->toArray();
		$this->view->album['keywords'] = join(' ', (array) $this->_tbl_albums->getTags($aid));
    	$this->view->categories = Category::getOptions($this->_channel_db, '顶级类别');
        $this->view->templates = $this->_tbl_templates->fetchByType(1);
        
        if ($this->isPost()) {
            $album = array(
                'title' => $this->_getParam('title'),
				'stitle' => $this->_getParam('stitle'),
                'cid' => (int) $this->_getParam('cid', 0),
                'template' => (int) $this->_getParam('template'),
                'link' => $this->_getParam('link'),
                'author' => $this->_getParam('author', ''),
				'source' => $this->_getParam('source', ''),
                'intro' => $this->_getParam('intro', ''),
            );

            if (false === $this->_tbl_albums->edit($album, "aid = $aid")) {
				$this->error($this->_tbl_albums->error);
            }
			
			$this->_tbl_albums->updateTags($aid, $this->_getParam('keywords'));

            $this->flash('图集修改成功', '/albums/albums/', 2);
        }
    }
    
    public function disableAction()
    {
        //$this->_checkPermission('albums', 'delete');
        $aid = (int) $this->_getParam('aid', 0);
        if (!$aid || false === $album = $this->_tbl_albums->find($aid)->current()) {
            $this->error('请指定图集', true);
        }
        
        $this->_tbl_albums->disable($aid);
        
        $this->flash('图集删除成功', '/albums/albums');
    }


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
    
    public function publishAction()
    {
        //$this->_checkPermission('albums', 'publish');
        $aid = (int) $this->_getParam('aid', 0);
        if (!$aid || false === $album = $this->_tbl_albums->find($aid)->current()) {
            $this->error('请指定图集', true);
        }

        $pub = new Publish_Album($this->getChannelConfig(), $aid);
        $published = $pub->publish();
        if($published===false)exit('发布失败');
        $url_published = Util::concatUrl($this->getChannelConfig()->url->published, $this->_tbl_albums->getUrl($album->createdate, $aid)); 
		$user= Zend_Session::namespaceGet('user');		
		$channel= $user['channel'];
		if($channel=='www' || $channel=='lottery' || $channel=='qipai'){
			refresh_cdn_lx($url_published,$channel);
		}
        $this->flash('发布成功', $url_published);
    }
}