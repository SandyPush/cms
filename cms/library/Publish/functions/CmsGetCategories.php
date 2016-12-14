<?php
require_once LIBRARY_PATH . 'Publish/models/Article.php';

//文章终极页分类名称
function CmsGetCategories($cms_cid)
{
	global $channel_db;
    if(!isset($channel_db))
    {
        $article_table = new ArticleTable();
        $channel_db = $article_table->getAdapter();
    }
    $row=$channel_db->fetchRow("SELECT * FROM categories WHERE cid='".$cms_cid."'");
    return $row;
}
?>
