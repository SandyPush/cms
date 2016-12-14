<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';
if(!isset($channel_db))
{
    $article_table = new ArticleTable();
    $channel_db = $article_table->getAdapter();
}
$GLOBALS['channel_db'] = $channel_db;
//ÏÔÊ¾Í·Í¼
function Cms_Flashppt($cms_pageid,$cms_oid=0,$num=100)
{
	require_once MODULES_PATH . 'flashppt/models/Flashppt.php';
	$flashppt=new flashppt($GLOBALS['channel_db']);
	return $flashppt->listPage($cms_pageid,1,$num,$cms_oid);
}
?>
