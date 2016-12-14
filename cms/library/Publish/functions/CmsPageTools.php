<?php
//添加立即发布按钮
function CmsPageTools($pid=1) {    
    if (Publish::$current_edition != Publish::WORK_EDITION) {
        return false;
    }
    $config = Zend_Registry::get('channel_config');
    echo "<div align=\"center\"><button onclick=\"window.location.href='".$config->site->url."/page/pages/publish/pid/".$pid."'\">立刻发布</button></div><hr>";
    
    return true;
}