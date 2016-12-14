<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'contents/models/Publisher.php';
require_once MODULES_PATH . 'contents/models/Article.php';
require_once MODULES_PATH . 'category/models/Category.php';
require_once MODULES_PATH . 'resource/models/Resource.php';
require_once MODULES_PATH . 'template/models/Templates.php';
require_once LIBRARY_PATH . 'Publish/Article.php';
require_once LIBRARY_PATH . 'Publish/functions/NewsRelated.php';
require_once LIBRARY_PATH . 'function.global.php';


class Contents_ArticleController extends BaseController
{
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
        $this->obj=new Article($channel_db);
        $this->publisherObj=new Publisher($channel_db);
        $this->categoryObj=new Category($channel_db);
        $this->resourceObj=new Resource($channel_db);
        $this->templateObj=new TemplatesTable($channel_db);
        $this->base_path=$this->getChannelConfig()->path->images;
        $this->base_url=$this->getChannelConfig()->url->images;
        $this->push_url=$this->getChannelConfig()->url->published;
        $this->push_path=$this->getChannelConfig()->path->published;
    }
    public function indexAction()
    {
        $tableadd='';
        $this->_checkPermission('article', 'list');
        $this->view->layout()->disableLayout();
        $perpage = 30;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        $order=$this->_getParam('order', 'postdate');
        $this->obj->setOrder($order);
        $this->view->order=$order;

        $this->obj->setPage($page);
        $this->obj->setPerpage($perpage);
        #搜索
        $search_para='';
        $cid=$this->_getParam('cid', 0);
        //if(!empty($cid))$this->obj->setWhere("cid ='".$cid."'");
        if(!empty($cid)){
            //$this->obj->setWhere("aid IN(SELECT aid FROM cate_links WHERE cid='".$cid."')");
            $this->obj->setWhere('1');
            $tableadd=" inner join cate_links c on a.aid=c.aid and c.cid='".$cid."'";
        }
        $this->view->cid=$cid;
        $search_para.='cid='.$cid.'&';
        $title=$this->_getParam('title', '');
        if(!empty($title))$this->obj->setWhere("title LIKE '%".$title."%'");
        $this->view->title=$title;
        $search_para.='title='.urlencode($title).'&';
        $author=$this->_getParam('author', '');
        if(!empty($author))$this->obj->setWhere("author LIKE '%".$author."%'");
        $this->view->author=$author;
        $search_para.='author='.urlencode($author).'&';
        $realname=$this->_getParam('realname', '');
        if(!empty($realname))$this->obj->setWhere("uid IN(SELECT uid FROM users WHERE realname LIKE '%".$realname."%')");
        $this->view->realname=$realname;
        $search_para.='realname='.urlencode($realname).'&';
        $article_type=$this->_getParam('article_type', 0);
        if(!empty($article_type))$this->obj->setWhere("type='".$article_type."'");
        $this->view->article_type=$article_type;
        $search_para.='article_type='.urlencode($article_type).'&';

        list($year,$month,$day)=explode('-',$this->_getParam('startTime'));
        $startTime=$this->_getParam('startTime') ? $this->_getParam('startTime') : $this->_getParam('year')."-".$this->_getParam('month')."-".$this->_getParam('day');
        $startTime=$startTime." 00:00:01";
        $this->view->year=$this->_getParam('startTime') ? $year : $this->_getParam('year');
        $this->view->month=$this->_getParam('startTime') ? $month : $this->_getParam('month');
        $this->view->day=$this->_getParam('startTime') ? $day : $this->_getParam('day');
        $search_para.='startTime='.urlencode(str_replace(' 00:00:01','',$startTime)).'&';

        list($end_year,$end_month,$end_day)=explode('-',$this->_getParam('endTime'));
        $endTime=$this->_getParam('endTime') ? $this->_getParam('endTime') : $this->_getParam('end_year')."-".$this->_getParam('end_month')."-".$this->_getParam('end_day');
        $endTime=$endTime." 23:59:59";
        $this->view->end_year=$this->_getParam('endTime') ? $end_year : $this->_getParam('end_year');
        $this->view->end_month=$this->_getParam('endTime') ? $end_month : $this->_getParam('end_month');
        $this->view->end_day=$this->_getParam('endTime') ? $end_day : $this->_getParam('end_day');
        $search_para.='endTime='.urlencode(str_replace(' 23:59:59','',$endTime)).'&';
        if($endTime!='-- 23:59:59' and $startTime!='-- 00:00:01'){
            $this->obj->setWhere("create_time>".strtotime($startTime)." and create_time<".strtotime($endTime));
        }else{
            //$this->obj->setWhere("create_time>".(time()-7*24*60*60));
        }

        $article_nopic=$this->_getParam('article_nopic', 0);
        if(!empty($article_nopic))$this->obj->setWhere("(image='' or image like '%nopic.jpg') and template!=1023");
        $this->view->article_nopic=$article_nopic;
        $search_para.='article_nopic='.urlencode($article_nopic).'&';


        //位置
        $publisher_id= $this->_getParam('publisher_id');
        if(!empty($publisher_id)){
            $this->obj->setWhere("source_id ='".$publisher_id."'");
            $search_para.='publisher_id='.$publisher_id;
            $this->view->publisher_id= $publisher_id;
        }

        $result=$this->obj->getPageList($tableadd);
        $this->view->result=$result;
        $this->view->base_path=$this->base_path;
        $this->view->base_url=$this->base_url;
        $this->view->push_url=$this->push_url;
        $this->view->search_para=$search_para;
        $this->view->publisher_select=$this->publisherObj->getSelect();

        #分类列表
        $this->view->category_options=Category::getOptions($this->channel_db,'全部类别');

        $total = $this->obj->getCount($tableadd);
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__&'.$search_para.'&order='.$order);
        $this->view->pagebar = $pagebar;
        $this->view->channel= $this->_user['channel'];
    }

    public function pushcidAction(){
        $tableadd='';
        $this->_checkPermission('article', 'list');
        $this->view->layout()->disableLayout();
        $perpage = 30;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        $order=$this->_getParam('order', 'postdate');
        $this->obj->setOrder($order);
        $this->view->order=$order;

        $this->obj->setPage($page);
        $this->obj->setPerpage($perpage);
        #搜索
        $search_para='';
        $cid=$this->_getParam('cid', 0);
        if(!empty($cid))$this->obj->setWhere("cid ='".$cid."'");
        $this->view->cid=$cid;
        $search_para.='cid='.$cid.'&';
        $title=$this->_getParam('title', '');
        if(!empty($title))$this->obj->setWhere("title LIKE '%".$title."%'");
        $this->view->title=$title;
        $search_para.='title='.urlencode($title).'&';
        $author=$this->_getParam('author', '');
        if(!empty($author))$this->obj->setWhere("author LIKE '%".$author."%'");
        $this->view->author=$author;
        $search_para.='author='.urlencode($author).'&';
        $realname=$this->_getParam('realname', '');
        if(!empty($realname))$this->obj->setWhere("uid IN(SELECT uid FROM users WHERE realname LIKE '%".$realname."%')");
        $this->view->realname=$realname;
        $search_para.='realname='.urlencode($realname).'&';
        $article_type=$this->_getParam('article_type', 0);
        if(!empty($article_type))$this->obj->setWhere("type='".$article_type."'");
        $this->view->article_type=$article_type;
        $search_para.='article_type='.urlencode($article_type).'&';
        //位置
        $publisher_id= $this->_getParam('publisher_id');
        if(!empty($publisher_id)){
            $this->obj->setWhere("source_id ='".$publisher_id."'");
            $search_para.='publisher_id='.$publisher_id;
            $this->view->publisher_id= $publisher_id;
        }

        $result=$this->obj->getPageList($tableadd);
        $this->view->result=$result;
        $this->view->base_path=$this->base_path;
        $this->view->base_url=$this->base_url;
        $this->view->push_url=$this->push_url;
        $this->view->search_para=$search_para;
        $this->view->publisher_select=$this->publisherObj->getSelect();


        #分类列表
        $this->view->category_options=Category::getOptions($this->channel_db,'全部类别');

        $total = $this->obj->getCount($tableadd);
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__&'.$search_para.'&order='.$order);
        $this->view->pagebar = $pagebar;
    }

    public function savepushcidAction(){
        $cid=$this->_getParam('cid');
        $aids=$this->_getParam('aids');
        if($cid and $aids){
            foreach($aids as $aid){
                $result=$this->channel_db->fetchRow('select * from cate_links where aid="'.$aid.'" and cid="'.$cid.'"');
                if(!$result){
                    $this->channel_db->query("insert ignore into cate_links(aid,cid) values ('".$aid."','".$cid."')");
                }
                $this->channel_db->query("update article set cid='".$cid."' where aid = '".$aid."'");
            }
            exit('<script type="text/javascript" src="/scripts/jquery/jquery.js"></script><script>alert("操作成功");parent.$("input:checked").parent().parent().fadeOut("slow");</script>');
        }else{
            exit('<script type="text/javascript" src="/scripts/jquery/jquery.js"></script><script>alert("缺少参数");</script>');
        }
        exit;
    }

    public function addAction()
    {
        $this->_checkPermission('article', 'add');
        $this->view->layout()->disableLayout();
        #分类列表
        $this->view->category_options=Category::getOptions($this->channel_db,'顶级类别');
        #位置分类文件内容
        $this->view->push_category_file_contents=file_get_contents($this->getChannelConfig()->path->push_category_file);
        #接收分类ID
        $cid=$this->_getParam('cid');
        if(empty($cid))$cid=@file_get_contents($this->getChannelConfig()->path->lastcid.$this->_user['uid'].'.txt');
        #新华社文章推送
        $this->view->title=$this->_getParam('title','');
        $this->view->contents=$this->_getParam('contents','');
        $this->view->publisher=$this->_getParam('publisher','');
        $this->view->author=$this->_getParam('author','');
        $this->view->tags=$this->_getParam('tags','');
        $this->view->postdate=$this->_getParam('postdate',TIME_NOW);
        #模板列表
        $this->view->templates=$this->templateObj->fetchByType(1);

        $this->view->cid=$cid;
        $this->view->publisher_select=$this->publisherObj->getSelect();
        $this->view->level_select=$this->obj->getLevelSelect();
        $this->view->status_select=$this->obj->getStatusSelect();
        $this->view->year_select=$this->obj->getYearSelect();
        $this->view->month_select=$this->obj->getMonthSelect();
        $this->view->day_select=$this->obj->getDaySelect();
        $this->view->channel= $this->_user['channel'];
        //$this->view->weiboUrl= getWeiboUrl($this->_user['channel']);
        //$this->view->template_select = $this->templateObj->toSelectOptions('请选择模板');
        $this->view->guisePV=RAND(100,1000);
    }

    public function setchannelAction()
    {
        //$this->_checkPermission('article', 'push');
        if (!$this->_acl->isAllowed($this->_user['usergroup'], 'article', 'push')){
            //throw new UserException('你没有执行此操作的权限'); 
            exit('denied');
        }
        $channel = $this->_getParam('channel');
        if(!empty($channel)){
            $channels=Zend_Registry::get('settings')->channels->toArray();
            if(isset($channels[$channel])){
                $user= new Zend_Session_Namespace('user');
                $user->channel= $channel;
                $category_options= Category::getOptions($this->getChannelDbAdapter(),'顶级类别');
                $category_options_contents= $this->view->formSelect('cid', null, array('onchange'=>'showPosition(this)'), $category_options);
                $push_category_file_contents= file_get_contents($this->getChannelConfig()->path->push_category_file);
                //template
                $templateObj= new TemplatesTable($this->getChannelDbAdapter());
                $templates= $templateObj->fetchByType(1);
                $templates_contents= $this->view->formSelect('template', null, null, $templates);
                //publisher
                $publisherObj= new Publisher($this->getChannelDbAdapter());
                $publisher=	$publisherObj->getSelect();
                $publisher_contents= $this->view->formSelect('publisher_id', null,array('onchange'=>'this.form.publisher.value=this.options[this.selectedIndex].innerText'), $publisher);
                $content= $category_options_contents.'(######)'.$push_category_file_contents.'(######)'.$templates_contents.'(######)'.$publisher_contents;
                //exit(iconv("GBK", "UTF-8//IGNORE", $content));
            }
        }
    }

    public function pushAction()
    {
        $this->_checkPermission('article', 'push');
        $this->view->layout()->disableLayout();
        #分类列表
        $this->view->category_options= Category::getOptions($this->channel_db,'顶级类别');
        #位置分类文件内容
        $this->view->push_category_file_contents= file_get_contents($this->getChannelConfig()->path->push_category_file);
        #接收分类ID
        $cid=$this->_getParam('cid');
        if(empty($cid))$cid=@file_get_contents($this->getChannelConfig()->path->lastcid.$this->_user['uid'].'.txt');
        #新华社文章推送
        $this->view->title=$this->_getParam('title','');
        $this->view->contents=$this->_getParam('contents','');
        $this->view->publisher=$this->_getParam('publisher','');
        $this->view->author=$this->_getParam('author','');
        $this->view->tags=$this->_getParam('tags','');
        $this->view->postdate=$this->_getParam('postdate',TIME_NOW);
        #模板列表
        $this->view->templates=$this->templateObj->fetchByType(1);

        $this->view->cid=$cid;
        $this->view->publisher_select=$this->publisherObj->getSelect();
        $this->view->level_select=$this->obj->getLevelSelect();
        $this->view->status_select=$this->obj->getStatusSelect();
        $this->view->year_select=$this->obj->getYearSelect();
        $this->view->month_select=$this->obj->getMonthSelect();
        $this->view->day_select=$this->obj->getDaySelect();
        //$this->view->template_select = $this->templateObj->toSelectOptions('请选择模板');			
        //$this->view->channel= $this->_user['channel'];	;
        $this->view->channels= array('soccer'=>'国际','cnsoccer'=>'国内','sports'=>'综合','basketball'=>'篮球','sports'=>'综合','lottery'=>'财经','news'=>'万象');
    }

    //返回在栏目中设置的内容页模版id。
    public function ajaxAction(){
        $cid=$this->_getParam('cid');
        $result=$this->channel_db->fetchRow('select articleTemplates from categories a,page b where a.bind_id=b.pid and a.cid="'.$cid.'"');
        echo $result['articleTemplates'];
        exit;
    }

    public function createAction()
    {
        $this->_checkPermission('article', 'add');
        $cid=$this->_getParam('cid');
        $this->obj->channel = $this->_user['channel'];
        $this->obj->setTitle($this->_getParam('title'));
        $this->obj->setShortTitle($this->_getParam('short_title'));
        $this->obj->setIs_ad($this->_getParam('is_ad',0));
        $this->obj->setIs_titan($this->_getParam('is_titan',0));
        $this->obj->setIntro($this->_getParam('intro'));
        if ($this->_user['channel'] == 'h5') {
            $this->obj->setGift($this->_getParam('gift'));
            $this->obj->setPhoneOnly($this->_getParam('phoneOnly', 0));
            $this->obj->setShowNum($this->_getParam('showNum'));
        }
        $this->obj->setIsLink($this->_getParam('islink'));//外链
        $this->obj->setTags($this->_getParam('tags'));//文章标签关键字
        $publisher_id=$this->publisherObj->getIdByName($this->_getParam('publisher'));
        $this->obj->setPublisher($publisher_id);
        $this->obj->setColumnName($this->_getParam('columnName'));  //设置栏目
        $this->obj->setType($this->_getParam('article_type',0));
        $this->obj->setAuthor($this->_getParam('author'));
        $this->obj->setUid($this->_user['uid']);//文章创建者
        $this->obj->setLastUid($this->_user['uid']);//最后更改者
        $this->obj->setPostDate(mktime($this->_getParam('hour'),$this->_getParam('minute'),$this->_getParam('second'),$this->_getParam('post_month'),$this->_getParam('post_day'),$this->_getParam('post_year')));//发布日期
        $this->obj->setLastDate(TIME_NOW);
        $this->obj->setTemplate($this->_getParam('template'));
        $this->obj->setCid($cid);//主分类ID
        $parent_cid=$this->categoryObj->getAllParentsId($cid,true);
        $push_cid=$this->_getParam('push_cid');
        if(!empty($push_cid))$parent_cid=array_merge($push_cid,$parent_cid);
        $this->obj->setPushCid($parent_cid);//推送分类
        $this->obj->setLevel($this->_getParam('level'));//权重
        $this->obj->setContentLength($this->_getParam('content_length'));//文章字数
        $this->obj->setStatus($this->_getParam('status'));//发布状态
        if($this->_getParam('imageToLocal'))$this->obj->setContents($this->obj->delNull($this->obj->imageToLocal($this->_getParam('contents'), $this->base_path, $this->base_url)));//内容
        else $this->obj->setContents($this->obj->delNull($this->_getParam('contents')));//内容
        //echo stripslashes($this->_getParam('contents'));die;
        if($this->_getParam('related_news_id')){
            $relatedID=$this->_getParam('related_news_id');
        }else{
            //从搜索引擎中取数据
            $relatedArray=$this->obj->getRelatedNewsForSolr(str_replace(' ','-',$this->_getParam('tags')),$cid);
            if($relatedArray){
                foreach($relatedArray as $related){
                    $relatedID[]=$related['aid'];
                }
            }
        }
        $this->obj->setRelatedNews($relatedID);//相关文章
        $this->obj->setImage($this->_getParam('image'));
        $this->obj->setAlbumid($this->_getParam('albumid'));
        $this->obj->setInsertTime($this->_getParam('insert_time'));

        $aid= $this->obj->add();
        if(!$aid){
            $this->flash('添加文章失败,可能的原因是标题重复!', '/contents/article/add/cid/'.$cid, 2);
            return false;
        }

        $vids=$this->_getParam('vids');
        if($aid and $vids){
            $vidArray=explode('|',$vids);
            $vidArray=array_unique($vidArray);
            foreach($vidArray as $vid){
                if($vid){
                    $sqlAid=$aid.'|';
                    $this->channel_db->query("update video set articleList=CONCAT(articleList,'".$sqlAid."') where vid='".$vid."'");
                }
            }
            //$this->db->query("")
        }
        //主分类信息
        $this->categoryObj->init($this->_getParam('cid'));
        if($this->obj->getIsLink()){
            $url=$this->_getParam('url');
        }else{
            $url=$this->categoryObj->getPublisherDir().$this->_getParam('cid').'/'.$aid.'.shtml';
            //$url=$this->categoryObj->getPublisherDir().date("Y-m-d",TIME_NOW).'/'.$aid.'.shtml';
        }
        $this->obj->modifyInfo(array('url'=>$url),'aid='.$aid);
        #记住当前用户最后一次操作文章的cid,注：iframe下cookie有问题
        //@file_put_contents($this->getChannelConfig()->path->lastcid.$this->_user['uid'].'.txt',$this->view->cid);

        /*
        //weibo		
        if($this->obj->getIsLink()){
            $pushUrl=$this->_getParam('url');
        }else{
            $pushUrl= $this->push_url. $this->categoryObj->getPublisherDir().date("Y-m-d",TIME_NOW).'/'.$aid.'.shtml';
        }
        */

        JTPC('videoData.guisePV',array('aid'=>$aid,'cid'=>$cid,"views"=>$this->_getParam('views'))); //更新播放美化数据

        //$pushUrl= preg_replace("/(?<!(http:))\/\//", '/', $pushUrl);
        //if($this->_getParam('addToTitanWeibo'))addToTitanWeibo($this->_user['channel'], $this->_getParam('intro'), $pushUrl, $this->_getParam('image'));
        if($this->_getParam('spider') or $this->_getParam('publishArticle')){
            $pub = new Publish_Article($this->getChannelConfig(), $aid);
            $pub->publish();
        }

        if($this->_getParam('status')=='2'){
            //投放到搜索引擎
            $solrResult=@$this->obj->putSolr($aid);
        }

        $edit_msg = $this->_editGameHtmlForSEO($this->_getParam('short_title'), $this->_getParam('title'));
        $this->flash('添加文章成功'.$solrResult . "<br />" . $edit_msg, '/contents/article/publish/id/'.$aid,0);
    }

    //接收其他程序导入的文章内容接口
    public function joinAction()
    {
        $cid=$this->_getParam('cid');

        $p['cid'] = $cid;
        $p['title'] = $this->_getParam('title');
        $p['source'] = $this->_getParam('publisher');
        $p['keyword'] = $this->_getParam('tags');

        //添加文章
        $this->obj->setId($this->_getParam('aid',0));
        $this->obj->setTitle($this->_getParam('title'));
        $this->obj->setShortTitle($this->_getParam('short_title'));
        $this->obj->setIs_ad($this->_getParam('is_ad',0));
        $this->obj->setIs_titan($this->_getParam('is_titan',0));
        $this->obj->setIntro($this->_getParam('intro'));
        $this->obj->setIsLink($this->_getParam('islink'));//外链
        $this->obj->setTags($this->_getParam('tags'));//文章标签关键字
        $publisher_id=$this->publisherObj->getIdByName($this->_getParam('publisher'));
        $this->obj->setPublisher($publisher_id);
        $this->obj->setType($this->_getParam('article_type',0));
        $this->obj->setAuthor($this->_getParam('author'));
        $this->obj->setUid($this->_user['uid']);//文章创建者
        $this->obj->setLastUid($this->_user['uid']);//最后更改者
        $this->obj->setPostDate(mktime($this->_getParam('hour'),$this->_getParam('minute'),$this->_getParam('second'),$this->_getParam('post_month'),$this->_getParam('post_day'),$this->_getParam('post_year')));//发布日期
        $this->obj->setLastDate(TIME_NOW);
        $this->obj->setTemplate($this->_getParam('template'));
        $this->obj->setCid($cid);//主分类ID
        $parent_cid=$this->categoryObj->getAllParentsId($cid,true);
        $push_cid=$this->_getParam('push_cid');
        if(!empty($push_cid))$parent_cid=array_merge($push_cid,$parent_cid);
        $this->obj->setPushCid($parent_cid);//推送分类
        $this->obj->setLevel($this->_getParam('level'));//权重
        $this->obj->setContentLength($this->_getParam('content_length',0));//文章字数
        $this->obj->setStatus($this->_getParam('status'));//发布状态
        $contents=$this->_getParam('contents');
        if($this->_getParam('imageToLocal'))$this->obj->setContents($this->obj->delNull($this->obj->imageToLocal($contents, $this->base_path, $this->base_url)));//内容
        else $this->obj->setContents($this->obj->delNull($contents));//内容

        $relatedID=$this->_getParam('related_news_id');
        $this->obj->setRelatedNews($relatedID);//相关文章
        $this->obj->setImage($this->_getParam('image'));
        $this->obj->setAlbumid($this->_getParam('albumid'));
        $this->obj->setInsertTime($this->_getParam('insert_time',TIME_NOW));

        $aid= $this->obj->joinArticleData();
        if(!$aid){
            exit("添加文章失败");
            return false;
        }

        //制作内容的URL
        $this->categoryObj->init($this->_getParam('cid'));
        if($this->obj->getIsLink())$url=$this->_getParam('url');
        else $url=$this->categoryObj->getPublisherDir().$cid.'/'.$aid.'.shtml';
        $this->obj->modifyInfo(array('url'=>$url),'aid='.$aid);

        //发布
        $pub = new Publish_Article($this->getChannelConfig(), $aid);
        $pub->publish();

        //投放到搜索引擎
        @$this->obj->putSolr($aid);
        exit($aid);
    }

    public function testAction(){
        print_r($this->obj->getRelatedNewsForSolr('高速-飙车',1147));
        exit;
    }

    public function deleteallAction(){
        if ($this->isPost()) {
            $deleteConten=$this->_getParam("allUrl","");
            $deleteContenArray=preg_split('/\s/i',$deleteConten);
            foreach($deleteContenArray as $url){
                if(!$url) continue;
                preg_match("/http\:\/\/(.*?)\/.*?\/([0-9]+)\..*?html/i",$url,$idArray);
                switch($idArray[1]){
                    case 'www.v1.cn' :
                        $channel='vodone';
                        break;
                    case 'link.v1.cn' :
                        $channel='spider';
                        break;
                    case 'cms.v1.dev' :
                        $channel='v1';
                    default :
                        $channel='';
                }
                $id=$idArray[2];
                if($channel and $id){
                    $channel_db = $this->getChannelDbAdapter($channel);
                    $this->obj= new Article($channel_db, $id);
                    $this->obj->delete();
                    $url= $this->obj->getUrl();
                    $link= $this->obj->getIsLink();
                    $file= $link?'': realpath($this->push_path.$url);
                    if(is_file($file)){
                        $dir= dirname($file);
                        chdir($dir);
                        foreach(glob('*.shtml') as $filename) {
                            if(preg_match("/^($id)[_]?[0-9]*(\.shtml)$/i", $filename)){
                                file_put_contents($filename,'<html><head><meta http-equiv="refresh" content="0; url=http://www.v1.cn" /></head></html>');
                                //@unlink($filename);
                            }
                        }
                    }
                    clearstatcache();
                    $cid= $this->_getParam('cid');
                    //撤销投放搜索引擎
                    @$this->obj->delSolr($id);

                    //非外链文章删除后刷新CDN
                    if(!$link){
                        $articleUrl=$this->push_url.substr($url,1);
                        file_get_contents('http://ccms.chinacache.com/index.jsp?user=vodone-correct&pswd=V-correct&ok=ok&urls='.$articleUrl);
                    }
                    echo "<font color='green'>删除成功 ID:".$id." channel:".$channel."</font><br>";
                }else{
                    echo "<font color='red'>删除失败 ID:".$id." channel:".$channel."</font><br>";
                }
            }
            exit;
        }
        echo '请贴入要删除的URL地址，每行一条：<form name="deleteAll" method="post" action=""><textarea name="allUrl" style="width: 100%;height: 500px;"></textarea><input type="submit" value="删除全部"/></form>';
        exit;
    }

    public function editAction()
    {

        $this->_checkPermission('article', 'edit');
        $this->view->layout()->disableLayout();
        #分类列表
        $this->view->category_options=Category::getOptions($this->channel_db,'顶级类别');
        #位置分类文件内容

        $this->view->push_category_file_contents=file_get_contents($this->getChannelConfig()->path->push_category_file);
        #模板列表
        $this->view->templates=$this->templateObj->fetchByType(1);

        $this->view->headScript()->appendFile('/scripts/dtree.js');
        $this->view->headLink()->appendStylesheet('/styles/dtree.css');
        $this->view->publisher_select=$this->publisherObj->getSelect();
        $this->view->level_select=$this->obj->getLevelSelect();
        $this->view->status_select=$this->obj->getStatusSelect();
        $this->view->year_select=$this->obj->getYearSelect();
        $this->view->month_select=$this->obj->getMonthSelect();
        $this->view->day_select=$this->obj->getDaySelect();
        $this->obj->init($this->_getParam('id'));
        $this->view->id=$this->obj->getId();
        $this->view->title=$this->obj->getTitle();
        $this->view->short_title=$this->obj->getShortTitle();
        $this->view->is_ad= ($this->obj->getIs_ad())==1? 'checked':'';
        $this->view->is_titan= ($this->obj->getIs_titan())==1? 'checked':'';
        $this->view->intro=$this->obj->getIntro();
        if ($this->_user['channel'] == 'h5') {
            $this->view->gift = $this->obj->getGift();
            $this->view->phoneOnly = ($this->obj->getPhoneOnly()) == 1 ? 'checked' : '';
            $this->view->showNum = $this->obj->getShowNum();
        }
        $this->view->url=$this->obj->getUrl();
        $this->view->islink=$this->obj->getIsLink();
        $this->view->tags=$this->obj->getTags();
        $this->view->image=$this->obj->getImage();
        $this->view->publisher=$this->obj->getPublisher();
        $this->view->columnName=$this->obj->getColumnName();
        $this->view->article_type=$this->obj->getType();
        $this->view->author=$this->obj->getAuthor();
        $this->view->uid=$this->obj->getUid();
        $this->view->last_uid=$this->obj->getLastUid();
        $this->view->create_time=$this->obj->getCreateTime();
        $this->view->post_date=$this->obj->getPostDate();
        $this->view->template=$this->obj->getTemplate();
        $this->view->cid=$this->obj->getCid();
        //$this->view->push_cid="['".implode("','",$this->obj->getPushCid())."']";//转换成适用于jquery的字符串格式
        $this->view->push_cid=$this->obj->getPushCid();
        $this->view->level=$this->obj->getLevel();
        $this->view->pv=$this->obj->getPv();
        $this->view->status=$this->obj->getStatus();
        $this->view->contents= stripslashes($this->obj->delNull($this->obj->getContents()));
        $this->view->albumid= $this->obj->getAlbumid()? $this->obj->getAlbumid(): '';
        $this->view->related_news_json=$this->obj->getRelatedNewsToJson();
        $this->view->refer= $_SERVER['HTTP_REFERER'];
        #记住当前用户最后一次操作文章的cid,注：iframe下cookie有问题
        @file_put_contents($this->getChannelConfig()->path->lastcid.$this->_user['uid'].'.txt',$this->view->cid);
        //解决转义的问题,郁闷
        $vars= $this->view->getVars();
        htmlspecialchars_array($vars);
        $this->view->assign($vars);
        $this->view->channel= $this->_user['channel'];

        $videoData=JTPC('videoData.getVideoData',array('aid'=>$this->_getParam('id')));
        if($videoData){
            $CMSEnhanced = json_decode($videoData,true);
            $views=$CMSEnhanced['result']['data']['items'][$this->_getParam('id')]['guisePV'];
        }
        $this->view->guisePV=$views ? $views : RAND(100,1000);
    }
    public function saveAction()
    {
        $aid=$this->_getParam('id');
        $this->_checkPermission('article', 'edit');
        $cid=$this->_getParam('cid');
        $this->obj->channel = $this->_user['channel'];
        $this->obj->setId($aid);
        $this->obj->setTitle($this->_getParam('title'));
        $this->obj->setShortTitle($this->_getParam('short_title'));
        $this->obj->setIs_ad($this->_getParam('is_ad',0));
        $this->obj->setIs_titan($this->_getParam('is_titan',0));
        $this->obj->setIntro($this->_getParam('intro'));
        if ($this->_user['channel'] == 'h5') {
            $this->obj->setGift($this->_getParam('gift'));
            $this->obj->setPhoneOnly($this->_getParam('phoneOnly', 0));
            $this->obj->setShowNum($this->_getParam('showNum'));
        }
        $this->obj->setIsLink($this->_getParam('islink'));
        if($this->obj->getIsLink())$url=$this->_getParam('url');
        elseif($this->_getParam('islink',0) == $this->_getParam('oldislink')) $url='';
        else
        {
            $this->categoryObj->init($cid);
            $url=$this->categoryObj->getPublisherDir().date("Y-m-d",$this->_getParam('create_time')).'/'.$this->obj->getId().'.shtml';
        }
        $this->obj->setUrl($url);
        $this->obj->setTags($this->_getParam('tags'));
        $publisher_id=$this->publisherObj->getIdByName($this->_getParam('publisher'));
        $this->obj->setPublisher($publisher_id);
        $this->obj->setColumnName($this->_getParam('columnName'));  //设置栏目
        $this->obj->setType($this->_getParam('article_type',0));
        $this->obj->setAuthor($this->_getParam('author'));
        $this->obj->setLastUid($this->_user['uid']);//最后更改者
        $this->obj->setPostDate(mktime($this->_getParam('hour'),$this->_getParam('minute'),$this->_getParam('second'),$this->_getParam('post_month'),$this->_getParam('post_day'),$this->_getParam('post_year')));//发布日期
        $this->obj->setLastDate(time());
        $this->obj->setTemplate($this->_getParam('template'));
        $this->obj->setCid($cid);//主分类ID
        $parent_cid=$this->categoryObj->getAllParentsId($cid,true);
        $push_cid=$this->_getParam('push_cid');
        if(!empty($push_cid))$parent_cid=array_merge($push_cid,$parent_cid);
        $this->obj->setPushCid($parent_cid);//推送分类
        $this->obj->setLevel($this->_getParam('level'));//权重
        $this->obj->setContentLength($this->_getParam('content_length'));//文章字数
        $this->obj->setStatus($this->_getParam('status'));//权重
        if($this->_getParam('imageToLocal'))$this->obj->setContents($this->obj->delNull($this->obj->imageToLocal($this->_getParam('contents'), $this->base_path, $this->base_url)));//内容
        else $this->obj->setContents($this->obj->delNull($this->_getParam('contents')));//内容
        $this->obj->setRelatedNews($this->_getParam('related_news_id'));//内容
        $this->obj->setImage($this->_getParam('image'));
        $this->obj->setAlbumid($this->_getParam('albumid'));
        $this->obj->modify();

        $vids=$this->_getParam('vids');
        if($aid and $vids){
            $vidArray=explode('|',$vids);
            $vidArray=array_unique($vidArray);
            foreach($vidArray as $vid){
                if($vid){
                    $sqlAid=$aid.'|';
                    $this->channel_db->query("update video set articleList=CONCAT(articleList,'".$sqlAid."') where vid='".$vid."'");
                }
            }
            //$this->db->query("")
        }
        #记住当前用户最后一次操作文章的cid,注：iframe下cookie有问题
        @file_put_contents($this->getChannelConfig()->path->lastcid.$this->_user['uid'].'.txt',$cid);

        //weibo
        /*
        if($this->obj->getIsLink()){
            $pushUrl=$this->_getParam('url');
        }else{
            $pushUrl= $this->push_url. $this->categoryObj->getPublisherDir().date("Y-m-d",TIME_NOW).'/'.$aid.'.html';
        }
        $pushUrl= preg_replace("/(?<!(http:))\/\//", '/', $pushUrl);
        addToTitanWeibo($this->_user['channel'], $this->_getParam('intro'), $pushUrl, $image= null);
        */
        JTPC('videoData.guisePV',array('aid'=>$aid,'cid'=>$cid,"views"=>$this->_getParam('views'))); //更新播放美化数据

        if($this->_getParam('publishArticle')){
            $pub = new Publish_Article($this->getChannelConfig(), $aid);
            $pub->publish();
        }

        if($this->_getParam('status')=='2'){
            //更新到搜索引擎
            $solrResult=@$this->obj->putSolr($aid,1);
        }

        $edit_msg = $this->_editGameHtmlForSEO($this->_getParam('short_title'), $this->_getParam('title'));
        exit('<script>alert("修改文章成功\n'. $edit_msg .'");window.close();</script>');
        /*
        if($refer= $this->_getParam('refer'))$this->flash('修改文章成功', '/contents/article/publish/id/'.$aid.'/?refer='.urlencode($refer), 0);
		else $this->flash('修改文章成功', '/contents/article/publish/id/'.$aid,0);
        */
    }

    public function deleteAction()
    {
        $this->_checkPermission('article', 'delete');
        $id= $this->_getParam('id');
        $this->obj= new Article($this->channel_db, $id);
        $this->obj->delete();
        $url= $this->obj->getUrl();
        $link= $this->obj->getIsLink();
        $file= $link?'': realpath($this->push_path.$url);
        /* 删除所有分页 */
        if(is_file($file)){
            $dir= dirname($file);
            chdir($dir);
            foreach(glob('*.shtml') as $filename) {
                if(preg_match("/^($id)[_]?[0-9]*(\.shtml)$/i", $filename)){
                    file_put_contents($filename,'<html><head><meta http-equiv="refresh" content="0; url=http://www.v1.cn" /></head></html>');
                    //@unlink($filename);
                }
            }
        }
        clearstatcache();
        $cid= $this->_getParam('cid');
        //撤销投放搜索引擎
        @$this->obj->delSolr($id);

        //非外链文章删除后刷新CDN
        if(!$link){
            $articleUrl=$this->push_url.substr($url,1);
            file_get_contents('http://ccms.chinacache.com/index.jsp?user=vodone-correct&pswd=V-correct&ok=ok&urls='.$articleUrl);
        }
        $this->flash('删除文章成功、刷新CDN成功', '/contents/article/index/cid/'.$cid,0);
    }
    public function searchAction()
    {
        $this->view->layout()->disableLayout();
        $this->getResponse()->clearBody();
        $this->view->setScriptPath(MODULES_PATH . 'contents/views/scripts/article/');
        $this->view->publisher_select=$this->publisherObj->getSelect();
        $this->view->level_select=$this->obj->getLevelSelect();
        $this->view->status_select=$this->obj->getStatusSelect();
        $this->view->year_select=$this->obj->getYearSelect();
        $this->view->month_select=$this->obj->getMonthSelect();
        $this->view->day_select=$this->obj->getDaySelect();
        echo $this->renderScript('search.phtml');
    }
    //预览
    public function previewAction()
    {
        $this->_checkPermission('article', 'preview');
        $id = (int) $this->_getParam('id', 0);
        //$this->obj->init($id);
        $is_link=$this->obj->getIsLink();
        if(!empty($is_link))
        {
            header('Location:'.$this->obj->getUrl());
            exit;
        }

        //$pub = new Publish_Article($this->getChannelConfig(), $id);
        //$pub->publish();

        //$url_workplace =  Util::concatUrl($this->getChannelConfig()->url->workplace, $this->obj->getAutoUrl());
        $url_published = Util::concatUrl($this->getChannelConfig()->url->published, $this->obj->getAutoUrl());
        header('Location:'.$url_published);
        exit;
    }
    //发布
    public function publishAction()
    {

        //调用刷新手机版新闻列表脚本end
        $this->_checkPermission('article', 'publish');
        $id = (int) $this->_getParam('id', 0);
        $refer = $this->_getParam('refer', null);
        $this->obj->init($id);

        $cid = $this->obj->getCid();//获取发布内容的类型
        $this->refresh($cid);//刷新静态资源 add by huishuai 2015-10-28
        $this->rsyncImg();
        $is_link=$this->obj->getIsLink();
        if($is_link)
        {
            header('Location:'.$this->obj->getUrl());
            exit;
        }
        $pub = new Publish_Article($this->getChannelConfig(), $id);
        $pub->publish();
        $url_workplace =  Util::concatUrl($this->getChannelConfig()->url->workplace, $this->obj->getAutoUrl());
        $url_published = Util::concatUrl($this->getChannelConfig()->url->published, $this->obj->getAutoUrl());

        if($refer)$this->flash('修改文章成功', $refer, 0);
        else $this->flash('发布成功', '/contents/article/index/cid/'.$this->obj->getCid(),0);
    }

    //add by shuai refresh phone page 2015-10-28
    private function refresh($cid){
        if(!$cid){
            return false;
        }

        $gameCidList = array(1018,1001,1021,1004,1005,1006,1007,1008,1009,1010,1011,1002,1003,1012,1017,1016,1015,1014,1013);//游戏类型列表
        if(in_array($cid,$gameCidList)){

            exec("/VODONE/server/php/bin/php /VODONE/www/vodone.cms/publish/h5/admin/makePageList.php");
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, "http://h.v1game.cn/admin/makePageList.php");
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_HEADER, 0);
//            $output = curl_exec($ch);
//            curl_close($ch);
//            $host = "h.v1game.cn";
//            $fp = fsockopen($host, 80, $errno, $errstr, 30);
//            if (!$fp){
//                echo 'error fsockopen';
//            }
//            else{
//                stream_set_blocking($fp,0);
//                $http = "GET /admin/makePageList.php HTTP/1.1\r\n";
//                $http .= "Host:{$host}\r\n";
//                $http .= "Connection: Close\r\n\r\n";
//                fwrite($fp,$http);
//                fclose($fp);
//            }
        }
        return 1;
    }
    private function rsyncImg(){

        exec("/VODONE/www/vodone.cms/publish/h5/uploadimg.sh");
    }

    //让拍客使用CMS模版发布页面(未使用，留作例子)
    public function paikepublishAction()
    {
        $data['aid']='753032';  //取拍客频道的任意一片文章，用来基于此ID获取面包屑导航及频道属性
        $data['source']='第一视频';
        $data['keywords']='a,b,c';
        $data['title']='我测试1111';
        $data['stitle']='测试';
        $data['intro']='测试测试测试测试测试测试测试';
        $data['videoUrl']='http://2';
        $data['author']='handong';
        $data['realname']='handong';
        $data['postdate']='1376990688';
        $data['image']='http://www.baidu.com/logo.gif';
        $data['insert_time']='1376990688';
        if($data['image'] and $data['videoUrl']){
            $data['contents']='<div class="video_play" id="playDiv"><object width="630" height="534" id="play1" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,115,0">
                                <param value="http://www.v1.cn/player/cloud/cloud_player.swf" name="movie" />
                                <param value="#000000" name="bgcolor" />
                                <param value="id=null&amp;startSwfUrl=http://www.v1.cn/player/cloud/loading.swf&amp;videoUrl='.$data['videoUrl'].'" name="FlashVars" />
                                <param value="always" name="allowScriptAccess" />
                                <param value="true" name="allowFullScreen" />
                                <param value="opaque" name="wmode" />
                                <embed width="630" height="534" type="application/x-shockwave-flash" cover="'.$data['image'].'" wmode="opaque" allowfullscreen="true" allowscriptaccess="always" bgcolor="#000000" name="play1" flashvars="id=null&amp;startSwfUrl=http://www.v1.cn/player/cloud/loading.swf&amp;videoUrl='.$data['videoUrl'].'" src="http://www.v1.cn/player/cloud/cloud_player.swf"></embed>
                                </object>
                                </div>';
            $pub = new Publish_Article($this->getChannelConfig());
            echo $pub->publishHTML(1000,$data);
            exit('发布成功');
        }
        exit('发布失败');
    }

    //选择文章
    public function chooseAction()
    {
        $this->indexAction();
        $this->view->layout()->setLayout('layout_choose');
        $this->view->layout()->setLayoutPath(MODULES_PATH . 'contents/views/layouts/');
    }
    //文章推荐
    public function recommendAction()
    {
        $aid=(int)$this->_getParam('id');
        $this->obj->init($aid);
        $level = (int) $this->_getParam('level', 0);
        if($level==9)
            $this->obj->cancelRecommend($aid);
        else
            $this->obj->recommend($aid);
        $this->flash('操作成功', '/contents/article/index/cid/',0);
    }
    //ajax读取位置
    public function getpositionAction()
    {
        $cid=(int)$this->_getParam('cid');
        $position_cid_array=Category::getList($this->channel_db,2,$cid);
        //iconv_recursion('gbk','utf-8',$position_cid_array);
        echo  json_encode($position_cid_array);
        exit;
    }

    //频道间新闻共享 --handong
    public function shareAction(){
        $aid=(int)$this->_getParam('id');
        if($this->_getParam('channel')){
            $channel=$this->_getParam('channel');
        }else{
            $channel=$this->_user['channel'];
        }
        $this->view->showNext= $channel==$this->_user['channel'] ? false : true;
        $this->view->channel=$channel;
        $channels=Zend_Registry::get('settings')->channels->toArray();
        $this->view->channels=$channels;

        $channel_db = $this->getChannelDbAdapter($channel);
        $this->view->categories = Category::getOptions($channel_db, $channels[$channel].' 分类');

        $users=$channel_db->fetchAll("select uid,username from users order by uid asc");
        foreach($users as $user){
            $optionUser.='<option value="'.$user['uid'].'">'.$user['username'].'</option>';
        }
        $this->view->optionUser=$optionUser;

        if($this->isPost()){
            $obj=new Article($channel_db);
            $cid=$this->_getParam('cid');

            $templates=$channel_db->fetchOne("select articleTemplates from categories a,page b where a.bind_id=b.pid and a.cid='".$cid."'");
            $article=$this->channel_db->fetchRow("select a.*,b.contents,b.related_news,c.name as source from article a,article_contents b,article_source c where a.aid=b.aid and a.source_id=c.id and a.aid='".$aid."'");
            if(strlen($aid)>3){  //match against语法兼容(此语法需要关键词长度大于3)
                $video=$this->channel_db->fetchRow("select * from video where match(articleList) against('".$aid."')");
            }else{
                $video=$this->channel_db->fetchRow("select * from video where articleList like '%".$aid."|%'");
            }
            $tags=$this->channel_db->fetchAll("select b.name from article_tag a,tags b where aid='".$aid."' and a.tagid=b.tagid order by a.id asc");
            if(is_array($tags)){
                $tagArray=array();
                foreach($tags as $tag){
                    $tagArray[]=$tag['name'];
                }
                $tag= implode(' ',$tagArray);
            }

            $picLink=$video['picLink'];
            $videoLink=$video['videoLink'];
            $obj->setTitle($article['title']);
            $obj->setShortTitle($article['short_title']);
            $obj->setIs_ad($article['is_ad']);
            $obj->setIntro($article['intro']);
            $obj->setIsLink($article['islink']);//外链
            $obj->setTags($tag);//文章标签关键字
            $publisherObj=new Publisher($channel_db);
            $publisher_id=$publisherObj->getIdByName($article['source']);
            $obj->setPublisher($publisher_id);
            $obj->setType($article['type']);
            $obj->setAuthor($article['author']);
            $obj->setUid($this->_getParam('uid'));//文章创建者 
            $obj->setLastUid($this->_getParam('uid'));//最后更改者
            $obj->setPostDate($article['postdate']);//发布日期
            $obj->setLastDate(TIME_NOW);
            $obj->setTemplate($templates);
            $obj->setCid($cid);//主分类ID

            $categoryObj=new Category($channel_db);
            $parent_cid=$categoryObj->getAllParentsId($cid,true);
            $obj->setPushCid($parent_cid);//推送分类
            $obj->setLevel($article['level']);//权重
            $obj->setContentLength($article['content_length']);//文章字数
            $obj->setStatus($article['status']);//发布状态
            $obj->setContents($article['contents']);//内容
            if($article['related_news']){
                $relatedID=explode(',',$article['related_news']);
            }else{
                //从搜索引擎中取数据
                $relatedArray=$obj->getRelatedNewsForSolr(str_replace(' ','-',$tag),$cid);
                foreach($relatedArray as $related){
                    $relatedID[]=$related['aid'];
                }
            }
            $obj->setRelatedNews($relatedID);//相关文章
            $obj->setImage($article['image']);
            $obj->setAlbumid($article['albumid']);
            $obj->setInsertTime(TIME_NOW);

            $aid= $obj->joinArticleData();
            if(!$aid){
                exit("添加文章失败");
                return false;
            }

            //主分类信息
            $categoryObj->init($cid);
            $url=$categoryObj->getPublisherDir().date("Y-m-d",TIME_NOW).'/'.$aid.'.shtml';

            $obj->modifyInfo(array('url'=>$url),'aid='.$aid);

            //添加视频
            $p['cid'] = $cid;
            $p['title'] = $video['title'];
            $p['source'] = $video['source'];
            $p['keyword'] = $video['keyword'];
            $p['picLink'] = $picLink;
            $p['videoLink'] = $videoLink;
            $p['filename'] = $video['filename'];
            $p['duration'] = $video['duration'];
            $p['transcodingDate'] = $video['transcodingDate'];
            $p['articleList'] = $aid.'|';
            if($p['picLink'] and $p['videoLink'] and $p['duration']){
                $channel_db->insert('video', $p);
            }

            $pub = new Publish_Article($this->getChannelConfig($channel), $aid);
            $pub->publish();

            //投放到搜索引擎
            @$obj->putSolr($aid);

            $this->flash('操作成功', '/contents/article/index/cid/',10);
        }
    }

    /**
     * 重写游戏中html文件中的title、keywords、description
     *
     * @author Straiway
     *
     */
    private function _editGameHtmlForSEO($url, $title){
        // 仅对h5平台进行此操作
        if ($this->_user['channel'] != 'h5') {
            return '';
        }

        $base_path = '/VODONE/www/Games/'; // 抓取的游戏
        $base_path_games = '/VODONE/www/vodone.cms/publish/h5'; // 修改的游戏

        // 本地测试目录
//         $base_path = '/Applications/XAMPP/htdocs/test/';
//         $base_path_games = '/Applications/XAMPP/htdocs/test';

        $pattern_head_start = '/<head[^>]*?>/is';
        $pattern_title = '/(<title[^>]*?>.*?<\/title>)/is';
        $pattern_keywords_and_description = '/(<meta[^>]*?name=[\'"](?:keywords|description)[\'"][^>]*?content=[\'"].*?[\'"][^>]*?>)/is';
        $pattern_disabled = '/<!--old0922 (.*?) 0922old-->/is';
        $pattern_add = '/\r?\n?<!-- title\/keywords\/description start -->.*?<!-- title\/keywords\/description end -->\r?\n?/is';

        $replace = <<<HTML

<!-- title/keywords/description start -->
<title>{$title}-第一游戏网H5-一个真正会玩的游戏平台-玩游戏送红包</title>
<meta name="keywords" content="{$title}，手机游戏，H5手机游戏，H5游戏" />
<meta name="description" content="第一游戏网H5游戏平台为您提供，{$title}H5手机游戏，最好玩的H5游戏就在第一游戏网，让你在休闲中体验竞技的乐趣！" />
<!-- title/keywords/description end -->

HTML;
        // 确定要解析的URL
        if (strpos($url, 'http://h.v1game.cn/games/game_loading/index.html?gameUrl=http://') === 0) {
            $query_str = parse_url($url, PHP_URL_QUERY);
            parse_str($query_str, $params);
            $url = $params['gameUrl'];
        }

        // 下列两种类型外的文件不处理
        if (strpos($url, 'http://gf') !== 0 && strpos($url, 'http://h.v1game.cn/games/') !== 0) {
            return '';
        }

        $url_info = parse_url($url);

        // 确定游戏文件夹
        if ($url_info['host'] == 'h.v1game.cn') {
            // 修改的小游戏
            if (strpos($url_info['path'], '/games/game_loading') === 0) {
                // game_loading 文件夹中的html不修改
                return '';
            }
            $filename = $base_path_games . $url_info['path'];
        } else {
            // 抓取的游戏
            $folder1 = substr($url_info['host'], 0, strpos($url_info['host'], '.'));
            $folder2 = substr($url_info['path'], 1,strpos($url_info['path'], '/', 1));
            $basename =basename($url_info['path']);
            $relative_name = $folder1 . '/' . $folder2 . $basename;
            $filename = $base_path . $relative_name;
        }

        // 开始操作
        if (is_file($filename)) {
            // 文件存在，开始处理内容
            $file_content = file_get_contents($filename);
            if (strpos($file_content, $replace) === FALSE) {
                // 没有替换过
                // 首先恢复已经使用本程序注释过的代码，防止重复注释
                $file_content = preg_replace($pattern_disabled, '\1', $file_content);
                $file_content = preg_replace($pattern_add, '', $file_content);

                // 注释掉已有的title/keywords/description
                $file_content = preg_replace($pattern_title, '<!--old0922 \1 0922old-->', $file_content);
                $file_content = preg_replace($pattern_keywords_and_description, '<!--old0922 \1 0922old-->', $file_content);

                //开始查找位置
                if (preg_match($pattern_head_start, $file_content, $match)) {
                    $head_str = $match[0];
                    // 查到位置，开始替换
                    $file_content = substr_replace($file_content, $head_str . $replace, strpos($file_content, $head_str), strlen($head_str));
                    if (file_put_contents($filename, $file_content)) {
                        $msg = "“{$title}”处理成功。";
                    } else {
                        $msg = "“{$title}”处理失败。没有修改权限。路径：{$filename}";
                    }
                } else {
                    $msg = "“{$title}”处理失败，未能匹配到<head>标签。路径：{$filename}";
                }
            } else {
                $msg = "“{$title}”已经处理过。";
            }
        } else {
            $msg = "“{$title}”处理失败，未能成功打开文件。路径：{$filename}";
        }

        return 'SEO优化：' . $msg;
    }
}
