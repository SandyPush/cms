<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'resource/models/Resource.php';
require_once MODULES_PATH . 'category/models/Category.php';

class Resource_IndexController extends BaseController
{
	private $obj;
	private $categoryObj;
	private $base_path;//资源所在绝对路径
	private $base_url;//资源所在URL地址
	public function init()
	{
    	$channel_db = $this->getChannelDbAdapter();
    	$this->obj=new Resource($channel_db);
    	$this->categoryObj=new category($channel_db);
    	$this->base_path=$this->getChannelConfig()->path->images;
    	$this->base_url=$this->getChannelConfig()->url->images;
    	//echo "<br>string = " . $this->base_path . "<br>";
		//echo "<br>string = " . $this->base_url . "<br>";exit;
	}
	public function indexAction()
	{
		$this->view->layout()->disableLayout();
		$this->getResponse()->clearBody();
		$img_size=Zend_Registry::get('settings')->resource;
		$this->view->article_img_width=$img_size->article->width;
		$this->view->article_img_height=$img_size->article->height;
		$this->view->focus_img_width=$img_size->focus->width;
		$this->view->focus_img_height=$img_size->focus->height;

		$this->view->type=$this->_getParam('type');
		$this->view->name=$this->_getParam('name');
	}
	public function uploadAction()
	{
		$this->view->layout()->disableLayout();
		$this->getResponse()->clearBody();
		if(!$_FILES['filename']['size'])return;
		$cid=$this->_getParam('cid','0');
    	$this->obj->type($this->_getParam('type'));
    	$this->obj->channel($this->_user['channel']);
    	$this->obj->name($this->_getParam('name'));
    	$this->obj->create_uid($this->_user['uid']);
    	$this->obj->create_time(time());
    	$this->obj->status(1);
    	$this->obj->filetype($_FILES['filename']['type']);
    	$this->obj->filesize($_FILES['filename']['size']);
    	$pathinfo=pathinfo($_FILES['filename']['name']);
    	$this->obj->file_ext($pathinfo['extension']);
		$resource_id=$this->obj->add();

		//创建上传目录并上专文件
		$dir_file=$this->obj->getPath($resource_id);
        $dir_file=preg_replace("/(\.[jpg|jpeg|gif|png])/i",'_0x0'."$1",$dir_file);
		//echo "<br>dir_file = " . $dir_file . "<br>";

		$path=$this->base_path;
		//echo "<br>path = " . $path . "<br>";

		$path_file=$path.DIRECTORY_SEPARATOR.$dir_file;
		is_dir(dirname($path_file)) OR mkdir(dirname($path_file),0775,true);
		//echo "<br>path_file = " . $path_file . "<br>";exit;

    	move_uploaded_file($_FILES['filename']['tmp_name'],$path_file);
        $resizeArray=$this->_getParam('resize');
                
		if(is_array($resizeArray))
		{
    		$img_size=Zend_Registry::get('settings')->resource;
    		foreach($resizeArray as $resize){
                switch($resize)
        		{
                    case 'article':
        				$img_width=$img_size->article->width;
        				$img_height=$img_size->article->height;
        				break;
        			case 'focus':
        				$img_width=$img_size->focus->width;
        				$img_height=$img_size->focus->height;
        				break;
        			case 'custom':
        				$img_width=$this->_getParam('custom_width');
        				$img_height=$this->_getParam('custom_height');
        				break;
        		}
                
                $dist_path_file=str_replace("0x0",$img_width."x".$img_height,$path_file);
                Util::resizeImage($path_file,$dist_path_file,$img_width,$img_height);
            }
    	}
        
        $selectedIndex=$this->_getParam('selectedIndex');
        switch($selectedIndex)
		{
            case 'article':
				$img_width=$img_size->article->width;
				$img_height=$img_size->article->height;
                $dir_file=str_replace("0x0",$img_width."x".$img_height,$dir_file);
				break;
			case 'focus':
				$img_width=$img_size->focus->width;
				$img_height=$img_size->focus->height;
                $dir_file=str_replace("0x0",$img_width."x".$img_height,$dir_file);
				break;
			case 'custom':
				$img_width=$this->_getParam('custom_width');
				$img_height=$this->_getParam('custom_height');
                $dir_file=str_replace("0x0",$img_width."x".$img_height,$dir_file);
				break;
		}

    	$this->view->fileurl=$this->base_url.$dir_file;
    }
}