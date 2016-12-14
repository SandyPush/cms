<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';
require_once MODULES_PATH . 'contents/models/Publisher.php';

function NewsList($cid = '0', $offset = 0, $num = 10, $orderby = '', $channel='qsl')
{
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    $orderby = $orderby ? 'ORDER BY a.'.$orderby.' DESC' : 'ORDER BY rand()'; 
	//$newsResult = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id,(select contents from article_contents where aid=a.aid) content FROM article a, cate_links c WHERE c.cid=$cid AND a.aid=c.aid AND a.status=2 and level >=3 ORDER BY $orderby DESC LIMIT $offset, $num");
    
    /*
    if($cid<=1043){
        $newsResult = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id, b.contents as content FROM article a use index (postdate) left join article_contents b on a.aid=b.aid left join cate_links c on a.aid=c.aid WHERE c.cid=$cid AND a.status=2 and a.level >=3 $orderby LIMIT $offset, $num");
    }else{
        $newsResult = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id, b.contents as content FROM article a left join article_contents b on a.aid=b.aid left join cate_links c on a.aid=c.aid WHERE c.cid=$cid AND a.status=2 and a.level >=3 $orderby LIMIT $offset, $num");
    }
    */
    
    $newsResult = $db->fetchAll("SELECT a.aid, a.is_titan, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv, a.source_id FROM article a left join cate_links c on a.aid=c.aid WHERE c.cid=$cid AND a.status=2 and a.level >=3 $orderby LIMIT $offset, $num"); 
    foreach($newsResult as $new){
        $new['content']=$db->fetchOne("select contents as content from article_contents where aid='".$new['aid']."'");
        $new['source']=Publisher::getHtmlName($db,$new['source_id']);
        if(!$new['islink']) $new['url']='http://www.v1.cn'.$new['url'];
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
