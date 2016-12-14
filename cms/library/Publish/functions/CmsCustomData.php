<?php
require_once LIBRARY_PATH . 'Publish/models/Customlist.php';

//显示手动区列表
function CmsCustomData($oid = 0, $pid = '', $cid = 1)
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
    $db = $table->getAdapter();
    if (!$data) {
        return false;
    }
    
    $customData=unserialize($data->content);
    foreach($customData as $dataKey=>$items){
        foreach($items['items'] as $itemsKey=>$item){
            foreach($item['items'] as $key=>$info){
                $href=$info['href'];
                if($href){
                    $aid=str_replace('.shtml','',basename($href));
                    if(is_numeric($aid)){
                        $info['aid']=$aid;
                        $article=$db->fetchRow("select image,intro,pv,postdate from article where aid='".$aid."'");
                        $info['intro']=$article['intro'];
                        $info['image']=$article['image'];
                        $info['video']=$db->fetchOne("select videoLink from video where match(articleList) against('".$aid."')");
                        $info['pv']=$article['pv'] ? $article['pv'] : RAND(100,1000);
                        $info['postdate']=$article['postdate'];
                    }
                }
                $item['items'][$key]=$info;
            }
            $items['items'][$itemsKey]=$item;
        }
        $customData[$dataKey]=$items;
    }
    
    return $customData;
}