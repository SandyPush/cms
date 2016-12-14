<?php
//网页css合并函数

function cssBox($cssName,$cssArray)
{
    $config = Zend_Registry::get('channel_config');
    $cssPath=$config->path->published."css/";
    $cssShowPath=$config->url->published."css/";
    @mkdir($cssPath, 0755, true);
    $cssFile=$cssPath.$cssName;
    $cssShowFile=$cssShowPath.$cssName;
    $cssContent='';
    
    foreach($cssArray as $cssPath){
        $cssPath=strstr($cssPath,"?") ? $cssPath.'&' : $cssPath.'?';
        $cssPath=$cssPath.'v='.time();
        $thisCss=file_get_contents($cssPath);
        $thisCss=preg_replace("/url\(\"(.*?)\"\)/i",'url($1)',$thisCss);
        $thisCss=preg_replace("/url\(([^http].*?\.(png|jpg|gif|jpeg))\)/i",'url("'.dirname($cssPath).'/$1")',$thisCss);
        $thisCss=preg_replace("/url\((http.*?\.(png|jpg|gif|jpeg))\)/i",'url("$1")',$thisCss);
        $cssContent.="\r\n\r\n".$thisCss;
    }

    file_put_contents($cssFile,$cssContent);
    echo '<link type="text/css" rel="stylesheet" href="'.$cssShowFile.'?v='.hash_file('md5',$cssFile).'" />';
}