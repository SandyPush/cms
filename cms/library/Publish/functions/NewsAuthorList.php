<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';

//按作者调用新闻
function NewsAuthorList($author = '', $cid,$num = 10)
{
    //$user= Zend_Session::namespaceGet('user');
	//$channel= $user['channel'];
	$article_table = new ArticleTable();
    $db = $article_table->getAdapter();
	$cid= intval($cid);
	$add= $cid? "AND aid IN (SELECT aid FROM cate_links WHERE cid='$cid')":'';
    
    $news = $db->fetchAll("SELECT aid, title, stitle, intro, url,islink, image, author,postdate FROM article WHERE author = '$author' $add AND status>=1 and level!=2 ORDER BY postdate DESC LIMIT $num");
	//foreach($news as $k=>$v){
		//$news[$k]['url']= (!$v['islink'])? "http://".$channel.".titan24.com".$v['url']:$v['url'];
		//if($channel=="qipai"){$news[$k]['url']= (!$v['islink'])? "http://sports.titan24.com/qipai".$v['url']:$v['url'];}
	//}
    
    return $news;
}
