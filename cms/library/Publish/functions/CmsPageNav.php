<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';
if(!isset($channel_db))
{
    $article_table = new ArticleTable();
    $channel_db = $article_table->getAdapter();
}
$GLOBALS['channel_db'] = $channel_db;

//ÆµµÀÒ³µ¼º½
function CmsPageNav($cms_pageid)
{
	$channel_db=$GLOBALS['channel_db'];
	$nav=array();
    $row=$channel_db->fetchRow("SELECT pid,parent,name,url FROM page WHERE pid='".$cms_pageid."'");
    if(empty($row))return $nav;
    $nav=CmsPageNav($row['parent']);
	$row['url']= str_replace(array('index.html', 'index.htm'),'', $row['url']);
	if($row['pid'] ==9 && $row['url']== '/2010/'){
		//$row['url']= str_replace('/2010/', 'http://2010.titan24.com/', $row['url']);
	}
    $nav[]= array('name'=>$row['name'],'url'=>$row['url']);
    return $nav;
}
?>
