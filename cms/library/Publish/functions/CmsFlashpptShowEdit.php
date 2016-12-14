<?php
//显示头图编辑按钮
function CmsFlashpptShowEdit($oid = 0, $pid = 0) {    
    if (Publish::$current_edition != Publish::WORK_EDITION) {
        return false;
    }
    
    echo '<script type="text/javascript">EXTRA_MENUS.push({text: "更新头图", handle: cmsEditFlashpptModules, target: ' . Publish::$current_oid . ',params: {oid: ' . $oid .', pid: "' . $pid . '"}})</script>';
    
    return true;
}