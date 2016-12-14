<?php
require_once 'Abstract.php';
require_once MODULES_PATH . 'page/models/Page.php';

class Publish_Page extends Publish
{
    protected $_page_table;
    protected $_page;
    
    public function __construct($config, $pageid)
    {
        $this->_type = self::OBJ_TYPE_PAGE;        
        // ...
        parent::__construct($config, $pageid);
     
        $this->_page_table = new PageTable($this->_db);
        $page = $this->_page_table->find($pageid)->current();
        $this->_page = $page;
        $this->_pageid = $page->pid;
        $this->_template_id = $page->template;        
    }
    
    public function publish()
    {
        $page = $this->_page;


        $this->_file_path = $page->url;

        $this->set('cms_page_id', $this->_pageid);
        $this->set('cms_page_title', $page->title);
        $this->set('cms_page_keywords', $page->keywords);
        $this->set('cms_page_description', $page->description);
		$this->set('cms_page_name', $page->name);

        return parent::_publish(self::OBJ_TYPE_PAGE);
    }
    
    public function publishWork(){
        $page = $this->_page;

        $this->_file_path = $page->url;
        
        $this->set('cms_page_id', $this->_pageid);
        $this->set('cms_page_title', $page->title);
        $this->set('cms_page_keywords', $page->keywords);
        $this->set('cms_page_description', $page->description);
		$this->set('cms_page_name', $page->name);

        return parent::_publishWork(self::OBJ_TYPE_PAGE);
    }    
}
