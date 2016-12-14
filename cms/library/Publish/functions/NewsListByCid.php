<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';

function NewsListByCid($cid = '0', $num = 10, $orderby = 'postdate')
{
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    
    $data="";
	$cid = trim($cid, ',');
    $news = $db->fetchAll("SELECT aid, title, url, postdate FROM article WHERE cid IN($cid) AND status>=1 ORDER BY $orderby DESC LIMIT $num");
	return $news;
}