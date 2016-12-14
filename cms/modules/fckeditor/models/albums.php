<?php
// --------------------------------------------------------------------------
// Files name   : albums.php
// Description : 后台组图发布功能
// Requirement : PHP4/PHP5 ([url]http://www.php.net[/url])
// Copyright(C), titanCMS, 2009, All Rights Reserved.
// Author: yxw ([email]yexinwei@titan24.com[/email]) 
// --------------------------------------------------------------------------

header("content-type:text/html;charset=utf8");
define('BASE_PATH', "/www/img/news");
define('BASE_URL', "http://img.qsl.cn/news");
define('TITANPIC_URL','http://img2008.titan24.com/imgwater/');

$data= getPost();
$html= creatHtml($data);
$html= stripslashes($html);
include('fckeditor/editor/plugins/albums/message.html');
exit();

function creatHtml($data){
	if(!$data)return;
	$html= "";
	$count= count($data);
	$j=0;
	foreach($data as $key=> $var){		
		if(!$var['image']){
			$count--;
			continue;			
		}
		$j++;
		$id= rand(100000,999999);
		//$html.="<p class=\"img\"><img alt=\"$var[alt]\" align=\"middle\" src=\"$var[image]\" /><span>$var[alt]</span></p>";
		if($count >1){
			$html.="<p class=\"img\" flag=\"1\"><a href=\"$$$$$$\"><img id=\"$id\" class=\"albums\" title=\"下一页\" alt=\"$var[alt]\" src=\"$var[image]\" align=\"middle\" /></a><span>$var[alt]　<a href=\"$$$$$$\">(点击图片到下一页)</a></span></p>";
		}else{
			$html.="<p class=\"img\" flag=\"1\"><a href=\"$$$$$$\"><img id=\"$id\" class=\"albums\" title=\"\" alt=\"$var[alt]\" src=\"$var[image]\" align=\"middle\" /></a><span>$var[alt]　<a href=\"$$$$$$\"> </a></span></p>";
		}
		$html.="<p>";
		$html.=	$var['info'];	
		$html.="</p>";
		if($j< $count){
			$html.=	"<p><hr style=\"page-break-after: always\" /></p>";
			//$html.=	"<div style=\"page-break-after: always\"><span style=\"display: none\">&nbsp;</span></div>";
		}		
	}
	$html= preg_replace( "/(<p><hr style=\"page-break-after: always\" \/><\/p>)$/i",'',$html);
	return $html;
}

function getPost(){
	$data= array();
	foreach($_FILES['upfile']['name'] as $order=>$var){		
		if($var){
			$data[$order]['image']= upload($_FILES['upfile']['name'][$order],$_FILES['upfile']['tmp_name'][$order],$_FILES['upfile']['size'][$order]);
		}elseif(!$data[$order]['image'] && $_POST['fileurl'][$order]){
			$data[$order]['image']= $_POST['fileurl'][$order];
		}elseif($_POST['titanpic'][$order]){
			$data[$order]['image']= getTitanPic($_POST['titanpic'][$order]);
		}
		$data[$order]['alt']= $_POST['alt'][$order];
		$data[$order]['info']= $_POST['info'][$order];
		if(!$data[$order]['image'])unset($data[$order]);
	}
	return $data;
}

function getTitanPic($id){
	if(!$id)return;
	$dir= (floor($id/1000))*1000;		
	return TITANPIC_URL.$dir.'/'.$id.'.jpg'; 
}

function upload($name,$tmpname,$size){
	if(!$name or !$tmpname or !$size)return;
	$ext= explode(".", $name);	
	$fileext= strtolower(end($ext));
			
	if($fileext!='jpg' && $fileext!='gif' && $fileext!='png'){	
		return false;
	}
			
	$time= date('Y/m/d',time());
	$filename= substr(md5($name),0,10).'_'.time().'.'.$fileext;
	$realname= $filename;
	$filedir= BASE_PATH.'/'.$time.'/';
	$filedir= str_replace(array("//","\\","\\\\"),'/',$filedir);
	$filename= $filedir.$filename;	
	$fileurl= BASE_URL.'/'.$time.'/'.$realname;
	createdir(dirname($filename));
	$image= upfile($tmpname, $filename)?$fileurl:'';
	return $image;
}


function upfile($tmp_name, $filename) {     
	if(strpos($filename,'..') !== false || eregi("\.php$", $filename)) {
		return false;
	}    
	if(function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name, $filename)) {
		 @chmod($filename, 0777);
		return true;
	}elseif(@copy($tmp_name, $filename)) {
		@chmod($filename, 0777);
		return true;
	}elseif(is_readable($tmp_name)) {
		file_put_contents($filename, file_get_contents($tmp_name));
		if(file_exists($filename)){
			@chmod($filename, 0777);
			return true;
		}
	}
	return false;
}

function createdir($dir, $mode = 0755) {
	if (is_dir($dir) || @mkdir($dir, $mode)) return true;
	if (!createdir(dirname($dir), $mode)) return true;
	return @mkdir($dir, $mode);
}
?>