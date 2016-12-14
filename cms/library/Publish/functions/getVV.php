<?
    require_once LIBRARY_PATH . 'function.global.php';
    function getVV($aid){
        $videoData=JTPC('videoData.getVideoData',array('aid'=>$aid));
        if($videoData){
            $CMSEnhanced = json_decode($videoData,true);
            $items=$CMSEnhanced['result']['data']['items'];
            if($items){
                $views=$CMSEnhanced['result']['data']['items'][$aid]['vvTotal'];
                return $views;
            }
        }
        return 0;
    }
?>