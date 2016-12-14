<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';
require_once MODULES_PATH . 'contents/models/Publisher.php';

function SpiderNewsList($cid = '0', $offset = 0, $num = 10, $orderby = 'postdate', $channel='qsl')
{
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    
	//$newsResult = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id,(select contents from article_contents where aid=a.aid) content FROM article a, cate_links c WHERE c.cid=$cid AND a.aid=c.aid AND a.status=2 and level >=3 ORDER BY $orderby DESC LIMIT $offset, $num");
    if($cid<=1043){
        $newsResult = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id, b.contents as content FROM article a use index (postdate) left join article_contents b on a.aid=b.aid left join cate_links c on a.aid=c.aid WHERE c.cid=$cid AND a.status=2 and a.level >=3 ORDER BY a.postdate DESC LIMIT $offset, $num");
    }else{
        $newsResult = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id, b.contents as content FROM article a left join article_contents b on a.aid=b.aid left join cate_links c on a.aid=c.aid WHERE c.cid=$cid AND a.status=2 and a.level >=3 ORDER BY a.postdate DESC LIMIT $offset, $num");
    }
    
    foreach($newsResult as $new){
        $new['source']=Publisher::getHtmlName($db,$new['source_id']);
        if(!$new['islink']) $new['url']='http://link.v1.cn'.$new['url'];
        $news[]=$new;
    }
    
/*
    foreach($news as $k=> &$v){
		
		if($channel=="qipai"){$news[$k]['url']= (!$v['islink'])? "http://sports.titan24.com/qipai".$v['url']:$v['url'];}
		else{$news[$k]['url']= (!$v['islink'])? "http://".$channel.".titan24.com".$v['url']:$v['url'];}		
		if($channel=="home" && !$v['islink'] && in_array($cid, range(19, 30))){
			$v['url']= str_replace('home.', 't.', $v['url']);
			$v['url']= str_replace('microblog', '', $v['url']);
			$v['url']= preg_replace("/(?<!(http:))\/\//", '/', $v['url']);
		}
	}
*/
    return $news;
}
