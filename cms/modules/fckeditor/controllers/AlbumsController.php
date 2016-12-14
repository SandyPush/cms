<?php

/** @see BaseController */
require_once 'BaseController.php';


class fckeditor_AlbumsController extends BaseController
{
	private $channel;	
	private $base_path;	
	private $base_url;	

	public function init(){
		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];		
		$this->base_path= $this->getChannelConfig($this->channel)->path->images;
    	$this->base_url= $this->getChannelConfig($this->channel)->url->images; 		
	}

	public function indexAction()
	{
		//从老cms移植过来的...
		require(MODULES_PATH . 'fckeditor/models/albums.php') ;
		return true;
	}

	public function swfAction()
	{
		require(WEB_PATH . 'fckeditor/editor/plugins/albums/swfupload.html') ;		
		exit(1);
		return true;
	}

	public function swfuploadAction()
	{
		if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
			echo "ERROR:invalid upload";
			exit(0);
		}
	
		$ext=explode(".", $_FILES["Filedata"]["name"]);	
		$fileext=strtolower(end($ext));
				
		if($fileext!='jpg' && $fileext!='gif' && $fileext!='png'){	
			echo "ERROR:Illegal file types";
			exit(0);
		}
				
		$time= date('Y/m/d',time());
		$filename= substr(md5($_FILES["Filedata"]["name"]),0,10).'_'.time().'.'.$fileext;
		$realname= $filename;
		$filedir= $this->base_path.'/'.$time.'/';
		$filedir= str_replace(array("//","\\","\\\\"),'/',$filedir);
		$filename= $filedir.$filename;	
		$fileurl= $this->base_url.$time.'/'.$realname;
		makedir(dirname($filename));
		$result= UploadFile($_FILES["Filedata"]["tmp_name"], $filename)? $fileurl.'|'.str_replace('.'.$fileext,'',$_FILES["Filedata"]["name"]):'';
		echo $result;
		exit(1);
		return true;
	}
}