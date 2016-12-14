<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'video/models/set.php';
require_once MODULES_PATH . 'video/models/video.php';
require_once MODULES_PATH . 'contents/models/Article.php';
require_once MODULES_PATH . 'category/models/Category.php';
require_once MODULES_PATH . 'contents/models/Publisher.php';

class Video_VideoController extends BaseController
{
    protected $_db;
	protected $_channel_db;
	protected $_tbl_templates;
	protected $_tbl_set;
    protected $_tbl_video;
    protected $categoryObj;
    protected $publisherObj;

    public function init()
    {
	    $channel_db = $this->getChannelDbAdapter();
        $this->_channel_db = $channel_db;
	    $this->_db = $channel_db;
        
        Zend_Db_Table::setDefaultAdapter($channel_db);
        
        $this->_tbl_set = new SetTable($channel_db);
		$this->_tbl_video = new VideoTable($channel_db);
        $this->categoryObj=new Category($channel_db);
        $this->publisherObj=new Publisher($channel_db);
    }
    
    public function indexAction()
    {
        
        $this->_checkPermission('video', 'index');
        $sid = (int) $this->_getParam('sid', 0);
		if (!$sid || !$set = $this->_tbl_set->find($sid)->current()) {
			$this->error('无效的视频集id', true);
		}

        $searchType=$this->_getParam('searchType');
        $keyName=$this->_getParam('searchKey');
        if($keyName){
            $keyName=str_replace("*","%",$keyName);
            $pluginWhere=' and '.$searchType.' like "%'.$keyName.'%"';
        }

        $perpage = 20;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
		
		if ($this->_request->isPost()) {
			$title = $this->_getParam('title');
            $source = $this->_getParam('source');
            $keyword = $this->_getParam('keyword');
            $delete = $this->_getParam('delete');
            foreach ($title as $id => $t) {
    			$p = array(
				   'title' => $t,
                   'source' => $source[$id],
                   'keyword' => $keyword[$id]
			    );
                    
                if (!empty($delete[$id])) {
    				$p['sid'] = $sid;
                    $p['status'] = 0;
    			}
    			$this->_tbl_video->edit($p, 'vid=' . $id);
    		}
    		$this->flash('修改成功', '/video/video/index?page='.$page.'&'.$searchType.'='.$keyName.'&sid='.$sid . $vid, 2);
		}
        
        $pluginWhere.=' and sid="'.$sid.'"';
        
        $total = $this->_tbl_video->count("status >= 1".$pluginWhere);
		$video = $this->_tbl_video->fetch($pluginWhere,$perpage, ($page - 1) * $perpage);
        
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__&'.$searchType.'='.$keyName.'&sid='.$sid);
		
        $config = Zend_Registry::get('channel_config');
        foreach($video as $key => $value){
            $video[$key]['isTranscoding']=$value['videoLink']=='' ? "<font color=red>未转码</font>" : "<font color=green>已转码</font>";
            if($video[$key]['videoLink']){
                $video[$key]['videoLink']='<a href="/video/video/player?videoUrl='.$video[$key]['videoLink'].'" target="_blank">'.$this->view->escape($video[$key]["filename"]).'</a>';
            }else{
                $video[$key]['videoLink']='<a href="javascript:alert(\'转码中...\');">'.$this->view->escape($video[$key]["filename"]).'</a>';
            } 
        }
        
        $this->view->video = $video;
        $this->view->pagebar = $pagebar;
        $this->view->set = $this->_tbl_set->find($sid)->current()->toArray();
    }
    
    public function ajaxAction()
    {
        $cid = (int) $this->_getParam('cid', 0);
        $sid = (int) $this->_getParam('sid', 0);
        $perpage = 10;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        
        $searchType=$this->_getParam('searchType');
        $keyName=$this->_getParam('searchKey');
        if($keyName){
            $keyName=str_replace("*","%",$keyName);
            $pluginWhere=' and '.$searchType.' like "%'.$keyName.'%"';
        }
        
        $pluginWhere.=" and videoLink!=''";
        if($sid) $pluginWhere.=' and sid="'.$sid.'"';
        if($cid) $pluginWhere.=' and cid="'.$cid.'"';

        $total = $this->_tbl_video->count("status >= 1".$pluginWhere);
		$video = $this->_tbl_video->fetch($pluginWhere,$perpage, ($page - 1) * $perpage);
              
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__&'.$searchType.'='.$keyName.'&sid='.$sid.'&cid='.$cid);
        
        foreach($video as $key => $value){
            $video[$key]['isTranscoding']=($value['videoLink']) ? "<font color=green>已转码</font>" : "<font color=red>未转码</font>";
            $video[$key]['isUse']=($value['articleList']) ? "<font color=green>已使用</font>" : "<font color=red>未使用</font>";
            $video[$key]['contents']=json_encode($video[$key]);
        }

        $this->view->setName = $this->_tbl_set->fetch("",9999);
        $this->view->categories = Category::getOptions($this->_channel_db, '顶级类别');
		$this->view->video = $video;
        $this->view->pagebar = $pagebar;
        $this->view->headScript()->appendFile('/scripts/jquery/jquery-1.9.1.js');
    }

	public function uploadAction()
	{
	    $this->_checkPermission('video', 'upload');
        $sid = (int) $this->_getParam('sid', 0);
		if (!$sid || !$set = $this->_tbl_set->find($sid)) {
			$this->error('无效的视频集id', true);
		}

		$set = current($set->toArray());
		$this->view->set = $set;
	}

	public function douploadAction()
	{
		if (!$this->_request->isPost()) {
			$this->error('方法不支持', true);
		}

        $sid = $this->_getParam('sid', 0);
		if (!$sid || !$set = $this->_tbl_set->find($sid)) {
			$this->error('无效的视频集', true);
		}

		$partial = false;
		$errors = array();
		$total = count($_FILES['video']['name']);
		$fields_input = $this->_request->getParam('video');

		$fields = $_FILES['video'];
        for ($i = 0; $i < $total; $i ++) {
			if ($fields['error'][$i] == 4) {
				if (!$errors) {
					$errors[] = '没有选择视频文件';
				}
				continue;
			}
			
			$name = $fields['name'][$i];
			$ext = strtolower(substr(strrchr($fields['name'][$i], '.'), 1));

			if ($fields['error'][$i] != 0) {
				$errors[] = sprintf('%s上传失败，原因未知', $name);
				continue;
			}
			
			$size = $fields['size'][$i];
			$type = $fields['type'][$i];			
							
			// size
			/*
            if ($size > 20 * 1024 * 1024) {
				$errors[] = sprintf('%s 超过规定大小20MB', $name);
				continue;
			}
            */
			
			$video[] = array (
				'name' => $name,
				'type' => $type,
				'size' => $size,
				'tmp_name' => $fields['tmp_name'][$i],
                'ext' => $ext
			);
		}

		if (!$video) {
			$this->error('上传失败, 可能的原因: ' . join(', ', $errors) . '.', 1);
		}else{
		    foreach ($video as $p) {
		      	$p['sid'] = $sid;
                $p['uid'] = $this->_user['uid'];
				$this->_tbl_video->create($p);
			}
		}
	}
    
    public function playerAction(){
        $videoUrl = $this->_getParam('videoUrl');
        $this->view->videoUrl=$videoUrl;
    }
    
    public function categoryAction()
    {
        $this->_checkPermission('video', 'category');
        $cid = (int) $this->_getParam('cid', 0);

        $searchType=$this->_getParam('searchType');
        $keyName=$this->_getParam('searchKey');
        if($keyName){
            $keyName=str_replace("*","%",$keyName);
            $pluginWhere=' and '.$searchType.' like "%'.$keyName.'%"';
        }

        $perpage = 20;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
		
		if ($this->_request->isPost()) {
			$title = $this->_getParam('title');
            $source = $this->_getParam('source');
            $keyword = $this->_getParam('keyword');
            $delete = $this->_getParam('delete');
            foreach ($title as $id => $t) {
    			$p = array(
				   'title' => $t,
                   'source' => $source[$id],
                   'keyword' => $keyword[$id]
			    );
                    
                if (!empty($delete[$id])) {
    				$p['status'] = 0;
    			}
    			$this->_tbl_video->edit($p, 'vid=' . $id);
    		}
    		$this->flash('修改成功', '/video/video/category?page='.$page.'&'.$searchType.'='.$keyName.'&cid='.$cid . $vid, 2);
		}
        
        if($cid){
            $pluginWhere.=' and cid="'.$cid.'"';
        }
        
        $total = $this->_tbl_video->count("status >= 1".$pluginWhere);
		$video = $this->_tbl_video->fetch($pluginWhere,$perpage, ($page - 1) * $perpage);
        
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__&searchType='.$searchType.'&searchKey='.$keyName.'&cid='.$cid);
		
        foreach($video as $key => $value){
            $video[$key]['isTranscoding']=$value['videoLink'] ? "<font color=green>已转码</font>" : "<font color=red>未转码</font>";
            if($video[$key]['videoLink']){
                $video[$key]['videoLink']='<a href="/video/video/player?videoUrl='.$video[$key]['videoLink'].'" title="'.$this->view->escape($video[$key]["filename"]).'" target="_blank">'.$this->view->escape($video[$key]["filename"]).'</a>';
            }else{
                $video[$key]['videoLink']='<a href="javascript:alert(\'转码中...\');">'.$this->view->escape($video[$key]["filename"]).'</a>';
            }
            
            if(!$value['cid']){
                $videoSet=$this->_channel_db->fetchRow("select * from video_set where sid='".$value['sid']."'");
                $value['cid']=$videoSet['cid'];
            }
            $this->categoryObj->init($value['cid']);
            $video[$key]['categoryName']=$this->categoryObj->getName() ? $this->categoryObj->getName() : '顶级分类';
        }
        
        $this->view->categories = Category::getOptions($this->_channel_db, '顶级类别');
        $this->view->video = $video;
        $this->view->pagebar = $pagebar;
    }
    
    public function unknownAction(){
        $this->_checkPermission('video', 'unknown');
        $uploadDir=$this->_user['uploadDir'];
        $transcodeDir=$this->_user['transcodeDir'];
        
        if ($this->_request->isPost()) {
			if(file_exists($transcodeDir)){
                $files =(array)$this->_getParam('files');
                $title = $this->_getParam('title');
                $source = $this->_getParam('source');
                $keyword = $this->_getParam('keyword');
                $cid = $this->_getParam('cid');
                
                $this->categoryObj->init($cid);
                if(!$this->categoryObj->getChannelId()){
                    $this->error('此分类未绑定频道，请选择其他常规分类', 1);
                }

                foreach ($files as $id => $t) {
        			$md5FileCode=md5($t);
                    $p = array(
    				   'title' => $title[$id],
                       'source' => $source[$id],
                       'keyword' => $keyword[$id],
                       'filename' => basename($t),
                       'cid' => $cid,
                       'md5'=>$md5FileCode,
                       'uid' => $this->_user['uid']
    			    );
                    
                    $vidoInfo = $this->_tbl_video->fetch(" and md5='".$md5FileCode."'");
                    $vid=$vidoInfo[0]["vid"];
                    if($vid){
                        $this->_db->update('video',$p,"vid='".$vid."'");
                    }else{
                        $this->_db->insert('video', $p);
                        $vid = $this->_db->lastInsertId();
                    }
                    
                    if(!rename($t,$transcodeDir.$vid."_".md5($t).strrchr($t,'.'))){
                       $this->error('文件移动失败：请检查权限', 1);
                    }
                    /*
                    if(PHP_OS=='WINNT'){
                        if(!rename($t,$transcodeDir.$vid."_".basename($t))){
                            $this->error('文件移动失败：请检查权限', 1);
                        }
                    }else{
                        $cmd="mv ".$t." ".$transcodeDir.$vid."_".basename($t);
                        exec($cmd . " > /dev/null &");
                    }
                    */
                    
        		}
        		$this->flash('保存成功,请继续对未处理视频进行处理', '/video/video/unknown', 2);
            }else{
                $this->error('文件或目录不存在', 1);
            }
		}
        
        $this->view->categories = Category::getOptions($this->_channel_db, '顶级类别');
        
        $this->view->uploadDir = $uploadDir;
        $this->view->transcodeDir = $transcodeDir;
        $this->view->realname=$this->_user['realname'];
        if($uploadDir and file_exists($uploadDir)){
            $fileList=Util::fileList($uploadDir);
            $this->view->fileList = $fileList;
        }
    }
    
    //导视频接口
    public function receivevideoAction(){
        //$p['vid'] = $this->_getParam('vid');
        $p['cid'] = $this->_getParam('cid');
        $p['filename'] = $this->_getParam('filename');
        $p['title'] = $this->_getParam('title');
        $p['source'] = $this->_getParam('source');
        $p['keyword'] = $this->_getParam('keyword');
        $p['picLink'] = $this->_getParam('picLink');
        $p['videoLink'] = $this->_getParam('videoLink');
        $p['duration'] = $this->_getParam('duration');
        $p['transcodingDate'] = $this->_getParam('transcodingDate');
        $p['articleList'] = $this->_getParam('articleList');
        if($p['picLink'] and $p['videoLink'] and $p['duration']){
            $this->_db->insert('video', $p);
            exit('添加视频成功');
        }else{
            exit('添加视频失败');
        }
        
    }
    
    //转码专用Action
    public function transcodingAction(){
        $channel=$this->_getParam('channel');
        $vid = $this->_getParam('vid');
        $p['picLink'] = $this->_getParam('picLink');
        $p['videoLink'] = $this->_getParam('videoLink');
        $p['duration'] = $this->_getParam('duration',0);
        if($p['duration']) $p['duration']=$p['duration']/1000;
        
        if($vid and $p['picLink'] and $p['videoLink'] and $p['duration']){
            $p['transcodingDate'] = time();
            
            //自动生成底层页面并发布
            $videoArray=$this->_tbl_video->fetch(" and vid='".$vid."'");
            $video=$videoArray[0];
            if($video['cid']){
                $cid=$video['cid'];
            }elseif($video['sid']){
                $setArray=$this->_tbl_set->fetch(" and sid='".$video['sid']."'");
                $cid=$setArray[0]['cid'];
            }
           
            if($cid){
                $result=$this->_channel_db->fetchRow('select articleTemplates from categories a,page b where a.bind_id=b.pid and a.cid="'.$cid.'"');
                $articleTemplatesID=$result['articleTemplates'];
            }else{
                $articleTemplatesID=0;
            }
       
            $articleObj=new Article($this->_channel_db);    
            $articleObj->setCid($cid);//主分类ID
        	$articleObj->setTitle($video['title']);
        	$articleObj->setShortTitle($video['title']);
        	$articleObj->setIntro($video['title']);
            $articleObj->setTags($video['keyword']);//文章标签关键字
            if(!$video['source']) $video['source']='第一视频'; 
            $publisher_id=$this->publisherObj->getIdByName($video['source']);
        	$articleObj->setPublisher($publisher_id);
            $articleObj->setColumnName($video['columnName']);
         	$articleObj->setAuthor('第一视频');
        	$articleObj->setUid($video['uid']);//文章创建者
        	$articleObj->setLastUid($video['uid']);//最后更改者
        	$articleObj->setPostDate(mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('Y')));//发布日期
        	$articleObj->setLastDate(TIME_NOW);
        	$articleObj->setTemplate($articleTemplatesID);
        	$articleObj->setPushCid($this->categoryObj->getAllParentsId($cid,true));//推送分类
        	$articleObj->setLevel(3);//权重
        	$articleObj->setContentLength(0);//文章字数
        	$articleObj->setStatus(2);//发布状态	
    		$articleObj->setContents('<div class="video_play" id="playDiv"><object width="630" height="534" id="play1" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0">
                                <param value="http://www.v1.cn/player/cloud/cloud_player.swf" name="movie" />
                                <param value="#000000" name="bgcolor" />
                                <param value="id='.$vid.'&amp;startSwfUrl=http://www.v1.cn/player/cloud/loading.swf&amp;videoUrl='.$p['videoLink'].'" name="FlashVars" />
                                <param value="always" name="allowScriptAccess" />
                                <param value="true" name="allowFullScreen" />
                                <param value="opaque" name="wmode" />
                                <embed width="630" height="534" type="application/x-shockwave-flash" cover="'.$p['picLink'].'" wmode="opaque" allowfullscreen="true" allowscriptaccess="always" bgcolor="#000000" name="play1" flashvars="id='.$vid.'&amp;startSwfUrl=http://www.v1.cn/player/cloud/loading.swf&amp;videoUrl='.$p['videoLink'].'" src="http://www.v1.cn/player/cloud/cloud_player.swf"></embed>
                                </object>
                                </div>');//内容
                                
        
            //从搜索引擎中取数据
            $relatedArray=$articleObj->getRelatedNewsForSolr(str_replace(' ','-',$video['keyword']),$cid);
            foreach($relatedArray as $related){
                $relatedID[]=$related['aid'];
            }  
    	    $articleObj->setRelatedNews($relatedID);//相关文章                                
        	if(!stristr($p['picLink'],'nopic')){
                $p['picLink']=str_replace('.jpg','-s.jpg',$p['picLink']);
            }
            $articleObj->setImage($p['picLink']);
            $articleObj->setAlbumid(0);
            $articleObj->setInsertTime(TIME_NOW);
        	$aid= $articleObj->joinArticleData();
            $p['articleList']=$aid.'|';
    		$this->_tbl_video->edit($p, 'vid=' . $vid);
            if(!$aid){
                exit("添加文章失败");
    			return false;
    		}
    		//主分类信息
            $this->categoryObj->init($cid);
    		$url=$this->categoryObj->getPublisherDir().date("Y-m-d",TIME_NOW).'/'.$aid.'.shtml';
        	$articleObj->modifyInfo(array('url'=>$url),'aid='.$aid);
            
    		$pub = new Publish_Article($this->getChannelConfig(), $aid);
    		$pub->publish();		
            //投放到搜索引擎
            $solrResult=@$articleObj->putSolr($aid);
            exit("添加文章成功");
        }else{
            exit('缺少参数！');
        }
        //http://cms.v1.dev/video/video/transcoding?channel=v1&vid=24&picLink=http://pic101.v1.cn/cloud/20130424/images/459877.jpg&videoLink=http://flv101.v1.cn/cloud/20130424/459877.flv&duration=120
    }
}