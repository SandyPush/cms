<?php
/**
 * Describe: 文章分页处理类
 * Date:2009.03.12
 */
require_once 'Zend/Db/Table.php';

class Fpage extends Zend_Db_Table{
	protected $_db;
    protected $_name = 'article';
    protected $_primary = 'aid';

	private $content= '';
	private $url;
	private $aid;
	private $cid;
	private $page_break= '<!--||||||-->';
	private $pageEnd=0;

    public function __construct($config, $array){ 	
        $this->content = $array['contents'];
		$this->url = $array['url'];
		$this->aid = $array['aid'];
		$this->cid = $array['cid'];
		$db = Zend_Db::factory('PDO_MYSQL', $config->db);
        $db->query("SET NAMES 'GBK'");
		$this->_db= $db;
    }

	private function getSqlData($sql){
		$query= $this->_db->query($sql);
		$result= $query->fetchAll();
		return $result;
	}

    public function parseContent(){
		$data= '';
		$content= $this->content;	
	    $content= str_replace('<div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>',$this->page_break,$content);
		$content= str_replace('<hr style="page-break-after: always" />',$this->page_break,$content);
		$content= str_replace('<hr style="page-break-after: always;" />',$this->page_break,$content);
		$content= str_replace('<hr style="page-break-after: always; " />',$this->page_break,$content);

        $content_array = explode($this->page_break,$content);     
        $count = count($content_array);

		if($count==1){
			$data[0]['content']= $this->parseAlbums($content_array[0]);
			$data[0]['url']= $this->url;	

		}else{
			$path= str_replace('.shtml','',$this->url);
			foreach($content_array as $key => $var){
				$pagenum= $key+1;
				if($pagenum== $count)$this->pageEnd= 1;
				$pageHtml= $this->getPages($pagenum,$count);
				$var.= $pageHtml;
				$data[$key]['content']= $this->parseAlbums($var);
				if($pagenum == 1){
					$data[$key]['url']= $this->url;
				}else{
					$data[$key]['url']= $path."_$pagenum".'.shtml';
				}
			}
		}
		return $data;
    }
	
	/* 对组图的处理 */
	public function parseAlbums($content){		
		if(preg_match("/<img class=\"albums\" id=\"[0-9]{6}\" title=\"下一页\" [^>]*>/i",$content)|| preg_match("/<img id=\"[0-9]{6}\" class=\"albums\" title=\"下一页\" [^>]*>/i",$content)){			
			if(!$this->pageEnd){
				if(preg_match("/<a href='([^>]*)'>下一页<\/a>/i",$content,$matches)){				
					$content= str_replace("$$$$$$",$matches[1],$content);
				}else{
					$content= str_replace("$$$$$$",'#',$content);
				}
			}else{
				if(preg_match("/<a href=\"([^>]*)\"[ ]?>1<\/a>/i",$content,$matches)){
					$content= str_replace("title=\"下一页\"","title=\"上一页\"",$content);
					$content=str_replace('<a href="$$$$$$">(点击图片到下一页)</a>','<a href="$$$$$$">(点击图片到上一页)</a>',$content);
					$content= str_replace("$$$$$$",$matches[1],$content);   
				}else{
					$content= str_replace("$$$$$$",'#',$content); 
				}
			}
		}else{
			$content= str_replace("$$$$$$",'#',$content); 
		}
        return $content;
	}
	
	/* 单篇文章分页 */
    public function getPages($page,$count){          
		if($this->url){
            $url_arr = end(explode('/',$this->url));
			$url_ext = '.'.end(explode('.',$url_arr));            
            $url_pre = substr($url_arr,0,-strlen($url_ext));
           
            if($count>1){
                $str = "";
                if($page==1){
                    $this->pageurl = $url_pre.$url_ext;   
                    $nextUrl = $url_pre.'_2'.$url_ext;     
                }else{
                    $nextPage = $page+1;
                    $prePage = $page-1;
                    $nextUrl = $url_pre.'_'.$nextPage.$url_ext;   
                    $preUrl  = $prePage == 1 ? $url_pre.$url_ext : $url_pre.'_'.$prePage.$url_ext;            
                    $this->pageurl = $url_pre.'_'.$page.$url_ext;                   
                }
                if($page>1){
                    $str.="<a href='$preUrl'>上一页</a>";
                }
                for($i=1;$i<=$count;$i++){
                    if($i==1){
                        $theurl = $url_pre.$url_ext;
                    }else{
                        $theurl = $url_pre.'_'.$i.$url_ext; 
                    }
                    if($i==$page){
                        $str.=" <a href=\"$theurl\" class=\"current\">$i</a>";
                    }else {
                        $str.=" <a href=\"$theurl\" >$i</a>";
                    }
                }
                if($page<$count){
                    $str.=" <a href='$nextUrl'>下一页</a>";
                }
                return "<p class='fpage'><strong>页数：</strong>$str</p>";
            }
        }
        return ;
    }

    public function getNext(){        
        $sql = "SELECT i.title, i.aid, i.url 
                FROM article as i , cate_links as c
                WHERE 
                    i.aid = c.aid 
                    AND c.cid = {$this->cid}
                    AND i.aid<'$this->aid'   
                    AND c.typeid ='3' 
                ORDER BY i.aid DESC 
                LIMIT 1";
        $rs = $this->getSqlData($sql);
        if($rs){  
            return $rs;
        }else{
            return array('aid'=>0,'url'=>'javascript:void(0);','title'=>'');
        }
    }

    public function getPrev(){        
        $sql = "SELECT i.title, i.aid, i.url 
                FROM article as i , cate_links as c
                WHERE 
                    i.aid = c.aid 
                    AND c.cid = {$this->cid}
                    AND i.aid>'$this->aid'   
                    AND c.typeid='3' 
                ORDER BY i.aid ASC 
                LIMIT 1";
        $rs = $this->getSqlData($sql);
        if($rs){ 
            return $rs;
        }else{
            return array('aid'=>0,'url'=>'javascript:void(0);','title'=>'');
        }
    }   
	
	/* 根据各栏目域名得到文章完整URL */
	public function cate2url(){

	}
}
?>
