<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';

function NewsImgList($cid = '0', $offset = 0, $num = 10, $orderby = 'postdate', $channel='qsl')
{
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    
	$news = $db->fetchAll("SELECT a.aid, a.title, a.stitle, a.intro, a.url, a.islink, a.image, a.author, a.postdate FROM article a, cate_links c WHERE c.cid=$cid AND a.aid=c.aid AND a.status=2 AND a.level >=3 AND a.image!='' ORDER BY $orderby DESC LIMIT $offset, $num");
    return $news;
}
