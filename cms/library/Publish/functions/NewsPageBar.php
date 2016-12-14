<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';
function NewsPageBar($cid,$pre,$pagesize=50)
{
    if (!$cid) {
        return false;
    }
	if(!$pre){
		$pre="http://soccer.titan24.com/app/soccer/list.php";
	}

    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
    $cid = trim($cid, ', ');
        if($cid===''){
            $sql="select count(aid) as nums from article where status>=1";
        }else{
            $sql="select count(aid) as nums from article a left join cate_links c on a.aid=c.aid where status>=1";
        }
        /*
        $sql_cids = $cid === '' ? '' : "AND (aid IN(SELECT aid FROM cate_links WHERE cid IN($cid)))";
        $sql="select count(aid) as nums from article where status>=1 ".$sql_cids;
        */
        
        $num =$db->fetchOne($sql);
        //$url= LIST_URL."list-{$cid}-__page__.html";
        $url= $pre."?cid={$cid}&pagesize={$pagesize}&page=__page__";
        $html= Util::buildPagebar($num, $pagesize, 1, $url);
        echo $html;
}