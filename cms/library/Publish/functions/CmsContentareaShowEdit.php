<?php

//显示手动区编辑按钮
function CmsContentareaShowEdit($oid = 0, $pid = 0) {    
    if (Publish::$current_edition != Publish::WORK_EDITION) {
        return false;
    }
    
    echo '<script type="text/javascript">EXTRA_MENUS.push({text: "编辑手动区", handle: cmsEditManualModules, target: ' . Publish::$current_oid . ',params: {oid: ' . $oid .', pid: "' . $pid . '", otype: "manual"}})</script>';
    
    return true;
}