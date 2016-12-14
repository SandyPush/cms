<?php
/**
 * 关键字
 *
 */
class Keyword {
    
	public $keyreplace = '<a href="$url" target="_blank">$keyword</a>';
	public $keywords= array();


    /*  解析关键字与其对应的地址。
        $values 格式为：
            keyword|url换行
            ...
        其中换行可以是 Windows、Unix/Linux 或 Mac OS 格式。
        返回值格式为：
        array (
            keyword(string) => (replace)string,
            ...
        );
    */
    public function get() {
		$values= file(DATA_PATH.'keyword.dat');
        $keywords = array();    
        foreach ($values as $value) {
			$value=trim($value);
            list($keyword, $url) = explode("|", $value);
			if(empty($keyword)||empty($url))continue;
            if ($keyword != "" and $url != "") {
                $keywords[$keyword] = str_replace('$keyword', "$keyword", $this->keyreplace);
                $keywords[$keyword] = str_replace('$url', "$url", $keywords[$keyword]);
            }
        }
		$this->keywords= $keywords;
        return 0;
    }


    /* 将内容中的关键字全部替换
        $keywords 为关键字替换列表，其值为 get 或 parse 函数的返回值。
        $content 是要进行替换的内容。
        返回值为替换后的内容。
    */
    public function replace(&$content) {	
		foreach ($this->keywords as $keyword => $replace) {
			$keyword=quotemeta($keyword);
			$keyword=str_replace('/','\/',$keyword);
			//$content = str_replace($keyword, $replace, $content);
			//自定义的连接优先，防止被系统连接替换造成链接不完整 by yxw
			////临时去掉限制 zhangjinguo 2009/2/14 21:25

			////if(preg_match("/<a[\s\S]* href=\"[\s\S]*>[\s\S]*{$keyword}[\s\S]*<\/a>/i", $content))continue;				
			////if(preg_match("/alt=\"[\s\S]*{$keyword}[\s\S]*\"/i", $content))continue;	
				
			$search= "/(<a [^>]*>[^<>]*)".$keyword."([^<>]*<\/a>)/i";	
			while(preg_match($search,$content)){
				$content= preg_replace($search,"\${1}######\$2",$content);	
			}

			$search= "/(alt=\"[^\"<>]*){$keyword}([^\"<>]*\")/i";
			while(preg_match($search,$content)){
				$content= preg_replace($search,"\${1}######\$2",$content);	
			}

			$search= "/(<span>[^<>]*)".$keyword."([^<>]*<a [^>]*>[^<>]*<\/a>[ ]*<\/span>)/i";	
			while(preg_match($search,$content)){
				$content= preg_replace($search,"\${1}######\$2",$content);
			}

			$content= $this->str_replace_once($keyword, $replace, $content);	
			$content= str_replace("######",$keyword,$content);			
        }       
    }
    
	public function str_replace_once($needle, $replace, $haystack){
		$pos = strpos($haystack, $needle);
		if ($pos === false) {
			return $haystack;
		}
		return substr_replace($haystack, $replace, $pos, strlen($needle));
	}
}
?>