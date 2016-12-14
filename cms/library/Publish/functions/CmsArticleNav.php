<?php
require_once LIBRARY_PATH . 'Publish/functions/CmsPageNav.php';
//ÎÄÕÂÖÕ¼«Ò³µ¼º½
function CmsArticleNav($cms_aid)
{
	global $channel_db;
	$nav=array();
    $row=$channel_db->fetchRow("SELECT aid,stitle,cid,url,islink,(SELECT bind_id FROM categories WHERE cid=a.cid LIMIT 1) pid FROM article a WHERE aid='".$cms_aid."'");
    $nav=CmsPageNav($row['pid']);
    //$nav[]=array('name'=>$row['stitle'],'url'=>$row['url']);
    return $nav;
}
?>
