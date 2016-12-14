<?php

class Article
{
	const TABLE = 'article';//主数据表
	const TABLE_CONTENT = 'article_contents';//内容表
	const TABLE_TAG = 'tags';//标签（关键字）表
	const TABLE_LINK_TAG = 'article_tag';//文章关联标签（关键字）表
	const TABLE_CATEGORY_LINK = 'cate_links';//类别关连文章表
	private $db;//数据库连接
	private $error_message = "";//错误信息
	private $id = 0;//文章ID
	private $title = "";//文章标题
	private $short_title = "";//文章短标题
	private $author = "";//文章作者
	private $publisher_id = "";//文章来源ID
    private $columnName = "";//栏目
	private $type = 0;//文章类别(0为正常，1为约稿，其他待定)
	private $intro = "";//文章简介
	private $gift = '';// 礼包内容
	private $phoneOnly = '';
	private $showNum = 1;// 展示次数
	private $url = "";//文章url
	private $islink = "";//文章外链标记
	private $tags = "";//文章标签（关键字）
	private $image = "";//文章图片
	private $create_time = 0;//文章创建时间
	private $post_date = 0;//文章发布时间
	private $last_date = 0;//文章最后修改时间
	private $uid = 0;//文章添加者ID
	private $albumid; //文章图集ID
	private $last_uid = 0;//文章最后修改者ID
	private $template = 0;//文章模板ID
	private $cid = 0;//文章主分类ID
	private $push_cid=array();//文章推送分类
	private $level = 0;//文章权重
	private $pv = 0;//文章点击数
	private $content_length = 0;//文章字数
	private $status = 1;//文章状态0为删除1为正常(未发布)2为发布
	private $contents = "";//文章内容
	private $is_ad = 0;//显示推荐
	private $is_titan = 0;//版权所有
	private $where="";//搜索条件数组
	private $order="";//排序
	private $perpage=30;//每页记录数
	private $page=1;//第几页
	private $insert_time;//抓取时间
	public $channel = '';
	
	public function __construct($db,$id=NULL)
	{
		$this->db=$db;
		if(!empty($id))
		{
			$this->init($id);
		}
	}
	public function __destruct()
	{
	}
	public function init($id)
	{
		$select=$this->db->select();
		$select->from(self::TABLE,'*');
		$select->where('aid=?',$id);
		$select->limit(1);
		$sql=$select->__toString();
		$result=$this->db->fetchRow($sql);
        if(empty($result))return false;//通过判断errorMessage为空且返回false确定取得数据为空
        $this->setId($result['aid']);
        $this->setTitle($result['title']);
        $this->setShortTitle($result['stitle']);
        $this->setAuthor($result['author']);
        $this->setPublisher($result['source_id']);
        $this->setColumnName($result['columnName']);
        $this->setType($result['type']);
        $this->setIntro($result['intro']);
        isset($result['gift']) && $this->setGift($result['gift']);
        isset($result['phoneOnly']) && $this->setPhoneOnly($result['phoneOnly']);
        isset($result['showNum']) && $this->setShowNum($result['showNum']);
        $this->setUrl($result['url']);
        $this->setIsLink($result['islink']);
        $this->setImage($result['image']);
        $this->setUid($result['uid']);
		$this->setAlbumid($result['albumid']);
        $this->setLastUid($result['lastuid']);
        $this->setPostDate($result['postdate']);
        $this->create_time=$result['create_time'];
        $this->setLastDate($result['lastdate']);
        $this->setTemplate($result['template']);
        $this->setCid($result['cid']);
        $this->setLevel($result['level']);
		$this->setPv($result['pv']);
        $this->setContentLength($result['content_length']);
        $this->setStatus($result['status']);
		$this->setIs_ad($result['is_ad']);
		$this->setIs_titan($result['is_titan']);
		$this->setInsertTime($result['insert_time']);
		
	}
	//设置错误信息
	protected function setErrorMessage($error_message)
	{
		$this->error_message=$error_message;
	}
	//获取错误信息
	public function getErrorMessage()
	{
		return $this->error_message;
	}
	//设置文章ID
	public function setId($id)
	{
		$this->id= intval($id);
	}
	//获取文章ID
	public function getId()
	{
		return $this->id;
	}
    
    //获取视频信息
    public function getVideoUrl($aid){
        $video=$this->db->fetchRow("select videoLink from video where match(articleList) against('".$aid."')");
        return $video['videoLink'];
    }
    
	//设置文章标题
	public function setTitle($title)
	{
		$this->title=trim($title);
	}
	//获取文章标题
	public function getTitle()
	{
		return $this->title;
	}
	//设置文章短标题
	public function setShortTitle($short_title)
	{
		$this->short_title=trim($short_title);
	}
	//获取文章短标题
	public function getShortTitle()
	{
		return $this->short_title;
	}

	//设置频道推荐
	public function setIs_ad($is_ad)
	{
		$this->is_ad= $is_ad;
	}
	//获取频道推荐
	public function getIs_ad()
	{
		return $this->is_ad;
	}

	//设置版权所有
	public function setIs_titan($is_titan)
	{
		$this->is_titan= $is_titan;
	}
	//获取版权所有
	public function getIs_titan()
	{
		return $this->is_titan;
	}

    //设置栏目
    public function setColumnName($columnName)
	{
		$this->columnName=$columnName;
	}
    
    //获取栏目
    public function getColumnName()
	{
		return $this->columnName;
	}

	//设置文章作者
	public function setAuthor($author)
	{
		$this->author=trim($author);
	}
	//获取文章作者
	public function getAuthor()
	{
		return $this->author;
	}
	//获取文章编辑
	public function getRealname($uid)
	{
		$sql= "select realname from users where uid='$uid'";
		$realname= $this->db->fetchOne($sql);		
		return 	$realname;	
	}
    
	//设置文章来源
	public function setPublisher($publisher_id)
	{
		$this->publisher_id= $publisher_id? intval($publisher_id):1;
	}
	//获取文章来源
	public function getPublisher()
	{
		return $this->publisher_id;
	}
	//设置文章类型
	public function setType($type)
	{
		$this->type=$type;
	}
	//获取文章类型
	public function getType()
	{
		return $this->type;
	}
	//设置文章简介
	public function setIntro($intro)
	{
		$array=array("\r","\n");
		$this->intro=trim(str_replace($array,'',$intro));
	}
	//获取文章简介
	public function getIntro()
	{
		return $this->intro;
	}
	//设置礼包内容
	public function setGift($gift)
	{
		$this->gift=trim($gift);
	}
	//获取礼包内容
	public function getGift()
	{
		return $this->gift;
	}
	//设置只能手机访问
	public function setPhoneOnly($phoneOnly)
	{
		$this->phoneOnly=$phoneOnly;
	}
	//获取只能手机访问
	public function getPhoneOnly()
	{
		return $this->phoneOnly;
	}
	//设置展示次数
	public function setShowNum($showNum)
	{
	    $this->showNum=trim($showNum);
	}
	//获取展示次数
	public function getShowNum()
	{
	    return $this->showNum;
	}
	//设置获取文章URL
	public function setUrl($url)
	{
		$this->url=trim($url);
	}
	public function getUrl()
	{
		return $this->url;
	}
	//设置获取文章外链URL
	public function setIsLink($islink)
	{
		$this->islink=trim($islink);
	}
	public function getIsLink()
	{
		return $this->islink;
	}

	public function setInsertTime($time)
	{
		$this->insert_time=trim($time);
	}
	public function getInsertTime()
	{
		return $this->insert_time;
	}

	
	//获取文章自动计算URL(现已改为直接读取文章URL字段)
	public function getAutoUrl()
	{
		//$url="article/".$this->cid."/".date("Y/m/d",$this->create_time)."/".$this->id.".html";
		$url=$this->url;
		return $url;
	}
	//设置获取文章标签
	public function setTags($tags)
	{
		$this->tags=trim($tags);
	}
	public function getTags()
	{
		$sql="select group_concat(b.name ORDER BY id ASC SEPARATOR ' ') from ".self::TABLE_LINK_TAG." a,".self::TABLE_TAG." b where aid=".$this->getId()." and a.tagid=b.tagid group by a.aid";
		return $this->db->fetchOne($sql);
	}
	//设置文章图片
	public function setImage($image)
	{
		$this->image=trim($image);
	}
	//获取文章图片
	public function getImage()
	{
		return $this->image;
	}
	//设置文章添加者
	public function setUid($uid)
	{
		$this->uid=trim($uid);
	}
	//获取文章添加者
	public function getUid()
	{
		return $this->uid;
	}

	//设置文章图集ID
	public function setAlbumid($id)
	{
		$this->albumid= trim($id);
	}
	//获取文章图集ID
	public function getAlbumid()
	{
		return $this->albumid;
	}
	//设置发布时间
	public function setPostDate($post_date)
	{
		$this->post_date=$post_date;
		
	}
	//获取发布时间
	public function getPostDate()
	{
		return $this->post_date;
	}
	//设置最后更改时间
	public function getCreateTime()
	{
		return $this->create_time;
	}
	public function setLastDate($last_date)
	{
		$this->last_date=trim($last_date);
		
	}
	//获取最后更改时间
	public function getLastDate()
	{
		return $this->last_date;
	}
	//设置文章最后修改者
	public function setLastUid($last_uid)
	{
		$this->last_uid=trim($last_uid);
	}
	//获取文章最后修改者
	public function getLastUid()
	{
		return $this->last_uid;
	}
	//设置文章模板
	public function setTemplate($template)
	{
		$this->template=trim($template);
	}
	//获取文章模板
	public function getTemplate()
	{
		return $this->template;
	}
	//设置文章主分类ID
	public function setCid($cid)
	{
		$this->cid=trim($cid);
	}
	//获取文章主分类ID
	public function getCid()
	{
		return $this->cid;
	}
	//设置获取文章推送分类ID
	public function setPushCid($push_cid)
	{
		$this->push_cid=$push_cid;
	}
	public function getPushCid()
	{
		$select=$this->db->select();
		$select->from(self::TABLE_CATEGORY_LINK,'cid');
		$select->where('aid=?',$this->getId());
		$result=$this->db->fetchCol($select->__toString());
		return $result;
	}
	//设置文章权重
	public function setLevel($level)
	{
		$this->level=trim($level);
	}
	//获取文章权重
	public function getLevel()
	{
		return $this->level;
	}
	//设置文章PV
	public function setPv($pv)
	{
		$this->pv=trim($pv);
	}
	//获取文章PV
	public function getPv()
	{
		return $this->pv;
	}
	//设置文章字数
	public function setContentLength($content_length)
	{
		$this->content_length=$content_length;
	}
	//获取文章被字数
	public function getContentLength()
	{
		return $this->content_length;
	}
	//设置文章状态
	public function setStatus($status)
	{
		$this->status=trim($status);
	}
	//获取文章状态
	public function getStatus()
	{
		return $this->status;
	}
	//获取文章权重列表
	public function getLevelSelect()
	{
		$array=array(0,1,2,3,4,5,6,7,8,9);
		return $array;
	}
	//设置文章内容
	public function setContents($contents)
	{
		//$this->contents=trim($contents);
		$this->contents=stripslashes($contents);
	}
	//获取文章内容
	public function getContents()
	{
		$select=$this->db->select();
		$select->from(self::TABLE_CONTENT,'contents');
		$select->where('aid=?',$this->getId());
		$select->limit(1);
		return $this->db->fetchOne($select->__toString());
	}
	//设置获取文章内容
	public function setRelatedNews($related_news)
	{
		if(empty($related_news))return ;
		$this->related_news=trim(implode(',',$related_news));
	}
	public function getRelatedNews()
	{
		$select=$this->db->select();
		$select->from(self::TABLE_CONTENT,'related_news');
		$select->where('aid=?',$this->getId());
		$select->limit(1);
		return explode(',',$this->db->fetchOne($select->__toString()));
	}
	public function getRelatedNewsToJson()
	{
		$select=$this->db->select();
		$select->from(self::TABLE_CONTENT,'related_news');
		$select->where('aid=?',$this->getId());
		$select->limit(1);
		//echo $select->__toString();
		$related_news_id=$this->db->fetchOne($select->__toString());
		if(empty($related_news_id))return '';
		$related_news=$this->db->fetchAll("SELECT aid id,title FROM article WHERE aid in(".$related_news_id.") AND status>0 order by substring_index('".$related_news_id."',aid,1)");
		if(empty($related_news))return false;
		return json_encode($related_news);
	}
    
    //从solr搜索引擎中获取相关文章
    /*
    public function getRelatedNewsForSolr($title){
        $title=preg_replace("/[\+|\-|\&|\||\!|\(|\)|\{|\}|\[|\]|\^|\"|\~|\*|\?|\\|\/|\:|\s]/",'',$title);
        $relateArray=array();
        require_once(dirname(__FILE__).'/Solr/Service.php');
        require_once(dirname(__FILE__).'/Solr/HttpTransport/Curl.php');
        $config = Zend_Registry::get('channel_config');
        $host=$config->solr->host;
        $port=$config->solr->port;
        $path=$config->solr->path;
        if(!$host) return $relateArray;
        $solr = new Apache_Solr_Service($host, $port, $path);
        if($solr->ping()){
            $newTransport = new Apache_Solr_HttpTransport_Curl();
            $solr->setHttpTransport($newTransport);
            $query = !empty($title) ? "title:".$title : '*:*';
            if (get_magic_quotes_gpc() == 1) $query = stripslashes($query);
            $limit=12;
            $results = $solr->search($query, 0, $limit,array("q.op"=>"OR"));
            foreach ($results->response->docs as $doc){
                $relate['aid']=$doc->id;
                $relate['title']=$doc->title;
                $relate['image']=$doc->screenshotPath;
                $relate['url']=$doc->url;
                $relateArray[]=$relate;
            }
        }
        return $relateArray;
    }
    */
    
    public function getRelatedNewsForSolr($keyword,$cid='',$num=12){
        if($cid){
            //分类
    		$sql="select bind_id from categories where cid='".$cid."'";
    		$category=$this->db->fetchRow($sql);
            
            //栏目ID及name
            $sql="select name from page where pid='".$category['bind_id']."'";
            $page=$this->db->fetchRow($sql);
        }

        $solrResult=$this->getSolr($keyword,(string)$page['name'],$num);
        if(!is_array($solrResult)) return false;
        foreach ($solrResult as $value){
                $relate['aid']=$value['id'];
                $relate['title']=$value['title'];
                $relate['image']=$value['screenshotPath'];
                $relate['url']=$value['url'];
                $relateArray[]=$relate;
        }
        return (array)$relateArray;
    }
    
    public function getSolr($keyword,$pageName='',$num=12){
        $config = Zend_Registry::get('channel_config');
        $findUrl=$config->solr->find;
        if(!$findUrl) return false;
        require_once LIBRARY_PATH . 'function.global.php';
        return json_decode(postdata($findUrl,array('title'=>$keyword,'pname'=>$pageName,'size'=>$num)),1);
    }
    
    /*
        $cname 分类名称
        
    public function getSolr($keyword,$cname=''){
        $config = Zend_Registry::get('channel_config');
        $findUrl=$config->solr->find;
        if(!$findUrl) return false;
        require_once LIBRARY_PATH . 'function.global.php';
        return json_decode(postdata($findUrl,array('keyword'=>$keyword,'pName'=>$cname,'size'=>12)),1);
    }
    */
    
    public function putSolr($aid,$isUpdate=0){
        $config = Zend_Registry::get('channel_config');
        $putUrl=$config->solr->add;
        $updateUrl=$config->solr->update;
        if(!$config->solr->submitToSolr) return;
        require_once LIBRARY_PATH . 'function.global.php';
        $solrUrl="http://ynews.v1.cn/news/solr/add-news.jhtml";
        
        $article=$this->db->fetchRow("select a.*,(select name from article_source where id=a.source_id) source from article a where status=2 and aid='".$aid."'");
        if($article['aid']){
            //分类
    		$sql="select name,bind_id from categories where cid='".$article['cid']."'";
    		$category=$this->db->fetchRow($sql);
            
            //栏目ID及name
            $sql="select pid,name,url from page where pid='".$category['bind_id']."'";
            $page=$this->db->fetchRow($sql);
            $url=explode('/',$page['url']);
    		$categoryPY=$url[2] ? $url[1] : 'www';
            
            //videoLink
        	$sql="select vid,videoLink,duration from video where match(articleList) against('".$aid."')";
        	$video=$this->db->fetchRow($sql);
        	$duration=$video['duration'] > 1 ? $video['duration'] : rand(120,360);
            if(!$video){
                $sql="select contents from article_contents where aid='".$aid."'";
        	    $content=$this->db->fetchRow($sql);
                preg_match("/videoUrl=([\w\W]*?)\.flv\"/i",$content['contents'],$videoUrl);
                if($videoUrl[1]){
                    $video['videoLink']=$videoUrl[1].'.flv';
                }
            }
            
            //tag
            $sql="select b.name from article_tag a,tags b where aid='".$aid."' and a.tagid=b.tagid order by a.id asc";
        	$tagAll=$this->db->fetchAll($sql);
            foreach($tagAll as $key=>$tag){
                if($key==0){
                    $tagStr=$tag['name'];
                }else{
                    $tagStr.=" ".$tag['name'];
                }
            }
            
            $videoData=JTPC('videoData.getVideoData',array('aid'=>$aid));
            if($videoData){
                $CMSEnhanced = json_decode($videoData,true);
                $pv=$CMSEnhanced['result']['data']['items'][$aid]['guisePV'];
            }else{
                $pv=0;
            }
            
            //简介最后一个字符为“~”的内容不显示在相关新闻中
            if(substr($article['intro'],-1)=='~'){
                $relatedDisplay=0;
            }else{
                $relatedDisplay=1;
            }
            
            $dataArray=array("id"=>$aid,
                        "title"=>$article['title'],
                        "brief"=>$article['intro'],
                        "userId"=>0,
                        "brief"=>$article['intro'],
                        "newsSource"=>$article['source'],
                        "columnName"=>$article['columnName'],
                        "createTime"=>date("Y-m-d H:i:s",$article['create_time']),
                        "publishTime"=>date("Y-m-d H:i:s",$article['postdate']),
                        "categoryId"=>$article['cid'],
                        "categoryName"=>$category['name'],
                        "pid"=>$page['pid'],
                        "pname"=>$page['name'],
                        "categoryPY"=>$categoryPY,
                        "videoId"=>$video['vid'],
                        "filePath"=>$video['videoLink'],
                        "duration"=>round($duration),
                        "keyword"=>$tagStr,
                        "screenshotPath"=>$article['image'],
                        "supportTimes"=>0,
                        "opposeTimes"=>0,
                        "pv"=>$pv,
                        "vv"=>0,
                        "uv"=>0,
                        "discussCount"=>0,
                        "url"=>Util::concatUrl($config->url->published, $article['url']),
                        "relatedDisplay"=>$relatedDisplay,
            );
            
            if($isUpdate){
                @postdata($updateUrl,$dataArray); //新版本搜索引擎测试 更新
            }else{
                @postdata($putUrl,$dataArray); //新版本搜索引擎测试 增加
                return postdata($solrUrl,$dataArray);
            }
        }
    }
    
    public function delSolr($aid){
        $config = Zend_Registry::get('channel_config');
        $delUrl=$config->solr->delete;
        if(!$config->solr->submitToSolr) return;
        @file_get_contents($delUrl.'/'.$aid);  //新版本搜索引擎测试
        file_get_contents('http://114.112.169.207:8983/solr/update/?stream.body=%3Cdelete%3E%3Cid%3E'.$aid.'%3C/id%3E%3C/delete%3E&stream.contentType=text/xml;charset=utf-8&commit=true');
    }
    
	//设置获取搜索条件
	public function setWhere($where)
	{
		$this->where.=" AND (".$where.")";
	}
	public function setOrWhere($where)
	{
		$this->where.=" OR (".$where.")";
	}
	public function getWhere()
	{
		return $this->where;
	}
	#设置排序
	public function setOrder($order)
	{
		switch($order)
		{
			case 'recommend':
				$this->order='order by level DESC,postdate desc';
				break;
			case 'pv':
				$this->order='order by pv DESC,postdate desc';
				break;
			case 'postdate':
				$this->order='order by postdate desc';
				break;
			default:
				break;			
		}
		
	}
	public function getOrder()
	{
		return $this->order;
	}
	public function setPage($page)
	{
		$this->page=trim($page);
	}
	public function setPerpage($perpage)
	{
		$this->perpage=trim($perpage);
	}
	//添加文章
	public function add()
	{
		self::checkArticleTableAlbumId();
		$title= $this->getTitle();
		$cid = $this->getCid();
		$aid= $this->db->fetchOne("select aid from article where title='".$title."' and cid='".$cid."' and status=2");
		if($aid>0)return;
		$article = array(
                  'title' => $this->getTitle(),
				  'stitle' => $this->getShortTitle(),
				  'intro' => $this->getIntro(),
				  'url' => $this->url,
				  'islink' => $this->islink,
				  'image' => $this->getImage(),
				  'source_id' => $this->getPublisher(),
                  'columnName' => $this->getColumnName(),
				  'type' => $this->getType(),
				  'author' => $this->getAuthor(),
				  'create_time' => time(),
				  'postdate' => $this->getPostDate(),
				  'lastdate' => $this->getLastDate(),
				  'uid' => $this->getUid(),
				  'lastuid' => $this->getLastUid(),
				  'cid' => $this->getCid(),
				  'level' => $this->getLevel(),
				  'content_length' => $this->getContentLength(),
				  'template' => $this->getTemplate(),
				  'status' => $this->getStatus(),
				  'is_ad' => $this->getIs_ad(),		
				  'is_titan' => $this->getIs_titan(),
				  'albumid' => $this->getAlbumid(),
				  'insert_time' => $this->getInsertTime()	
				  );
		if ($this->channel == 'h5') {
			$article['gift'] = $this->getGift();
			$article['phoneOnly'] = $this->getPhoneOnly();
			$article['showNum'] = $this->getShowNum();
		}
		$this->db->insert(self::TABLE, $article);
		$insertId=$this->db->lastInsertId();
		$this->setId($insertId);
		$this->addContents();
		$this->deleteArticleTags();
		$this->addArticleTags();
		$this->deletePushCid($this->id);
		$this->addPushCid($this->id,$this->push_cid);
        $this->db->query("UPDATE categories SET article_nums = article_nums + 1 WHERE cid = ?", $this->getCid());
		return $insertId;
	}
    
    //添加文章 -- 来自第三方的数据
	public function joinArticleData()
	{
		self::checkArticleTableAlbumId(); 
		$title= $this->getTitle();
		$aid= $this->db->fetchOne("select aid from article where title='".$title."' and status=2");
        if($aid>0 or !$title)return false;
        $data=array(
                  'title' => $this->getTitle(),
				  'stitle' => $this->getShortTitle(),
				  'intro' => $this->getIntro(),
				  'url' => $this->url,
				  'islink' => $this->islink,
				  'image' => $this->getImage(),
				  'source_id' => $this->getPublisher(),
                  'columnName' => $this->getColumnName(),
				  'type' => $this->getType(),
				  'author' => $this->getAuthor(),
				  'create_time' => $this->getPostDate(),
				  'postdate' => $this->getPostDate(),
				  'lastdate' => $this->getLastDate(),
				  'uid' => $this->getUid(),
				  'lastuid' => $this->getLastUid(),
				  'cid' => $this->getCid(),
				  'level' => $this->getLevel(),
				  'content_length' => $this->getContentLength(),
				  'template' => $this->getTemplate(),
				  'status' => $this->getStatus(),
				  'is_ad' => $this->getIs_ad(),		
				  'is_titan' => $this->getIs_titan(),
				  'albumid' => $this->getAlbumid(),
				  'insert_time' => $this->getInsertTime()	
				  );
        if($this->getId()) $data['aid'] = $this->getId();
		$a=$this->db->insert(self::TABLE,$data);
        $insertId=$this->db->lastInsertId();
		$this->setId($insertId);
		$this->addContents();
		$this->deleteArticleTags();
		$this->addArticleTags();
		$this->deletePushCid($this->id);
		$this->addPushCid($this->id,$this->push_cid);
        $this->db->query("UPDATE categories SET article_nums = article_nums + 1 WHERE cid = ?", $this->getCid());
		return $insertId;
	}
    
	//添加文章内容
	private function addContents()
	{
		$this->db->insert(self::TABLE_CONTENT, 
			array('aid' => $this->getId(),
				  'contents' => $this->contents,
				  'related_news' => $this->related_news,
			));
	}
	//添加文章标签
	private function addArticleTags()
	{
		if(empty($this->tags))return ;
		$tags_array=preg_split("/[_\s,-]+/",$this->tags);
		foreach($tags_array as $tag)
		{
			if(empty($tag))continue;
            if(strlen($tag)>32) $tag=mb_substr($tag,0,32,'utf-8');
			$tagid=$this->addTag($tag);
			$sql="INSERT IGNORE INTO ".self::TABLE_LINK_TAG."(aid,tagid) VALUES(".$this->id.",".$tagid.")";
			$this->db->query($sql);
		}
	}
	//删除文章标签
	private function deleteArticleTags()
	{
		if(empty($this->id))return ;
		$sql="delete from ".self::TABLE_LINK_TAG." where aid=".$this->id;
		$this->db->query($sql);
	}
	//添加标签,返回标签ID
	private function addTag($tag)
	{
		$sql="insert ignore into ".self::TABLE_TAG."(name) values('".$tag."')";
		$this->db->query($sql);
		$sql="select tagid from ".self::TABLE_TAG." where name='".$tag."'";
		return $this->db->fetchOne($sql);
	}
	//添加删除文章推送分类
	private function addPushCid($aid,$cid_array)
	{
		if(empty($cid_array) or empty($aid))return false;
		$sql="insert ignore into cate_links(aid,cid) values";
		foreach($cid_array as $cid)
		{
			$sql.="(".$aid.",".$cid."),";
		}
		$sql=substr($sql,0, -1);
		$this->db->query($sql);
	}
	private function deletePushCid($aid)
	{
		if(empty($aid))return false;
		$sql="delete from cate_links where aid=".$aid;
		$this->db->query($sql);
	}
	//修改文章
	public function modify()
	{
		$data=array('title' => $this->getTitle(),
				  'stitle' => $this->getShortTitle(),
				  'intro' => $this->getIntro(),
				  'image' => $this->getImage(),
				  'source_id' => $this->getPublisher(),
                  'columnName' => $this->getColumnName(),
				  'type' => $this->getType(),
				  'author' => $this->getAuthor(),
				  'postdate' => $this->getPostDate(),
				  'lastdate' => $this->getLastDate(),
				  'lastuid' => $this->getLastUid(),
				  'cid' => $this->getCid(),
				  'level' => $this->getLevel(),
				  'content_length' => $this->getContentLength(),
				  'status' => $this->getStatus(),
				  'template' => $this->getTemplate(),
				  'islink' => $this->getIsLink(),
				  'is_ad' => $this->getIs_ad(),
			      'is_titan' => $this->getIs_titan(),
			      'albumid' => $this->getAlbumid()	
		);
		if ($this->channel == 'h5') {
			$data['gift'] = $this->getGift();
			$data['phoneOnly'] = $this->getPhoneOnly();
			$data['showNum'] = $this->getShowNum();
		}
		self::checkArticleTableAlbumId();
		if($this->getIsLink() OR $this->getUrl()!='')$data['url']=$this->getUrl();
		$this->modifyInfo($data,'aid='.$this->getId());
		$this->modifyContents();
		$this->deleteArticleTags();
		$this->addArticleTags();
		$this->deletePushCid($this->id);
		$this->addPushCid($this->id,$this->push_cid);
	}
	//修改文章信息
	public function modifyInfo($array,$where)
	{
		$this->db->update(self::TABLE,$array,$where);
	}
	private function modifyContents()
	{
		$nums=$this->db->fetchOne("SELECT COUNT(*) FROM ".self::TABLE_CONTENT." WHERE aid=".$this->getId());
		
		if($nums > 0)
		{
			$this->db->update(self::TABLE_CONTENT, 
				array('aid' => $this->getId(),
					  'contents' => $this->contents,
					  'related_news' => $this->related_news,
				),"aid='".$this->getId()."'");
		}else
		{
			$this->db->insert(self::TABLE_CONTENT, 
				array('aid' => $this->getId(),
					  'contents' => $this->contents,
					  'related_news' => $this->related_news,
				));
		}
	}
	#推荐此文章
	public function recommend($aid)
	{
		$this->modifyInfo(array('level'=>9),'aid='.$aid);
	}
	#取消推荐此文章
	public function cancelRecommend($aid)
	{
		$this->modifyInfo(array('level'=>3),'aid='.$aid);
	}
	//删除文章
	public function delete()
	{
		$sql="update article set status=0 where aid='".$this->getId()."'";
		$this->db->query($sql);
        $this->db->query("UPDATE categories SET article_nums = if(article_nums=0,0,article_nums-1) WHERE cid = ?", $this->getCid());
	}
	//删除文章内容
	private function deleteContents()
	{
		$sql="delete from article_contents where aid='".$this->getId()."'";
		$this->db->query($sql);
	}
	//获取文章记录总数
	public function getCount($tableadd='')
	{
		$sql="select count(*) as nums from article a ".$tableadd." where status>=1 ".$this->getWhere()."";
		return $this->db->fetchOne($sql);
	}
	//获取文章列表
	public function getList($start=0,$offset=0, $tableadd='')
	{
		$sql="select a.*,
			(select realname from users where uid=a.uid) realname,
			(select realname from users where uid=a.lastuid) lastrealname,
			(select name from article_source where id=a.source_id) publisher,
			(select name from categories where cid=a.cid) category 
		from article a ".$tableadd." where status>=1 ".$this->getWhere()." ".$this->getOrder();
        if($offset > 0) $sql.=" limit ".$start.", ".$offset;		
		return $this->db->fetchAll($sql);
	}
	//根据页码和页数获取文章列表
	public function getPageList($tableadd='')
	{
		$start=($this->page - 1) * $this->perpage;
		return $this->getList($start,$this->perpage,$tableadd);
	}
	public function getStatusSelect()
	{
		return array(1=>"未发布",2=>"已发布");
	}
	public function getYearSelect()
	{
		$array=array();
		for($i=(date("Y") - 5);$i < (date("Y")+3);$i++)
			$array[$i]=$i;
		return $array;
	}
	public function getMonthSelect()
	{
		$array=array();
		for($i=1;$i < 13;$i++)
			$array[$i]=$i;
		return $array;
	}
	public function getDaySelect()
	{
		$array=array();
		for($i=1;$i < 32;$i++)
			$array[$i]=$i;
		return $array;
	}

	public function delNull($content){
		return preg_replace("/<p[^>]*>(&nbsp;)*(　)*(\s)*( )*<\/p>/i",'',$content);
	}

	public function imageToLocal($content, $path, $url){			
		function copyImage($buffer, $path, $url) {				
			if(strpos($buffer, 'titan24.com') !== false){
				return $buffer;
			}
			$file= date('/Ymd/').md5(uniqid(rand(), true)).'.jpg';			
			$path= $path.$file;
			$path= str_replace('//', '/', $path);
			$url= $url.$file;
			$url= preg_replace("/(?<!(http:))\/\//", '/', $url);			
			makedir(dirname($path));
			if(is_dir(dirname($path)) && copy($buffer, $path)) {
				return $url;
			}else{
				return $buffer;
			}			
		}
	//	$content= preg_replace("/(http:\/\/)([\S]+)(\.jpg)/ie", "copyImage('\\0', '$path', '$url')", $content);		
	$content = preg_replace_callback("/(http:\/\/)([\S]+)(\.jpg)/ie", function($r){ 
		return copyImage($r[0],$path,$url); 
		}, $content);		
		return $content;	
	}

	public function getArticleAlbumIdData($id){	
		if(!$id)return;
		$url  = sprintf(ARTICLE_ALBUM_INTERFACE, $id);		
		$data = @unserialize(@file_get_contents($url));	
		$keywords= explode(' ' ,$data['keywords']);
		$keyword = $keywords[0];
		$href    = $data['next'];
		$data    = $data['photos'];
		$count   = @count($data);
		if($count== 0) return;
		if($count== 1){
			$val= current($data);
			$html= '<div class="foto"> <b><a href="javascript:void(0)"><img src="'.$val['src'].'" alt="'.$val['title'].'" /></a>'.$val['title'].' </b>
            <p><a href="http://pic.titan24.com" target="_blank">进入体坛网图片频道</a></p>
			</div>';
			return $html;
		}
		ob_start();
		?>
		<div class="foto">
          	<div id="picon">
          	<table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td id="fgl" title="上一张"><img src="http://www.titan24.com/style/img/fgl.gif" alt="前一张" /></td>
                    <th>
                    	<a href="javascript:void(0)"><img src="<?=$data[0]['src']?>" id="cpic" alt="<?=$data[0]['title']?>" /></a>
                    </th>
                    <td id="fgr" title="下一张"><img src="http://www.titan24.com/style/img/fgr.gif" alt="后一张" /></td>
                </tr>
            </table>
            <div class="last" id="last">
                <h4>已经浏览到最后一张，您可以</h4>
                <h5><a href="javascript:void(0)" id="review"> 重新欣赏 </a> <a href="<?=$href?>" target="_blank">进入下一组图</a></h5>
            </div>
            </div>
			<div class="pnl">
            	<a href="javascript:void(0)" class="pgl" id="pgl" title="上一组">上一组</a>
                <div id="plist">
                	<ul>
			<?php
				foreach($data as $key=> $val){
					 $class= ($key==0)? 'class="cur"':'';
                	 echo '<li '.$class.'>
                    	<a href="javascript:void(0)" rel="'.$val['src'].'"><span><img src="'.$val['square'].'" width="75" height="74" alt="'.$val['title'].'" /></span><b>'.($key+1).'</b></a>
						</li>'; 
				}
			?>
                </ul>
              </div>
                <a href="javascript:void(0)" class="pgr" id="pgr" title="下一组">下一组</a>
            </div>
            <p><a href="http://pic.titan24.com/search/s?keyword=<?=urlencode($keyword)?>" target="_blank">查看<?=$keyword?>更多图片</a></p>
          </div>	
		<?php
			$contents = ob_get_contents();     
			ob_end_clean();
			return $contents;
	}

	public function checkArticleTableAlbumId(){
		$db= $this->db;
		$field= $db->fetchRow("DESCRIBE article albumid");		
		if(empty($field)){				
			$old_cid=  $db->fetchRow("DESCRIBE article old_cid");
			if(empty($old_cid)){
				$db->query("ALTER TABLE `article` ADD `albumid` INT( 10 ) UNSIGNED NULL COMMENT '图集id'");
			}else{
				$db->query("ALTER TABLE `article` CHANGE `old_cid` `albumid` INT( 10 ) UNSIGNED NULL COMMENT '图集id'");
			}
		}			
	}
}