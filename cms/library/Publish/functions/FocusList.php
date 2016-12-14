<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';

//调用专题列表
function FocusList($cid = '0', $offset = 0, $num = 10, $orderby = 'starttime')
{
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    
    $now=mktime();
	if($cid>0){$sqladd="and cid=$cid";}else{$sqladd="";}
	$news = $db->fetchAll("SELECT * FROM focus WHERE status=1 $sqladd ORDER BY $orderby DESC LIMIT $offset, $num;");
	
    
    $config = Zend_Registry::get('channel_config');
    
    foreach($news as $k=>$v){

		$news[$k]['url']= (!$v['islink'])? $config->url->published.$v['url']:$v['url']; 
		if($news[$k]['template']=='0' && $news[$k]['title']==''){
		  $news[$k]['url']= $config->url->published."/ztm/".$v['url']; 
		}
	}
    
    return $news;
}