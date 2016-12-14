<?php
require_once MODULES_PATH . 'contents/models/Article.php';

function getSolr($key = '',$cid = '')
{
    $article=new Article();
    $relatedNews=$article->getRelatedNewsForSolr($key,$cid);
    return $relatedNews;
}
