<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';

function NewsRelated($aid, $nums=6)
{
    $article_table = new ArticleTable();
    $db = $article_table->getAdapter();
	$related_aid=array();
    #手动相关新闻
    $related_aid_str=$db->fetchOne("SELECT related_news FROM article_contents WHERE aid='".$aid."'");
    if(!empty($related_aid_str))
	{
		$related_aid=explode(',',$related_aid_str);
	    $sql = "SELECT aid FROM article  WHERE aid IN(".implode(',',$related_aid).") AND status>=1 order by substring_index('".$related_aid_str."',aid,1)";
	    $related_aid = $db->fetchCol($sql);
	}
	#如果没有手动相关新闻则自动获取相关新闻
	if(empty($related_aid) or count($related_aid) < $nums)
	{
		#根据关键字依次搜索相关新闻
		$sql="select aid,tagid from article_tag WHERE aid=".$aid." ORDER BY id ASC LIMIT ".($nums - count($related_aid));
		$query=$db->query($sql);
		while($row=$query->fetch())
		{
			$rel_sql="SELECT aid FROM article_tag WHERE tagid=".$row['tagid']." AND (aid>".$row['aid']." OR aid<".$row['aid'].") ORDER BY aid DESC LIMIT ".($nums - count($related_aid));
			$related_aid=array_merge($related_aid,$db->fetchCol($rel_sql));
			if(count($related_aid)==$nums)break;
		}
		#如果关键字相关新闻不足，则取此新闻主分类下的最新新闻补足
		if(count($related_aid) < $nums)
		{
			$rel_sql="SELECT aid FROM article WHERE cid=(SELECT cid FROM article WHERE aid=".$aid.") AND (aid>".$aid." OR aid<".$aid.") AND status>=1 ORDER BY aid DESC LIMIT ".($nums - count($related_aid));
			$related_aid=array_merge($related_aid,$db->fetchCol($rel_sql));
		}
	}
	if(empty($related_aid))return false;
    $db->query("update article_contents set related_news='".implode(',',$related_aid)."' where aid='".$aid."'");
    $sql = "SELECT a.aid, title, stitle, intro, url,islink, image, author, postdate FROM article a WHERE aid IN(".implode(',',$related_aid).") order by substring_index('".implode(',',$related_aid)."',aid,1)";
    $news = $db->fetchAll($sql);
    return $news;
}
?>
