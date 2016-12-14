<?php
        function mkdir_r($dir, $mode = 0755){
		  return is_dir($dir) || ( mkdir_r(dirname($dir), $mode) && @mkdir($dir, $mode) );
	    }
        
        function postdata($url,$data){
            $context = array();   
            if (is_array($data))   
            {   
                ksort($data);   
                $context['http'] = array   
                (   
                    'method' => 'POST',   
                    'content' => str_replace('&amp;','&',http_build_query($data)), 
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                );   
            }
            return @file_get_contents($url, false, stream_context_create($context));
        }
   
        /**
		* 从api.busap.cn的basicInformation中获取城市信息
		* @param obj JTPC obj格式参数，例如：basicInformation.city
		* @param parameter 数组参数，例如:array('name'=>'handong');
		* JTPC('basicInformation.city',array("name"=>"handong"));
		*/
		function JTPC($obj,$parameter){
			$config = Zend_Registry::get('channel_config');
            $JTPCInterface=$config->url->CMSEnhanced;
			if($JTPCInterface){
                $url=$JTPCInterface.'interface';
    			$parameter['obj']=$obj;
    			$result=postdata($url,$parameter);
            }else{
                $result=false;
            }		
			return $result;
		}
?>