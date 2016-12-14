<?php

function NewsTagList($cid = '', $num = 10, $tags = '', $channel='soccer', $level = '',$orderby = 'postdate')
{
    if ($tags === '') {
        return false;
    }
	//$included_files= get_included_files();foreach($included_files as $filename)echo "$filename\n";
	$env_config = new Zend_Config_Ini(CONFIG_FILE);	
	$db = Zend_Db::factory('PDO_MYSQL', $env_config->db);	
	$db->query('set names utf8');
	$channelDb= 'cms_'.$channel;	

    $cids = trim($cid, ', ');
    $sql_cids = !$cid ? '' : " AND a.aid IN (SELECT DISTINCT aid FROM $channelDb.cate_links WHERE cid IN($cids))";
    
    $tags = trim($tags, ', ');
    $tags = explode(',', $tags);

    foreach($tags as $k => &$v)$v= $db->quote(trim($v));
  
    $levelAdd = empty($level)? "": "AND a.level IN ($level)";
	
    $sql = "SELECT DISTINCT a.aid, title, stitle, intro, url,islink, image, author,postdate FROM $channelDb.article a, $channelDb.article_tag at, $channelDb.tags t
        WHERE t.name IN (" . join(',', $tags) . ") AND at.tagid = t.tagid AND a.aid = at.aid  AND a.status>=1 $levelAdd $sql_cids ORDER BY $orderby DESC LIMIT $num";
    $news = $db->fetchAll($sql);
        foreach($news as $k=>&$v)$v['url']= (!$v['islink'])? "http://".$channel.".titan24.com".$v['url']:$v['url'];
    
    return $news;
}