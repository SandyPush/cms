<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'template/models/Templates.php';
require_once MODULES_PATH . 'page/models/Page.php';
require_once MODULES_PATH . 'focus/models/Focus.php';
require_once LIBRARY_PATH . 'Publish/Focus.php';
require_once MODULES_PATH . 'category/models/Category.php';
require_once LIBRARY_PATH . 'function.global.php';


class Focus_FocusController extends BaseController
{
    protected $_db;
    protected $_templates_table;    
    protected $_focus_table;

    public function init()
    {
        $db = $this->getChannelDbAdapter();
        $this->_db = $db;

        Zend_Db_Table::setDefaultAdapter($db);
        
        $this->_templates_table = new TemplatesTable($db);
        $this->_focus_table = new FocusTable();
    }

    public function indexAction()
    {
        $this->_checkPermission('focus', 'index');
        $perpage = 20;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);

        #搜索
        $where="status>=1";
        $search_para='';
        $cid=$this->_getParam('cid', 0);
        if(!empty($cid))$where.=" AND cid='".$cid."'";
        $this->view->cid=$cid;
        
        $title=$this->_getParam('title', '');
        if(!empty($title))$where.=" AND name LIKE '%".$title."%'";
        $this->view->title=$title;
        $search_para.='&title='.urlencode($title);
        
        $realname=$this->_getParam('realname', '');
        if(!empty($realname))$where.=" AND realname LIKE '%".$realname."%'";
        $this->view->realname=$realname;
        $search_para.='&realname='.urlencode($realname);
       
        $star=$this->_getParam('star', 0);
        $where.=" AND star = ".$star;
        $this->view->star=$star ? $star : 0;
        $search_para.='&star='.urlencode($star);

        $config = $this->getChannelConfig();
        
        $total = $this->_focus_table->count($where);
        $focuses = $this->_focus_table->fetch($perpage, ($page - 1) * $perpage,$where);
        foreach ($focuses as &$focus) {
            $focus['cover']= $focus['image']? ($focus['cover'] = Util::concatUrl($config->site->url, $config->url->images, $focus['image'])): '/images/nopic.jpg';		
            $focus['realurl']=$focus['islink']?$focus['url'] : $this->getChannelConfig()->url->published.$focus['url'];
			if(!$focus['title'] &&!$focus['keywords'] &&!$focus['template'] &&!$focus['islink']){
				$focus['upload']=1;
				$focus['realurl']= $this->getChannelConfig()->url->published.'/ztm/'.$focus['url'];
			}
            $focus['realurl']=preg_replace("/([\w\W]+[0-9a-zA-Z]\/)\//i","$1",$focus['realurl']);
        }

    	#分类列表
    	$this->view->category_options=Category::getOptions($this->_db,'全部类别');
    	
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__'.$search_para);
        
        $this->view->focuses = $focuses;
        $this->view->pagebar = $pagebar;
    }

    public function createAction()
    {
        $this->_checkPermission('focus', 'add');
        $this->view->headTitle('模板方式创建专题');
        $this->view->headScript()->appendFile('/scripts/jquery/ui.js');
        $this->view->headLink()->appendStylesheet('/scripts/jquery/datepicker.css');
        
        $this->view->templates = $this->_templates_table->fetchByType(2);
    	#分类列表
    	$this->view->category_options=Category::getOptions($this->_db,'顶级类别');
        $this->view->star=$this->_getParam('star', 0);
                
		$this->view->focus = array('url'=> '/zt/'.date("YmdHis").'.shtml');
        if ($this->isPost())
        {
            $focus = array(
                'name' => $this->_getParam('name'),
                'title' => $this->_getParam('title'),
                'keywords' => $this->_getParam('keywords'),
                'description' => $this->_getParam('desc'),
                'url' => $this->_getParam('url'),
                'islink' => $this->_getParam('islink'),
                'template' => $this->_getParam('template'),
                'cid' => (int) $this->_getParam('cid'),
                'image' => $this->_getParam('image'),
                'star' => $this->_getParam('star',0),
                'star_image' => $this->_getParam('star_image'),
            //'star' => $this->_getParam('star'),
                'starttime' => $this->_getParam('starttime').date(' H:i:s'),
                'endtime' => $this->_getParam('endtime').date(' H:i:s'),
                'uid' => $this->_user['uid'], // todo
                'status' => $this->_getParam('status'),
            );
        	$this->view->focus = $focus;
            #验证url是否已存在
            $Page=new PageTable($this->_db);
            if($Page->checkUrlExist($focus['url']))
            {
                $this->error('和栏目URL冲突');
                return false;
            }
            if($this->_focus_table->checkUrlExist($focus['url']))
            {
                $this->error('和其他专题URL冲突');
                return false;
            }
            if (false === $this->_focus_table->insert($focus)) {
                $this->error($this->_focus_table->error);
                return false;
            }
            $this->flash('专题创建成功', '/focus/focus/');
        }
    }
    
    public function editAction()
    {
        $this->_checkPermission('focus', 'edit');
        $fid = (int) $this->_getParam('fid', 0);
        if (!$focus = $this->_focus_table->find($fid)->current()) {
            $this->error('请指定专题', true);
        }
     
        $focus = $focus->toArray();
        $focus['starttime'] = substr($focus['starttime'], 0, 10);
        $focus['endtime'] = substr($focus['endtime'], 0, 10);
        $this->view->headTitle('修改专题');
        $this->view->headScript()->appendFile('/scripts/jquery/ui.js');
        $this->view->headLink()->appendStylesheet('/scripts/jquery/datepicker.css');

        $this->view->templates = $this->_templates_table->fetchByType(2);
        $this->view->focus = $focus;
        
    	#分类列表
    	$this->view->category_options=Category::getOptions($this->_db,'顶级类别');
        if ($this->isPost())
        {
            $url = $this->_getParam('url', '');
            $url = $url ? $url : $url = sprintf(Zend_Registry::get('settings')->focus->default_url, $fid);
            
            $focus_new = array(
                'fid' => $fid,
                'name' => $this->_getParam('name'),
                'title' => $this->_getParam('title'),
                'keywords' => $this->_getParam('keywords'),
                'description' => $this->_getParam('desc'),
                'url' => $url,
                'islink' => $this->_getParam('islink'),
                'image' => $this->_getParam('image'),
                'star' => $this->_getParam('star'),
                'star_image' => $this->_getParam('star_image'),
                'template' => $this->_getParam('template'),
                'cid' => (int) $this->_getParam('cid'),
                'starttime' => $this->_getParam('starttime').date(' H:i:s'),
                'endtime' => $this->_getParam('endtime').date(' H:i:s'),
                'status' => $this->_getParam('status'),
            );
            #验证url是否已存在
            $Page=new PageTable($this->_db);
            if($Page->checkUrlExist($focus_new['url']))
            {
                $this->error('和栏目URL冲突');
                return false;
            }
            if($this->_focus_table->checkUrlExist($focus_new['url'],$fid))
            {
                $this->error('和其他专题URL冲突');
                return false;
            }
            
            if (false === $this->_focus_table->edit($focus_new, "fid = $fid")) {
                $this->error($this->_focus_table->error);
                return false;
            }
            
            $this->flash('专题修改成功', '/focus/focus/');
        }
    }
    
    public function delAction()
    {
        $this->_checkPermission('focus', 'delete');
        $fid = (int) $this->_getParam('fid', 0);
        if (!$focus = $this->_focus_table->find($fid)->current()) {
            $this->error('请指定专题', true);
        }		
		$file_workplace =  Util::concatUrl($this->getChannelConfig()->path->workplace, $focus->url);
        $file_published = Util::concatUrl($this->getChannelConfig()->path->published, $focus->url);  		 
		if($focus->url && is_file($file_workplace))unlink($file_workplace);
		if($focus->url && is_file($file_published))unlink($file_published);		
		$this->_focus_table->delete($fid);
        $this->flash('专题删除成功', '/focus/focus/');
    }
    #预览
    public function previewAction()
    {
        $this->_checkPermission('focus', 'preview');
        $fid = (int) $this->_getParam('fid', 0);
        if (!$fid || false === $focus = $this->_focus_table->find($fid)->current()) {
            $this->error('请指定专题', true);
        }
		if(!empty($focus->islink))
        {
	        header('Location:'.$focus->url);
	        exit;
        }
        $pub = new Publish_Focus($this->getChannelConfig(), $fid);
        $pub->publishWork();
        
        $url_workplace =  Util::concatUrl($this->getChannelConfig()->url->workplace, $focus->url);
        //$url_published = Util::concatUrl($this->getChannelConfig()->url->published, $focus->url);        
        
        //$this->view->url_workplace = $url_workplace;
        //$this->view->url_published = $url_published;
        header('Location:'.$url_workplace);
        exit;
        
        //$this->flash('专题发布成功', '/page/pages/');
    }
    public function publishAction()
    {
        $this->_checkPermission('focus', 'publish');
        $fid = (int) $this->_getParam('fid', 0);
        if (!$fid || false === $focus = $this->_focus_table->find($fid)->current()) {
            $this->error('请指定专题', true);
        }
                
		if(!empty($focus->islink))
        {
	        header('Location:'.$focus->url);
	        exit;
        }
        
        $pub = new Publish_Focus($this->getChannelConfig(), $fid);
        $pub->publish();
        
        $url_workplace =  Util::concatUrl($this->getChannelConfig()->url->workplace, $focus->url);
        $url_published = Util::concatUrl($this->getChannelConfig()->url->published, $focus->url);        
        
        $this->view->url_workplace = $url_workplace;
        $this->view->url_published = $url_published;
        
        /******提交到搜索引擎*******/
        /**/$url=preg_replace("/([^:])\/\//i","$1/",$this->getChannelConfig()->url->published.$focus->url);
        /**/@file_get_contents('http://10.10.1.30:8081/searchApi/cms/topic/buildTopic?url='.$url.'&id='.$focus->fid.'&title='.urlencode($focus->title).'&image='.$focus->image);
        /*************/
        
        //专题页生成wap页
        $savePath=$this->getChannelConfig()->path->published.'m'.$focus->url;
        mkdir_r(dirname($savePath));
        $wapContent=@file_get_contents($this->getChannelConfig()->url->published.'wapZT.php?ztUrl='.$this->getChannelConfig()->path->published.$focus->url);
        file_put_contents($savePath,$wapContent);
        
        $this->flash('发布成功', $url_published);
        
        //$this->flash('专题发布成功', '/page/pages/');
    }

	public function error($str){
		 echo "<script>alert('{$str}');</script>"; 
	}
}