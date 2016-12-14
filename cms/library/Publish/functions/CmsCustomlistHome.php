<?php
require_once LIBRARY_PATH . 'Publish/models/Customlist.php';

function CmsCustomlistHome($oid = 0, $pid = 'p1', $cid = 1)
{
    if (Publish::$current_edition == Publish::WORK_EDITION) {
        echo '<script type="text/javascript">EXTRA_MENUS.push({text: "编辑手动区列表", handle: cmsEditManualModules, target: ' . Publish::$current_oid . ',params: {oid: ' . $oid .', pid: "' . $pid . '", otype: "list", cid:"'.$cid.'"}})</script>';
    }
        
    if ($oid == 0 ||( $pid && !preg_match('`^[a-z]?\d+$`i', $pid))) {
        return false;
    }
    
    $pid = strtolower($pid);

    if (is_numeric($pid)) {
        $pid = 'p' . $pid;
    }

    $table = new CustomlistTable();
    $data = $table->fetchData($oid, $pid);
    if (!$data) {
        return false;
    }

	$topnews=preg_replace("/<li><span class=\"linez\" title=\"\"><\/span> <\/li>/","<li class=\"linez\"></li>",$data->contenthtml);    
    echo $topnews;
}