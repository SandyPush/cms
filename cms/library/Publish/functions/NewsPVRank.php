<?
//按时间进行排行
    require_once LIBRARY_PATH . 'Publish/models/Article.php';
    require_once MODULES_PATH . 'contents/models/Publisher.php';
    require_once LIBRARY_PATH . 'function.global.php';
    function NewsPVRank($cid,$startTime){
        
        $aidJson=JTPC('videoData.getPVRank',array('startTime'=>$startTime,'cid'=>$cid));
        if($aidJson){
            $article_table = new ArticleTable();
            $db = $article_table->getAdapter();
            $CMSEnhanced = json_decode($aidJson,true);
            $items=$CMSEnhanced['result']['data']['items'];
            $aids=implode(',',$items);
	    if($aids){
            	$newsResult = $db->fetchAll("select aid,title,stitle,url,islink,image,author,postdate,level from article where aid in (".$aids.") and status>=1 order by substring_index('".$aids."',aid,1)");
            	foreach($newsResult as $new){
                	if(!$new['islink']) $new['url']='http://www.v1.cn'.$new['url'];
               		$news[]=$new;
            	}
            	return $news;
	    }
        }
        return 0;
    }
?>
