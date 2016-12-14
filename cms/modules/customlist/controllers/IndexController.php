<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'customlist/models/Customlist.php';
require_once MODULES_PATH . 'customlist/models/Objects.php';
require_once MODULES_PATH . 'customlist/models/Article.php';

class Customlist_IndexController extends BaseController{
    protected $_db;
    protected $_table;
	protected $channel;

    
    public function init(){		
        $db = $this->getChannelDbAdapter();
        $this->_db = $db;        
        Zend_Db_Table::setDefaultAdapter($db);        
        $this->_table = new Customlist($db);		
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];
    }

	public function indexAction(){
		$oid=$this->_getParam('oid');	
		$cid=$this->_getParam('cid');
		$date_time= $this->_getParam('date_time');

		$data= $date_time? $this->_table->getBigNewsByTime($oid,$date_time): $this->_table->getBigNews($oid);
		//$this->view->customlist = json_encode($this->_table->iconv_deep("GBK", "UTF-8", $this->_table->stripslashes_deep(unserialize($data['content']))));
		
		$this->view->customlist = json_encode($this->_table->stripslashes_deep(unserialize($data['content'])));	
		
		$this->view->customlist_select =str_replace("<option value=\"/pid/$pid/oid/$oid/date_time/$date_time\" >","<option value=\"/pid/$pid/oid/$oid/date_time/$date_time\" selected=\"selected\" >",$this->_table->getBigNewsSelect($oid));
		
		$this->view->oid = $oid;
		$this->view->action= $extraNS->action;
		$this->view->pid = $data['pid'];
		$this->view->cid = $cid? $cid: 0;	
		$this->view->user = $_SESSION['user']['realname'];	
		$this->view->iframeUrl= Zend_Registry::get('env_config')->site->url.'/customlist/index/search/?cid='.$cid;
		$this->view->layout()->disableLayout();
	}
	
	public function showAction(){
		$oid=$this->_getParam('oid');	
		$pid= $this->_getParam('pid');
		$cid=$this->_getParam('cid');
		$date_time= $this->_getParam('date_time');
		//echo "<option value=\"/pid/$pid/oid/$oid/date_time/$date_time/\" >";die;
		$data= $date_time? $this->_table->getBigNewsByTime($oid,$date_time,$pid): $this->_table->getBigNews($oid,$pid);
		//$this->view->customlist = json_encode($this->_table->iconv_deep("GBK", "UTF-8", $this->_table->stripslashes_deep(unserialize($data['content']))));	
		$this->view->customlist = json_encode($this->_table->stripslashes_deep(unserialize($data['content'])));
	
		$this->view->customlist_select =str_replace("<option value=\"/pid/$pid/oid/$oid/date_time/$date_time\" >","<option value=\"/pid/$pid/oid/$oid/date_time/$date_time/\" selected=\"selected\" >",$this->_table->getBigNewsSelect($oid,$pid));
		
		$this->view->layout()->disableLayout();
		$this->getResponse()->clearBody();

		$this->view->oid = $oid;		
		$this->view->pid = $this->_getParam('pid');
		if(strpos($this->_getParam('pid'),'f')===false){
			$this->view->publishUrl = '/page/pages/publish/pid/'.str_replace(array('p','f','a'),array(),$this->_getParam('pid'));
		}else{
			$this->view->publishUrl = '/focus/focus/publish/fid/'.str_replace(array('p','f','a'),array(),$this->_getParam('pid'));			
		}
		$this->view->action= $extraNS->action;		
		$this->view->cid = $cid? $cid: 0;
		$this->view->user = $_SESSION['user']['realname'];	
		$this->view->iframeUrl= Zend_Registry::get('env_config')->site->url.'/customlist/index/search/?cid='.$cid;
		$this->view->channel= $this->channel;		
	}

	public function saveAction(){
		$mode=$this->_request->getPost('mode');	
		$channel= $this->_getParam('channel', '');	
		$oid=$this->_request->getPost('oid');
		$customlist=$this->_request->getPost('bignews');	
		$pid=trim($this->_request->getPost('pid'));
		$cid=trim($this->_request->getPost('cid'));		
		$lastuid=$this->_request->getPost('lastuid');

		if($mode=='show'){
			if($channel!= $this->channel){						
				$this->flash('所编辑的模块已不属于当前频道,可能的原因是你在编辑的过程中切换了频道!', '/customlist/index/show/oid/'.$oid.'/pid/'.$pid.'/cid/'.$cid,3);
				return false;
			}
		}		
		
		if(!$oid or !$customlist){
			$this->flash('参数错误', '/customlist/index/index/oid/'.$oid);	
			return false;
		}
		if($this->_table->saveBigNews($oid,$lastuid,$customlist,$pid)){
			if($mode=='show'){
				$this->flash('保存成功', '/customlist/index/show/oid/'.$oid.'/pid/'.$pid.'/cid/'.$cid);
			}else{
				$this->flash('保存成功', '/customlist/index/index/oid/'.$oid.'/cid/'.$cid);
			}
		}		
		return true;
	}

	public function previewAction(){	
		$oid=$this->_request->getPost('oid');
		$customlist= $this->_request->getPost('bignews');	
		$pid= trim($this->_request->getPost('pid'));
		$cid= trim($this->_request->getPost('cid'));	
		$lastuid=$this->_request->getPost('lastuid');
		$mode=$this->_request->getPost('mode');
		
		if(!$oid or !$customlist){
			$this->flash('参数错误', '/customlist/index/index/oid/'.$oid);	
			return false;
		}
		$this->view->contentHtml= addslashes($this->_table->buildBigNewsList($customlist));				
	}

	public function searchAction(){		
		$this->_table = new Article($this->_db);
		$user= Zend_Session::namespaceGet('user');
		$channel= $user['channel'];
		$channels= Zend_Registry::get('settings')->channels->toArray();		

		$cid= intval($this->_getParam('cid'));
		$currentChannel= $channel=='www'? $this->_getParam('channel', current(array_keys($channels))): $this->_getParam('channel', $channel);		
		$currentDb= 'cms_'.$currentChannel;
		$keyword=$this->_table->Char_cv($this->_getParam('keyword'));
		$author=$this->_getParam('author');
		$tableadd='';
        $sqladd='';
		$urladd='';

		if ($keyword) {			
			$sqladd .= " AND a.title LIKE '%$keyword%'";
			$urladd .= "&keyword=$keyword";
		}
		if ($author) {
			$sqladd .= " AND a.author = '$keyword'";
			$urladd .= "&author=$author";
		}
		if ($cid) {	
			//$sqladd .= " AND (a.aid IN (SELECT distinct aid from cate_links where cid = '$cid'))";            
            $tableadd=" inner join cate_links c on a.aid=c.aid and c.cid='$cid'";
			$urladd .= "&cid=$cid";
		}
		if($c= $this->_getParam('channel')){
			$urladd .= "&channel=$c";
		}
		if($channel== 'soccer' && $source_id= $this->_getParam('source_id')){
			$source_id= is_array($source_id)? $source_id: explode(',', $source_id);
			$sqladd .= " and a.source_id in ('".join("','", $source_id)."')";
			$urladd .= "&source_id=".join(",", $source_id);
		}

		$this->view->layout()->disableLayout();
		$this->getResponse()->clearBody();		

		$pagenum = 20;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        $start =($page - 1) * $pagenum; 		    	
		$total = $this->_table->count($sqladd, $currentDb, $tableadd);		
        $pagebar = Util::buildPagebar($total, $pagenum, $page, '?page=__page__'.$urladd);
		$this->view->pagebar = $pagebar;
		/* 特殊处理,首页频道调用各频道的文章,而不是当前频道*/
		$this->view->category_select= $channel=='www'? array(): $this->_table->getSelect();		
		$list= $this->_table->getList($sqladd, $start, $pagenum, $currentDb, $tableadd);	
		foreach($list as $k=> &$v){
			$v['url']= (!$v['islink'])? Util::concatUrl($this->getChannelConfig($currentChannel)->url->published, $v['url']):$v['url']; 	
			//$v['url']=  str_replace('http://soccer.titan24.com/2010/', 'http://2010.titan24.com/', $v['url']);
		}

		$this->view->list= $list;
		$this->view->cid= $cid;
		$this->view->channel= $channel;
		$this->view->channels= $channels;
		$this->view->source_id= "['".@join("','",$source_id)."']";
		$this->view->currentChannel= $currentChannel;
	}

	public function __call($method, $args){   
		$this->flash('参数错误', '/object/objects/index/type/list');
		return false;	
	}
}
?>