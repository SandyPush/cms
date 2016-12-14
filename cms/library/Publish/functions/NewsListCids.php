<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';

function NewsListCids($cid = '0', $offset = 0, $num = 10, $orderby = 'postdate', $channel='soccer')
{
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    
    $cid = trim($cid, ',');
    $news = $db->fetchAll("SELECT distinct a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate, a.pv FROM article a, cate_links c WHERE c.cid IN($cid) AND a.aid=c.aid AND a.status>=1 AND a.level >=3 ORDER BY $orderby DESC LIMIT $offset, $num");
	foreach($news as $k=>$v)$news[$k]['url']= (!$v['islink'])? "http://".$channel.".titan24.com".$v['url']:$v['url'];
    
    return $news;
}