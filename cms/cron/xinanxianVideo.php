<?
// --------------------------------------------------------------------------
// File name   : xinanxianVideo.php
// Description : 将新岸线提供的视频导入到CMS
// Requirement : PHP4/PHP5 ([url]http://www.php.net[/url])
// Copyright(C), handong, 2014, All Rights Reserved.
// Author: handong ([email]handong@v1.cn[/email]) 
// 例子 php xinanxianVideo.php
// --------------------------------------------------------------------------
$channel='vodone';   //频道
$uid='1157';        //用户ID
$mysqlHost='cms.db.v1cn';
$mysqlUser='cms';
$mysqlPassword='cmsv1cn';
$uploadDir="/VODONE/transcode/videoSourceFTP/xinanxian/";  //上传视频地址
$transcodeDir="/VODONE/transcode/phpcms/ftp/";           //转码目录地址


$db = mysql_connect($mysqlHost, $mysqlUser,$mysqlPassword)or die("Could not connect: " . mysql_error());
mysql_select_db('cms_'.$channel, $db);
mysql_query("set names utf8");

$allFile=scandir($uploadDir);
foreach($allFile as $file){
    $fileInfo=pathinfo($file);
    if($fileInfo['extension']=='xml'){
        $file=$uploadDir.$file;
        
        $xml=simplexml_load_file($file);
        $cid=(string)$xml->categoryId;
        $title=(string)$xml->title;
        $source=(string)$xml->channelCName;
        $keyword=(string)$xml->keywords;
        $video=(string)$xml->AssetFiles->file->filePath;
	$columnName=(string)$xml->coloumnName;
        if(!$video) continue;
	$video=$uploadDir.$video;
        if(preg_match("/\s*(nba|中国好歌曲)\s*/i",$title)){
                unlink($video);
                unlink($file);
                continue;
        }
        $md5FileCode=md5($video);
        $p = array(
			   'title' => $title,
               'source' => $source,
               'keyword' => $keyword,
               'filename' => basename($video),
               'cid' => $cid,
               'md5'=>$md5FileCode,
               'uid' => $uid
		    );
        
        $query = mysql_query("select * from video where md5='".$md5FileCode."'");
        $vidoInfo=mysql_fetch_assoc($query);
        $vid=$vidoInfo["vid"];
        if($vid){
            mysql_query("update video set title='".$title."',`source`='".$source."',keyword='".$keyword."',filename='".basename($video)."',cid='".$cid."',md5='".$md5FileCode."',uid='".$uid."',columnName='".$columnName."',status='1' where vid='".$vid."'");
        }else{
            mysql_query("insert into video (title,`source`,keyword,filename,cid,md5,uid,columnName,status) values ('".$title."','".$source."','".$keyword."','".basename($video)."','".$cid."','".$md5FileCode."','".$uid."','".$columnName."','1')");
            $vid = mysql_insert_id();
        }

        if(PHP_OS=='WINNT'){
            if(!rename($video,$transcodeDir.$vid."_".basename($video))){
                exit('文件移动失败，请检查权限');
            }
        }else{
            $cmd="mv ".$video." ".$transcodeDir.$vid."_".basename($video);
            exec($cmd . " > /dev/null &");
        }
        unlink($file);
	//exit('一条结束');
    }
}
?>
