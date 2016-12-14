<?php
/**
 * common lightweight utilities
 */

class Util
{
    /**
     *  取得cookie
     *
     * @param string    $key    名称
     * @return boolean
     */
    static public function getCookie($key)
    {
        return isset($_COOKIE[COOKIE_PREFIX . $key]) ? $_COOKIE[COOKIE_PREFIX . $key] : '';
    }
    
    /**
     * 设置cookie
     *
     * @param string    $key    名称
     * @param mixed     $value  值，设置为false则注销此cookie
     * @param integer   $expire 过期时间，默认为0，即浏览器进程
     * @return boolean
     */
    static public function setCookie($key, $value, $expire = 0)
    {
        return setcookie(COOKIE_PREFIX . $key, $value, $expire, '/', false);
    }
    
    /**
     * 是否为合法的email地址
     *
     * @param string $email
     */
    static public function isValidEmail($email)
    {
    	// not implemented
    	return true;
    }
    
    static public function getDbAdapter($params, $name = 'main') {
        static $links;
        
        if (isset($links[$name])) {
            return $links[$name];
        }
        
        $db = Zend_Db::factory('PDO_MYSQL', $params);
        $db->query("SET NAMES 'utf8'");
        
        $links[$name] = $db;
        
        return $db;
    }
    
    static public function concatUrl()
    {
        $args = func_get_args();
        
        for ($i = func_num_args() - 1; $i >= 0; $i --) {
            if (preg_match('`^https?://`i', $args[$i])) {
                $args = array_slice($args, $i);
            }
        }
        
        $url = join('/', $args);
        
        if (!preg_match('`^https?://`i', $url) && $url{0} !== '/') {
            $url = 'http://' . $url;
        }
        
        $url = str_replace('\\', '/', $url);
        $url = preg_replace('`(?<!:)/{1,}`', '/', $url);
        
        return $url;
    }
    
    /**
     * 检查url中是否包含禁止使用的关键字
     *
     * @param string $url
     */
    static public function checkUrl($url)
    {
        if (preg_match('`/styles|scripts|images|img/`i', $url)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Enter description here...
     *
     * @param unknown_type $data
     * @param unknown_type $iteration
     * @return unknown
     */
    static public function cleanGlobals(&$data, $iteration = 0)
    {
        if( $iteration >= 10 ) {
            return $data;
        }
        
        if(count($data)) {
            foreach($data as $k => $v) {
                if (is_array($v)) {
                    self::cleanGlobals($data[$k], $iteration ++);
                } else {
                    if (get_magic_quotes_gpc()) {
                        $v = stripslashes($v);
                    }

                    $v = preg_replace('/\\\0/' , '', $v);
                    $v = preg_replace('/\\x00/', '', $v);
                    $v = str_replace('%00', '', $v);
                    
                    //$v = str_replace('../', '&#46;&#46;/', $v);
                    
                    $data[$k] = $v;
                }
            }
        }
    }
    

	public static function getPhotoPath($id, $type = 'o', $prefix = '') {
		// file name
		$suffix = '_' . $type;
		if ($type == 'o') {
			$suffix = '_' . substr(md5($id . 'OuTmAn'), 8, 16);
		}

		$filename = $id . $suffix . '.jpg';

		// path
		$segments = array();
		$base = pow(100, 4);
		
		while ($base > 0) {			
			if ($id < $base) {
				$segments[] = 0;				
			} else {
				$segment = floor($id / $base);
				$segments[] = $segment;
				$id = $id - $segment * $base;
			}

			$base = floor($base / 100);
		}
		
	    $path = $prefix . join('/', $segments) . '/';

		return $path . $filename;
	}
    
    public static function getVideoPath($id, $type = 'o', $prefix = '',$extName="flv") {
		// file name
		$suffix = '_' . $type;
		if ($type == 'o') {
			$suffix = '_' . substr(md5($id . 'OuTmAn'), 8, 16);
		}

		$filename = $id . $suffix . '.' . $extName;

		// path
		$segments = array();
		$base = pow(100, 4);
		
		while ($base > 1) {			
			if ($id < $base) {
				$segments[] = 0;				
			} else {
				$segment = floor($id / $base);
				$segments[] = $segment;
				$id = $id - $segment * $base;
			}

			$base = floor($base / 100);
		}
		
	    $path = $prefix . join('/', $segments) . '/';

		return $path . $filename;
	}

	//

    /**
     * resize image or fit cancas
     *
     * @author legend<legendsky@hotmail.com>
     *
     * @param string/resource $src
     * @param string $dst output file 
     * @param string $mode resize or fit
     * 
     * @return boolean
     */
    static public function resizeImage($src, $dst, $dst_w, $dst_h, $mode = 'fit', $quality = 100)
    {
        if (is_resource($src))
        {
            $im_src = & $src;
        }
        else
        {
			//不要通过后缀来判断类型,modified by yxw
			$ext= getimagesize($src);
			switch ($ext[2])
            {
                case '2' :
                    $im_src = imagecreatefromjpeg($src);
                    break;
                case '3' :
                    $im_src = imagecreatefrompng($src);
                    break;
                case '1' :
                    $im_src = imagecreatefromgif($src);
                    break;
                default :
                    return false;
            }
			/*
            $ext = strtolower(substr(strrchr($src, '.'), 1));
            switch ($ext)
            {
                case 'jpg' :
                    $im_src = imagecreatefromjpeg($src);
                    break;
                case 'png' :
                    $im_src = imagecreatefrompng($src);
                    break;
                case 'gif' :
                    $im_src = imagecreatefromgif($src);
                    break;
                default :
                    return false;
            }
			*/
        }
        
        $src_w  = imagesx($im_src);
        $src_h  = imagesy($im_src);

        //if ($mode == 'resize' && ($src_w > $dst_w || $src_h > $dst_h))
        if ($mode == 'resize')
        {
            if ($src_w / $src_h == $dst_w / $dst_h)
            {
                $width  = $dst_w;
                $height = $dst_h;
            }
            else if ($src_w / $src_h > $dst_w / $dst_h)
            {
                $width  = $dst_w;
                $height = round($src_h * ($width / $src_w));
            }
            else
            {
                $height = $dst_h;
                $width  = round($src_w * ($height / $src_h));
            }

            $im_new = imagecreatetruecolor($width, $height);
            imagealphablending($im_new, true);
            ImageCopyResampled($im_new, $im_src, 0, 0, 0, 0, $width, $height, $src_w, $src_h);
        }
        // fit
        else
        {
            if ($src_w / $src_h == $dst_w / $dst_h)
            {
                $width  = $dst_w;
                $height = $dst_h;

                $x = $y = 0;
            }
            else if ($src_w / $src_h > $dst_w / $dst_h)
            {
                $height  = $dst_h;
                $width  = round(($dst_h / $src_h) * $src_w);

                $x = round(($width - $dst_w) / 2);
                $y = 0;
            }
            else
            {
                $width = $dst_w;
                $height  = round(($dst_w / $src_w) * $src_h);

                $x = 0;
                $y = round(($height - $dst_h) / 2);
            }

            $im_new = imagecreatetruecolor($dst_w, $dst_h);         
            $im_tmp = imagecreatetruecolor($width, $height);
            
            imagealphablending($im_new, true);
            imagealphablending($im_tmp, true);

            ImageCopyResampled($im_tmp, $im_src, -$x, -$y, 0, 0, $width, $height, $src_w, $src_h);
            ImageCopyResampled($im_new, $im_tmp, 0, 0, 0, 0, $width, $height, $width, $height);

            imagedestroy($im_tmp);
        }

        // calculate done
        imagejpeg($im_new, $dst, $quality);
        imagedestroy($im_new);

        if (!is_resource($src))
        {
            imagedestroy($im_src);
        }

        return true;
    }
    
    //递归查询指定目录下的文件
    static public function fileList($path){
        $backFileList=array();
        $files=array();
        if(substr($path,-1)!='/') $path.="/";
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $filePath=$path.$file;
                    if(is_dir($filePath)){
                        $backFileList=Util::fileList($filePath);
                    }else{
                        $files[]=$filePath;
                    }
                }
            }
            closedir($handle);
            $result=array_merge($backFileList,$files);
            return $result;
        }
    }
        
    /**
     * 创建分页条
     *
     * @param integer $total 总记录数
     * @param integer $perpage 每页显示记录数
     * @param integer $page 当前页码
     * @param string $url 链接,其中的__page__将用页码替换
     *
     * @return string
     */
    static public function buildPagebar($total, $perpage, $page, $url)
    {
        $pages = ceil($total / $perpage);
        $page = max($page, 1);                   
        $total = max($total, 1);        
    
        $html = '<div class="fpage">';
    
        if ($pages <= 11) {
            $start = 1;
            $end   = $pages;
        }
        else if ($page > 6 && $page + 5 <= $pages) {
            $start = $page - 5;
            $end   = $page + 5;
        }
        else if ($page + 5 > $pages) {
            $start = $pages - 10;
            $end   = $pages;            
        }
        else if ($page <= 6) {
            $start = 1;
            $end   = 11;    
        }
        
        // 
        if ($page == 1) {
            $html .= "<a>上一页</a> ";   
        }
        else {
            $html .= "<a href=\"" . str_replace("__page__", $page - 1, $url) . "\">上一页</a> ";
        }
    
        if ($start > 1) {
            $html .= "<a href=\"" . str_replace("__page__", 1, $url) . "\">1</a> ";
        }
    
        if ($start > 2) {
            $html .= "<a href=\"" . str_replace("__page__", 2, $url) . "\">2</a> ";
        }
    
        if ($start > 3) {
            $html .= "<a>...</a> ";
        }
        //
    
        for ($i = $start; $i <= $end; $i ++) {
            if ($page == $i) {
                $html .= "<a href=\"" . str_replace("__page__", $i, $url) . "\" class=\"current\">$i</a> ";
            }
            else {
                $html .= "<a href=\"" . str_replace("__page__", $i, $url) . "\">$i</a> ";
            }
        }
        
        if ($end < $pages - 1) {
            $html .= "<a>...</a> ";
        }
        
        /*
        if ($end < $pages - 1)
        {
            $html .= "<a href=\"" . str_replace("__page__", $pages - 1, $url) . "\">" . ($pages - 1) . "</a>";
        }
        */
    
        if ($end < $pages) {
            $html .= "<a href=\"" . str_replace("__page__", $pages, $url) . "\">$pages</a> ";
        }
    
        if ($page >= $pages) {
            $html .= "<a>下一页</a> ";
        }
        else {
            $html .= "<a href=\"" . str_replace("__page__", $page + 1, $url) . "\">下一页</a> ";
        }
        
        $html .= "</div>";
    
        return $html;
    }
}

function dump($val)
{
    $out  = "<pre style=\"background: #000; color: #ccc; font: 11px 'courier new'; text-align: left; width: 100%; padding: 5px\">\n";
    $out .= print_r($val, true);
    $out .= "</pre>\n";
    
    echo $out;
}

function iconv_recursion($incharset,$outcharset,&$value)
{
    if(is_array($value))
    {
    	foreach($value as $key => $data)
    	{
    		iconv_recursion($incharset,$outcharset,$value[$key]);
    	}
    }
    else
    	$value=iconv($incharset,$outcharset,$value);
}

/** 
 * 截取UTF8编码字符串从首字节开始指定宽度(非长度), 适用于字符串长度有限的如新闻标题的等宽度截取 
 * 中英文混排情况较理想. 全中文与全英文截取后对比显示宽度差异最大,且截取宽度远大越明显. 
 * @param string $str   UTF-8 encoding 
 * @param int[option] $width 截取宽度 
 * @param string[option] $end 被截取后追加的尾字符 
 * @param float[option] $x3<p> 
 *  3字节（中文）字符相当于希腊字母宽度的系数coefficient（小数） 
 *  中文通常固定用宋体,根据ascii字符字体宽度设定,不同浏览器可能会有不同显示效果</p> 
 * 
 * @return string 
 * @author waiting 
 * http://waiting.javaeye.com 
 */  
function substrs($str, $width = 0, $end = '…', $x3 = 0) {  
    global $CFG; // 全局变量保存 x3 的值  
    if ($width <= 0 || $width >= strlen($str)) {  
        return $str;  
    }  
    $arr = str_split($str);  
    $len = count($arr);  
    $w = 0;  
    $width *= 10;  
  
    // 不同字节编码字符宽度系数  
    $x1 = 11;   // ASCII  
    $x2 = 16;  
    $x3 = $x3===0 ? ( $CFG['cf3']  > 0 ? $CFG['cf3']*10 : $x3 = 21 ) : $x3*10;  
    $x4 = $x3;  
  
    // http://zh.wikipedia.org/zh-cn/UTF8  
    for ($i = 0; $i < $len; $i++) {  
        if ($w >= $width) {  
            $e = $end;  
            break;  
        }  
        $c = ord($arr[$i]);  
        if ($c <= 127) {  
            $w += $x1;  
        }  
        elseif ($c >= 192 && $c <= 223) { // 2字节头  
            $w += $x2;  
            $i += 1;  
        }  
        elseif ($c >= 224 && $c <= 239) { // 3字节头  
            $w += $x3;  
            $i += 2;  
        }  
        elseif ($c >= 240 && $c <= 247) { // 4字节头  
            $w += $x4;  
            $i += 3;  
        }  
    }  
  
    return implode('', array_slice($arr, 0, $i) ). $e;  
}  

function IntfaceProxy($intface, $cachetime = 600, $offer = false, $minSize = 10)  {
	$intfaceKey = md5($intface);
	$tmp = str_split($intfaceKey, 2);
	$return = null;
	//  生成缓存散列目录
	$cachePath = "/tmp/intface/" . "$tmp[0]/" . "$tmp[1]/";
	makedir($cachePath);
	$cachePath .= $intfaceKey;
	clearstatcache();
	$filetime = filemtime($cachePath);
	// 如果缓存已超时，则读取接口
	if(time() - $filetime > $cachetime) {
		$timer1 = microtime(true);
		$data = @file_get_contents($intface);
		$timer2 = microtime(true);
		$cmd = empty($_SERVER['REQUEST_URI']) ? implode(",", $_SERVER['argv']) : $_SERVER['REQUEST_URI'];
		@file_put_contents("/www/scripts/logs/intface.log", date("Y-m-d H:i:s") . "\t" . strlen($data) . "\t" . ($timer2-$timer1) . "\t" . $intface . "\t{$cmd}\r\n", FILE_APPEND);
		// 如果读取成功，则更新缓存
		if(!empty($data) && strlen($data) >= $minSize)  {
			$return = $data;
			@file_put_contents($cachePath, $data);
		}
		// 如果读取失败，并强制使用接口开关打开时，使用空数据，并将空数据写入缓存
		elseif($offer) {
			$return = $data;
			@file_put_contents($cachePath, $data);
		}
		// 如果读取失败，并强制使用接口开关关闭时，使用缓存
		else {
			$return = @file_get_contents($cachePath);
		}
	}
	// 如果缓存未超时，则读取缓存
	else {
		$return = @file_get_contents($cachePath);
	}
	return $return;
}

function get_info($url){
	$handler=curl_init($url);
	curl_setopt($handler,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($handler,CURLOPT_CONNECTTIMEOUT,2);
	$out =curl_exec($handler);
	curl_close($handler);
	return  unserialize($out);
}

function makedir($dir, $mode = 0755) {
	if (is_dir($dir) || @mkdir($dir, $mode)) return true;
	if (!makedir(dirname($dir), $mode)) return true;
	return @mkdir($dir, $mode);
}

function rm($dir) {
	if(@unlink($dir)) return;
	if(!$dh = @opendir($dir)) return;
	while (($obj = readdir($dh))) {
		if($obj == '.' || $obj == '..') continue;
			rm($dir . '/' . $obj);
        }
        closedir($dh);
        @rmdir($dir);
}

function copydir($source, $dest, $overwrite = true) {
	if($source && $dest){
        if ($handle = opendir($source)) {
            if (is_file($dest) && $overwrite) {
                $this->rm($dest);
            }
            if (!is_dir($dest)) {
                mkdir($dest);
            }
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $sourcepath = $source . '/' . $file;
                    $destpath = $dest . '/' . $file;
                    if (is_file($sourcepath)) {
                        if (!is_file($destpath) || $overwrite) {
                            if (is_dir($destpath) && $overwrite) {
                                $this->rm($destpath);
                            }
                            @copy($sourcepath, $destpath);
                        }
                    }
                    elseif(is_dir($sourcepath)) {
                        $this->copydir($sourcepath, $destpath, $overwrite);
                    }
                }
            }
            closedir($handle);
        }
    }
}

function UploadFile($tmp_name, $filename) {
	if($tmp_name && $filename){
        // 过滤目标路径包含 .. 和 扩展名为 php 的文件。
        if(strpos($filename,'..') !== false || eregi("\.php$", $filename)) {
            return false;
        }
        // 当 move_uploaded_file 函数存在时，优先使用它，
        if(function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name, $filename)) {
            @chmod($filename, 0777);
            return true;
        }
        // 如果它出错，则使用 copy，
        elseif(@copy($tmp_name, $filename)) {
            @chmod($filename, 0777);
            return true;
        }
        // 如果还出错，则使用 file_get_contents 和 file_put_contents。
        elseif(is_readable($tmp_name)) {
            file_put_contents($filename, file_get_contents($tmp_name));
            if(file_exists($filename)) {
                @chmod($filename, 0777);
                return true;
            }
        }
        return false;
	}
}

//国际足球过刊阅读
function getFootZZ()
{
    $num = 3;
    $url = 'http://media.titan24.com/soccerweekly/index.html';
    $htm = file_get_contents($url);
    // 加入本地缓存防止页面读取失败
    if ($htm === false)
    {
        $htm = file_get_contents("/www/news.titan24.com/titan/31/bak/guokanyuedu.txt");
    } else {
        file_put_contents("/www/news.titan24.com/titan/31/bak/guokanyuedu.txt", $htm);
        @chmod("/www/news.titan24.com/titan/31/bak/guokanyuedu.txt", 0777);
    }
    preg_match_all("/\d{0,3}(-)?\d{3}[a-zA-Z]*\.jpg/", $htm, $img); // 获取图片
    $img = $img[0];
    for ($i = 0, $j = count($img); $i < $j; $i++) // 过滤掉重复
    {
        if (intval($img[$i]) == intval($img[$i+1])) unset($img[$i]);
    }
    $img = array_slice($img, 0, $num);
    return $img;
}


//联赛积分数据排序方法
function teamScoreOrder($arr1, $arr2)
{
    return ($arr1['goaltimes'] / ($arr1['wintimes'] + $arr1['equtimes'] + $arr1['losetimes']) < $arr2['goaltimes'] / ($arr2['wintimes'] + $arr2['equtimes'] + $arr2['losetimes'])) ? 1 : -1;
}
 
// 联赛红黄牌数据排序方法
function teamCardOrder($arr1, $arr2)
{
    return ($arr1['yellow_cards_num'] / $arr1['match_num'] < $arr2['yellow_cards_num'] / $arr2['match_num']) ? 1 : -1;
}


/**
 * 过滤统计小组赛各球队积分榜
 *
 * @param  $arrTeam Array 小组各球队
 * @param  $allTeam Array 整个赛区积分
 * @retrun Array 返回指定小组积分榜
 */
function team_point($arrTeam, $allTeam)
{
 $tmp_arr = array();
 foreach ($allTeam as $val) // 循环赛区积分数组，过滤指定小组球队积分数据
 {
  if (in_array($val[teamname], $arrTeam))
  {
   $tmp_arr[] = $val;
  }
 }
 
 $tmp_team = array();
 foreach ($tmp_arr as $val)
 {
  $tmp_team[] = $val[teamname];
 }
 
 foreach ($arrTeam as $val)
 {
  if (!in_array($val, $tmp_team))
  {
   $tmp_arr[] = array('teamname'=>$val,'wintimes'=>0,'equtimes'=>0,'losetimes'=>0,'score'=>'0');
  }
 }
 
 return $tmp_arr;
}
 
// 合并新闻按时间排序
function news_postdate_order($arr1, $arr2)
{
    return ($arr1['postdate'] > $arr2['postdate']) ? -1 : 1;
}
 
// 过滤关键字搜索重复新闻
function delete_news_cid($arr, $num=10)
{
    $cids = array();
    $tmp = array();
    foreach ($arr as $val)
    {
        if (!in_array($val['tid'], $cids))
        {
            $tmp[] = $val;
            $cids[] = $val['tid'];
        }
    }
	if ($num== 0 ) return $tmp;	
	return array_slice($tmp, 0, $num);

}

//计算缩略图的大小
function scale_image($img, $width, $height) {
	$imagesize = @getimagesize($img);	
	$arg = array(
			'max_width'  => $width,
			'max_height' => $height,
			'cur_width'  => $imagesize[0],
			'cur_height' => $imagesize[1]
		);
	$ret = array('img_width' => $arg['cur_width'], 'img_height' => $arg['cur_height']);
	if ( $arg['cur_width'] > $arg['max_width'] ) {
		$ret['img_width']  = $arg['max_width'];
		$ret['img_height'] = ceil( ( $arg['cur_height'] * ( ( $arg['max_width'] * 100 ) / $arg['cur_width'] ) ) / 100 );
		$arg['cur_height'] = $ret['img_height'];
		$arg['cur_width']  = $ret['img_width'];
	}
	if ( $arg['cur_height'] > $arg['max_height'] ) {
		$ret['img_height']  = $arg['max_height'];
		$ret['img_width']   = ceil( ( $arg['cur_width'] * ( ( $arg['max_height'] * 100 ) / $arg['cur_height'] ) ) / 100 );
	}
	return $ret;
}

//刷新CDN使用
function refresh_cdn_lx($url,$channel) {
 if (substr($url,0,7)=="http://") $url = substr($url,7,strlen($url));
 //var_dump($url); //日志分析用
 $time="[".date('Y-m-d H:i:s')."]";
 $username = "titan24";
 $password = "gsZmeqiO";
 $md5 = md5($username . $password . $url);
 $cdnurl = "http://wscp.lxdns.com:8080/wsCP/servlet/contReceiver?username=$username&passwd=$md5&url=$url";
 $content = file_get_contents($cdnurl);
 //file_put_contents("/tmp/".$channel."_cdn. log", $time." ".$url." ".$content);
 fwrite(fopen("/tmp/".$channel."_cdn.log",'a+'),$time." ".$url." ".$content); 
}


function htmlspecialchars_array(&$data){	
	if(is_array($data)){
		foreach($data as &$v){
			if(is_array($v)){
				htmlspecialchars_array($v);
			}else {                
				$v= is_string($v)? htmlspecialchars($v): $v;
			}
		}
	}else{
		$data= is_string($data)? htmlspecialchars($data): $data;
	}
}

//null
function simpleParseIniFile($filename){
	$data= parse_ini_file($filename, false);
	$result= array();
	foreach ($data as $key => $var){
		$bits = explode('.', $key);
		$thisSection = trim($bits[0]);
		if(count($bits)==1){
	      	$result[$thisSection]= $var;
		}elseif(count($bits)==2){
	      	$result[$thisSection][$bits[1]]= $var;
		}else{
			$str= '$result';
			for ($i = 0; $i < count($bits); $i++) {
				$str.= '[$bits['.$i.']]';
			}							
			if(false=== eval("{$str}= '$var';")){
				echo "{$str}= $var;\n";				
			}
		}
	}
	return $result;
}

//usage: addToTitanWeibo('soccer','我不是黄蓉,我不会武功', 'http://soccer.titan24.com/2010-05-14/70624.html')
function addToTitanWeibo($channel, $content, $url, $image= null){
	$weiboConfig = simpleParseIniFile(LIBRARY_PATH.'weibo/weibo.ini');
	require_once(LIBRARY_PATH. 'weibo/weibo.class.php');
	$config= $weiboConfig['weibo'][$channel];
	if(!$config || !$content)return false;
	$w = new weibo($config['API_KEY']);
	$w->setUser($config['username'], $config['password']);

	$content= substrs($content, 110, 1, 1);
	
	if(!$image){
		$result= $w->update($content.' '.$url);
	}else{		
		$result= $w->upload($content.' '.$url, file_get_contents($image));
	}	
	if(!$result['error']){
		return true;
	}else{
		file_put_contents(DATA_PATH.'weibo_error.log', $content.' '.$url.' '.var_export($result, TRUE)."\n");
		return false;
	}
}

//usage: getWeiboUrl('soccer')
function getWeiboUrl($channel){
	$weiboConfig = simpleParseIniFile(LIBRARY_PATH.'weibo/weibo.ini');	
	$config= $weiboConfig['weibo'][$channel];	
	return $config['domain'];	
}
