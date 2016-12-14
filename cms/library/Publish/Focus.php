<?php
require_once 'Abstract.php';
require_once MODULES_PATH . 'focus/models/Focus.php';
require_once MODULES_PATH . 'category/models/Category.php';

class Publish_Focus extends Publish
{
    protected $_focus_table;
    protected $_focus;
    protected $_categoryObj;
    
    public function __construct($config, $pageid)
    {
        $this->_type = self::OBJ_TYPE_FOCUS;        
        // ...
        parent::__construct($config, $pageid);
                
        $this->_focus_table = new FocusTable();
        $this->_categoryObj=new Category($this->_db);
        
        $focus = $this->_focus_table->find($pageid)->current();
        $this->_focus = $focus;
        $this->_pageid = $focus->fid;
        $this->_template_id = $focus->template;
        $this->_config = $config;        
    }
    
    public function publish()
    {
        $focus = $this->_focus;
        
        $this->_file_path = $focus->url;
        
        $this->set('cms_page_id', $this->_pageid);
        $this->set('cms_page_title', $focus->title);
        $this->set('cms_page_keywords', $focus->keywords);
        $this->set('cms_page_description', $focus->description);
		$this->set('cms_page_name', $focus->name);
        $this->_categoryObj->init($focus->cid);
        $this->set('cms_page_category', $this->_categoryObj->getName());
        $this->set('focus_url',$focus->url);       
        $this->set('focus_image',$focus->image);
        return parent::_publish(self::OBJ_TYPE_FOCUS);
    }
    
    public function publishWork()
    {
        $focus = $this->_focus;
        
        $this->_file_path = $focus->url;
        
        $this->set('cms_page_id', $this->_pageid);
        $this->set('cms_page_title', $focus->title);
        $this->set('cms_page_keywords', $focus->keywords);
        $this->set('cms_page_description', $focus->description);
		$this->set('cms_page_name', $focus->name);
        $this->_categoryObj->init($focus->cid);
        $this->set('cms_page_category', $this->_categoryObj->getName());
        $this->set('focus_url',$focus->url);
        $this->set('focus_image',$focus->image);
        return parent::_publishWork(self::OBJ_TYPE_FOCUS);
    }    
}
