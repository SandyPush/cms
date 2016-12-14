<?php
require_once 'Abstract.php';
require_once MODULES_PATH . 'albums/models/albums.php';
require_once MODULES_PATH . 'albums/models/photos.php';
require_once MODULES_PATH . 'category/models/Category.php';

class Publish_Album extends Publish
{
    protected $_albums_table;
	protected $_photos_table;
    protected $_categoryObj;
    protected $_album;
    
    public function __construct($config, $aid)
    {
        $this->_type = self::OBJ_TYPE_ALBUM;        
        // ...
        parent::__construct($config, $pageid);
                
        $this->_albums_table = new AlbumsTable($this->_db);
		$this->_photos_table = new PhotosTable($this->_db);
        $this->_categoryObj=new Category($this->_db);
        
        $album = $this->_albums_table->find($aid)->current();
        $this->_album = $album;
        $this->_pageid = $album->aid;
        $this->_template_id = $album->template;        
    }
    
    public function publish()
    {
        $album = $this->_album;
        
        $this->_file_path = $this->_albums_table->getUrl($album->createdate, $album->aid);
        
        $this->set('cms_page_id', $this->_pageid);
        $this->set('cms_page_title', $album->title);
        $this->set('cms_page_keywords', implode(" ",$this->_albums_table->getTags($album->aid)));
        $this->set('cms_page_description', $album->intro);
        $this->_categoryObj->init($album->cid);
        $this->set('cms_page_category', $this->_categoryObj->getName());
		$this->set('album', $album);
		$this->set('photos', $this->_photos_table->getList($album->aid));
        
		$this->_albums_table->update(array('pubdate' => time()), 'aid=' . $album->aid);

        return parent::_publish(self::OBJ_TYPE_ALBUM);
    }
}
