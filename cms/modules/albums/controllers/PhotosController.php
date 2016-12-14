<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'albums/models/albums.php';
require_once MODULES_PATH . 'albums/models/photos.php';
//require_once LIBRARY_PATH . 'Publish/Album.php';

class Albums_PhotosController extends BaseController
{
    protected $_db;
	protected $_channel_db;
	protected $_tbl_templates;
	protected $_tbl_albums;
	protected $_tbl_photos;

    public function init()
    {
	    $channel_db = $this->getChannelDbAdapter();
	    $this->_channel_db = $channel_db;
	    $this->_db = $channel_db;
        
        Zend_Db_Table::setDefaultAdapter($channel_db);
        
        $this->_tbl_albums = new AlbumsTable($channel_db);
		$this->_tbl_photos = new PhotosTable($channel_db);
    }
    
    public function indexAction()
    {
		$aid = (int) $this->_getParam('aid', 0);
		if (!$aid || !$album = $this->_tbl_albums->find($aid)->current()) {
			$this->error('无效的图集id', true);
		}
		
		if ($this->_request->isPost()) {
			$title = $this->_getParam('title');
			$intro = $this->_getParam('intro');
            $sequence = $this->_getParam('sequence');
			$delete = $this->_getParam('delete');
			$cover = $this->_getParam('cover');
			
			foreach ($title as $id => $t) {
				$p = array(
                    'title' => $t,
					'intro' => $intro[$id],
                    'sequence' => $sequence[$id],
				);
				
				if (!empty($delete[$id])) {
                    $p['aid'] = $aid;
					$p['status'] = 0;
				}

				$this->_tbl_photos->edit($p, 'pid=' . $id);
			}

			if ($cover) {
				$this->_db->query('UPDATE albums SET cover = ? WHERE aid = ?', array($cover, $aid));
			}

			$this->flash('修改成功', '/albums/photos?aid=' . $aid, 2);
		}

		$photos = $this->_tbl_photos->fetch(array('aid' => $aid), $count);

		$this->view->album = $this->_tbl_albums->find($aid)->current()->toArray();
		$this->view->photos = $photos;
    }

	public function uploadAction()
	{
		$aid = (int) $this->_getParam('aid', 0);
		if (!$aid || !$albums = $this->_tbl_albums->find($aid)) {
			$this->error('无效的图集id', true);
		}
		$album = current($albums->toArray());
		$this->view->album = $album;
	}

	public function douploadAction()
	{
		if (!$this->_request->isPost()) {
			$this->error('方法不支持', true);
		}

		$aid = $this->_getParam('aid', 0);
		if (!$aid || !$album = $this->_tbl_albums->find($aid)) {
			$this->error('无效的图集', true);
		}

		$photos = array();
		$partial = false;
		$errors = array();
		var_dump($_FILES['photos']);
		$total = count($_FILES['photos']['name']);
		$fields_input = $this->_request->getParam('photos');
		
		$fields = $_FILES['photos'];
		for ($i = 0; $i < $total; $i ++) {
			if ($fields['error'][$i] == 4) {
				if (!$errors) {
					$errors[] = '没有选择图片文件';
				}
				continue;
			}
			
			$name = basename($fields['name'][$i]);
			$ext = strtolower(substr(strrchr($fields['name'][$i], '.'), 1));
			if ($fields['error'][$i] != 0) {
				$errors[] = sprintf('%s上传失败，原因未知', $name);
				continue;
			}                
			
			$size = $fields['size'][$i];
			$type = $fields['type'][$i];			
							
			// size
			if ($size > 20 * 1024 * 1024) {
				$errors[] = sprintf('%s 超过规定大小20MB', $name);
				continue;
			}

			// mime type
			/*if (!preg_match('/^image\/(pjpeg|jpeg|jpg|gif|png)$/i', $type)) {
				$errors[] = sprintf('%s 不是有效的图片文件', $name);
				continue;
			}*/
			
			$photos[] = array (
				'name' => $name,
				'type' => $type,
				'size' => $size,
				'tmp_name' => $fields['tmp_name'][$i],
			);
		}

		if (!$photos) {
			$this->error('上传失败, 可能的原因: ' . join(', ', $errors) . '.', 1);
		}
		
		if ($photos) {
			foreach ($photos as $p) {
				$p['aid'] = $aid;
				$p['title'] = '';
				$p['intro'] = '';

				$this->_tbl_photos->create($p);
			}
		}

		$this->_tbl_albums->setCover($aid);
	}
}