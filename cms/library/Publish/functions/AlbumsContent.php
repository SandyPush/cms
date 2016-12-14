<?php
require_once MODULES_PATH . 'albums/models/albums.php';
require_once MODULES_PATH . 'albums/models/photos.php';

//调用图集内容
function AlbumsContent($aid = '0', $channel='qsl')
{
    global $channel_db;
	$tbl_albums = new AlbumsTable($channel_db);
    $tbl_photos = new PhotosTable($channel_db);
    
    $album = $tbl_albums->find($aid)->current();
    $albumInfo['aid']=$album->aid;
    $albumInfo['title']=$album->title;
    $albumInfo['stitle']=$album->stitle;
    $albumInfo['source']=$album->source;
    $albumInfo['author']=$album->author;
    $albumInfo['createdate']=$album->createdate;
    $albumInfo['photos']=$album->photos;
    
    
    $result['base']=$albumInfo;
    $result['photo']=$tbl_photos->getList($album->aid);
    
    return $result;
}
?>