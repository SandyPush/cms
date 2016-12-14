<?php
require_once 'Abstract.php';
require_once MODULES_PATH . 'contents/models/Article.php';
require_once MODULES_PATH . 'contents/models/Publisher.php';
require_once MODULES_PATH . 'system/models/Users.php';

class Publish_Article extends Publish
{
    protected $_article;
    protected $_db;
	protected $_channel;
    
    public function __construct($config='', $pageid=0)
    {

            $this->_type = self::OBJ_TYPE_ARTICLE;
            parent::__construct($config, $pageid);
                    
            $db = Zend_Db::factory('PDO_MYSQL', $config->db);
            $db->query("SET NAMES 'utf8'");
            $this->_db= $db;
    		$this->_channel= str_replace('cms_','', $config->db->dbname);
        if($pageid){
        	$this->_article= new Article($db, $pageid);
            $this->_pageid = $pageid;
            $this->_template_id = $this->_article->getTemplate();
        }
    }
    
    public function publish()
    {
        $this->_file_path = $this->_article->getAutoUrl();			
        $this->set('aid', $this->_article->getId());
        $this->set('source', Publisher::getHtmlName($this->_db,$this->_article->getPublisher()));
        $this->set('keywords', implode(",",explode(" ",$this->_article->getTags())));
        $this->set('title', $this->_article->getTitle());
        $this->set('stitle', $this->_article->getShortTitle());
        $this->set('intro', $this->_article->getIntro());
		$this->set('cid', $this->_article->getCid());
		$this->set('url', $this->_article->getAutoUrl());
        $this->set('videoUrl',$this->_article->getVideoUrl($this->_article->getId()));
        $this->set('author', $this->_article->getAuthor());
		$this->set('realname', $this->_article->getRealname($this->_article->getUid()));
        $this->set('postdate', $this->_article->getPostDate());
        $this->set('contents', $this->_article->getContents());
		$this->set('image', $this->_article->getImage());
		$this->set('insert_time', $this->_article->getInsertTime());
		$this->set('is_ad', $this->_article->getIs_ad());
		$this->set('is_titan', $this->_article->getIs_titan());
		$this->set('channel', $this->_channel);	
		$this->set('album', $album= $this->_article->getArticleAlbumIdData($this->_article->getAlbumid()));		
	    $config = new Zend_Config_Ini(CONFIG_FILE);
		$db     = Zend_Db::factory('PDO_MYSQL', $config->db->toArray());			
		$this->set('weibo', UsersTable::getWeibo($db, $this->_article->getUid()));		
		$this->set('nickname', UsersTable::getNickname($db, $this->_article->getUid()));	
        //$this->set('related_news', $article_contents->related_news);			
		return parent::_publish(self::OBJ_TYPE_ARTICLE);

    }
    
    public function publishHTML($templateID,$dataArray)
    {
        $this->_template_id = $templateID;
        foreach($dataArray as $key=>$value){
            $this->set($key, $value);
        }

        $template = $this->getTemplate();
        $this->_template = $template->content;
        if (!$this->_template) {
            // TODO: error
			echo 'template error!';
            return false;
        }
        $public_edition = $this->renderTemplate($this->_template,  self::PUBLIC_EDITION);
		return $public_edition;
    }
}    
