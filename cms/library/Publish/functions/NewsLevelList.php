<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';
require_once MODULES_PATH . 'contents/models/Publisher.php';

function NewsLevelList($cid = '0', $offset = 0, $num = 10, $orderby = 'postdate', $level = '')
{    
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    
    $cid = trim($cid, ',');
	$level = trim($level, ',');
    
    //$newsResult = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id FROM article a, cate_links c WHERE c.cid=$cid AND a.aid=c.aid AND a.level IN ($level) AND a.status>=1 ORDER BY $orderby DESC LIMIT $offset, $num");
    
    $newsResult = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id FROM article a INNER JOIN cate_links c on a.aid=c.aid AND c.cid=$cid AND a.level IN ($level) AND a.status>=1 ORDER BY $orderby DESC LIMIT $offset, $num");
    
    /*
    $newsResult = $db->fetchAll("select aid from cate_links where cid='".$cid."'");
    foreach($newsResult as $news){
        $aidArray[]=$news['aid'];
    }
    unset($news);

    $aidStr=implode(',',$aidArray);
    $newsResult = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id FROM article a WHERE a.aid in ($aidStr) and a.level IN ($level) AND a.status>=1 ORDER BY $orderby DESC LIMIT $offset, $num");
    */
   
    foreach($newsResult as $new){
        $new['source']=Publisher::getHtmlName($db,$new['source_id']);
        if(!$new['islink']) $new['url']='http://www.v1.cn'.$new['url'];
        $video=$db->fetchOne("select videoLink from video where match(articleList) against('".$new['aid']."')");
        $new['video']=$video;
        $news[]=$new;
    }
    
    return $news;
}
