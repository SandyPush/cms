<?php

/** @see BaseController */
require_once 'BaseController.php';


class fckeditor_FckuploadController extends BaseController
{
	public function indexAction()
	{
		$base_path= $this->getChannelConfig($this->channel)->path->images;
    	$base_url= $this->getChannelConfig($this->channel)->url->images; 
		require(MODULES_PATH . 'fckeditor/models/php/config.php') ;
		require(MODULES_PATH . 'fckeditor/models/php/util.php') ;
		require(MODULES_PATH . 'fckeditor/models/php/io.php') ;
		require(MODULES_PATH . 'fckeditor/models/php/commands.php') ;
		require(MODULES_PATH . 'fckeditor/models/php/phpcompat.php') ;
		require MODULES_PATH . 'fckeditor/models/php/upload.php';
	}
}