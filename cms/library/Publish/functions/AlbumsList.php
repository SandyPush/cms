<?php
require_once MODULES_PATH . 'albums/models/albums.php';

//���������ͼ��
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