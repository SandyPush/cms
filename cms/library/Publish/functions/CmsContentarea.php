<?php
require_once LIBRARY_PATH . 'Publish/models/Contentarea.php';

//显示手动区
function CmsContentarea($oid = 0, $pid = NULL)
{
    if ($oid == 0 || (isset($pid) && !preg_match('/^[a-z]?\d+$/i', $pid))) {
        return false;
    }
    
    $pid = strtolower($pid);

    if (is_numeric($pid)) {
        $pid = 'p' . $pid;
    }

    $table = new ContentareaTable();
    $data = $pid? $table->fetchData($oid, $pid): $table->fetchData($oid);
    
    return $data;
}