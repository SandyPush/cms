<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';
require_once LIBRARY_PATH . 'function.global.php';

//生成搜索引擎xml专用
function seoData($cms_page_id){
    $resultNewArray=array();
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    $sql="select description from page where pid='".$cms_page_id."'";
    $lastUpdateTime=$db->fetchOne($sql);
    $lastUpdateTime=$lastUpdateTime ? strtotime($lastUpdateTime) : time();
    //$newsArray = $db->fetchAll("SELECT a.*,(select name from categories where cid=a.cid) category from  article a where status=2 and postdate>".$lastUpdateTime." order by postdate desc");
    $newsArray = $db->fetchAll("SELECT a.*,(select name from categories where cid=a.cid) category from  article a where status=2 and template=1000 and create_time>UNIX_TIMESTAMP('".date('Y-m-d')." 00:00:01') order by create_time desc");
    if($newsArray){ 
        foreach($newsArray as $new){
            $new['title']=preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$new['title']);
            $new['intro']=preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$new['intro']);
            $new['create_time']=date("Y-m-d H:i:s",$new['create_time']);
	    $new['image']=str_replace('-s.jpg','.jpg',$new['image']);
            //tag
            $sql="select b.name from article_tag a,tags b where aid='".$new['aid']."' and a.tagid=b.tagid order by a.id asc";
            $tagQuery=$db->fetchAll($sql);
            $tagArray=array();
            foreach($tagQuery as $tag){
                $tagArray[]=$tag['name'];
            }
            $new['tagArray']=$tagArray;
            $new['tag']= implode(' ',$new['tagArray']);
            //videoLink
            //$sql="select videoLink,duration from video where articleList like '%".$new['aid']."%'";
            $sql="select videoLink,duration,articleList from video where match(articleList) against('".$new['aid']."')";
            $video=$db->fetchRow($sql);
            $new['videoLink']=$video['videoLink'];
            $new['duration']=$video['duration'] > 1 ? $video['duration'] : rand(120,360);
            
            $videoData=JTPC('videoData.getVideoData',array('aid'=>$new['aid']));
            if($videoData) $CMSEnhanced = json_decode($videoData,true);
            $new['pv']=$CMSEnhanced['result']['data']['items'][$new['aid']]['guiseVV'] ? $CMSEnhanced['result']['data']['items'][$new['aid']]['guiseVV'] : 0;
            $resultNewArray[]=$new;
        }
    }
    $db->query("update page set description='".date('Y-m-d H:i:s',time())."' where pid='".$cms_page_id."'");
    return $resultNewArray;
}
?>
