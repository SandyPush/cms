<?php
//网页js合并函数

function jsBox($jsName,$jsArray)
{
    $config = Zend_Registry::get('channel_config');
    $jsPath=$config->path->published."js/";
    $jsShowPath=$config->url->published."js/";
    @mkdir($jsPath, 0755, true);
    $jsFile=$jsPath.$jsName;
    $jsShowFile=$jsShowPath.$jsName;
    $jsContent='';
    foreach($jsArray as $jsPath){
        $jsPath=strstr($jsPath,"?") ? $jsPath.'&' : $jsPath.'?';
        $jsPath=$jsPath.'v='.time();
        $jsContent.=file_get_contents($jsPath).PHP_EOL;
    }
    
    file_put_contents($jsFile,$jsContent);
    echo '<script type="text/javascript" src="'.$jsShowFile.'?v='.hash_file('md5',$jsFile).'"></script>';
}