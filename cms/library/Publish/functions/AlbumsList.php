<?php
require_once MODULES_PATH . 'albums/models/albums.php';

//按分类调用图集
function AlbumsList($cid = '0', $offset = 0, $num = 10, $orderby = 'postdate', $channel='qsl')
{
	global $channel_db;

	$tbl_albums = new AlbumsTable($channel_db);
	
	$params = array (
		'cid' => $cid,
		'offset' => $offset,
		'limit' => $num,
		'orderby' => $orderby,
	);

	return $tbl_albums->ls($params, $count);

    return $news;
}
?>