<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';
require_once MODULES_PATH . 'contents/models/Publisher.php';

function NewsListDate($cid = '0', $offset = 0, $num = 10, $orderby = 'postdate', $startTime='0')
{
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    $orderby = $orderby ? 'ORDER BY a.'.$orderby.' DESC,a.postdate desc' : 'ORDER BY rand()'; 
    
	$sqladd="";
	if($startTime>"0"){$startTime=strtotime($startTime);$sqladd=" and a.postdate>=".$startTime;}
    $newsResult = $db->fetchAll("SELECT a.aid, a.is_titan, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id FROM article a left join cate_links c on a.aid=c.aid WHERE c.cid=$cid ".$sqladd." AND a.status=2 and a.level >=3 $orderby LIMIT $offset, $num");

    foreach($newsResult as $new){
        $new['content']=$db->fetchOne("select contents as content from article_contents where aid='".$new['aid']."'");
        $new['source']=Publisher::getHtmlName($db,$new['source_id']);
        if(!$new['islink']) $new['url']='http://www.v1.cn'.$new['url'];
        $news[]=$new;
    }
    return $news;
}
