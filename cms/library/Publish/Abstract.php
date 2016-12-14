<?php
require_once MODULES_PATH . 'object/models/Objects.php';
require_once MODULES_PATH . 'object/models/ObjContents.php';
require_once MODULES_PATH . 'template/models/Templates.php';
require_once LIBRARY_PATH . 'Publish/models/Contentarea.php';
require_once LIBRARY_PATH . 'Publish/models/Customlist.php';
require_once LIBRARY_PATH . 'Publish/models/keyword.php';
require_once LIBRARY_PATH . 'Publish/models/fpage.php';
require_once MODULES_PATH . 'focus/models/Focus.php';


/**
 * publish class
 *
 * options:
 * type
 * published_path
 * workplace_path
 * cms_public_path
 * ...
 */
abstract class Publish
{
    const TAG_PREFIX = '<!--#';
    const TAG_SUFFIX = '#-->';
    
    const TAG_MODULE = 'CMS_MODULE';
    const TAG_FUNC   = 'CMS_FUNC';

    // editions
    const WORK_EDITION   = 1;
    const PUBLIC_EDITION = 2;

    // object types
    const OBJ_TYPE_TEMPLATE = 0;
    const OBJ_TYPE_ARTICLE = 1;
    const OBJ_TYPE_FOCUS   = 2;
    const OBJ_TYPE_PAGE    = 3;
	const OBJ_TYPE_ALBUM   = 4;

    // object OWNEDs
    const OBJ_OWNED_CHANNEL = 0;
    const OBJ_OWNED_TEMPLATE = 1;
    const OBJ_OWNED_PAGE    = 2;

    private $_scripts = array ( 
        'jquery/jquery.js', 'edit.js'
    );

    private $_styles = array (
        'edit.css'
    );

    private $_append_files = array (
        'form.html'
    );

    protected $_db;
	protected $_config;     
    
    protected $_published_path;
    protected $_workplace_path;
    protected $_cms_public_url;
    protected $_template;
    protected $_template_id = 0;

    protected $_type;
    protected $_pageid;
    protected $_owned;

    protected $_file_path;

    protected $_page_table;
    protected $_templates_table;
    protected $_focus_table;
    protected $_article_table;
    
    public static $current_edition = 0;
    public static $current_oid = 0;
        
    protected $_modules = array();
    protected $_vars = array();    
    
    public $debug = false;
    public $error;
    
    public function __construct($config, $pageid)
    {
        $this->_workplace_path = $config->path->workplace;
        $this->_published_path = $config->path->published;
        $this->_cms_public_url = $config->site->url;

        $db = Zend_Db::factory('PDO_MYSQL', $config->db);
        $db->query("SET NAMES 'utf8'");
        $this->_db=$db;

        Zend_Db_Table::setDefaultAdapter($db);

        $this->_templates_table = new TemplatesTable($db);
        $this->_objects_table = new ObjectsTable();
        $this->_obj_contents_table = new ObjContentsTable();
		$this->_config= $config;
    }

    protected function _publish($type = 0)
    {
        $type = $type ? $type : $this->_type;
        $template = $this->getTemplate();
        $this->_template = $template->content;
        if (!$this->_template) {
            // TODO: error
			echo 'template error!';
            return false;
        }
		//发布的是文章
		if($type==1){
			//分页处理
			$fpage=new Fpage($this->_config, $this->_vars);		
			$data=$fpage->parseContent();	
			unset($this->_vars['contents']);

			//发布每一页
			foreach($data as $var){				
				//关键字替换
				$keyword= new Keyword();
				$keyword->get();
				$keyword->replace($var['content']);
				$this->set('contents',$var['content']);					

				// work edition
				$work_edition = $this->renderTemplate($this->_template, self::WORK_EDITION);
				if ($work_edition === false) {			
					echo $this->error;
					return false;
				}        
				$headers = $this->_appendHeaders();
				$append_html = $this->_appendFiles();
				$work_edition = preg_replace('`</head>`i', $headers . '</head>', $work_edition);
				$work_edition = preg_replace('`</body>`i', $append_html . '</body>', $work_edition);    
				$path = $this->_concatPath($this->_workplace_path.$var['url']);	
				
				if (false == $this->_write($path, $work_edition)) {
					return false;
				}


				// public edition
				$public_edition = $this->renderTemplate($this->_template,  self::PUBLIC_EDITION);				
				$path = $this->_concatPath($this->_published_path.$var['url']);
				if (false == $this->_write($path, $public_edition)) {
					return false;
				}
			}
		}else{		
/*
            // work edition
			$work_edition = $this->renderTemplate($this->_template,  self::WORK_EDITION);
			if ($work_edition === false) {
				echo $this->error;
				return false;
			} 
			$headers = $this->_appendHeaders();
			$append_html = $this->_appendFiles();
			$work_edition = preg_replace('`</head>`i', $headers . '</head>', $work_edition);
			$work_edition = preg_replace('`</body>`i', $append_html . '</body>', $work_edition);        
			$path = $this->_concatPath($this->_workplace_path . $this->_file_path);
			if (false == $this->_write($path, $work_edition)) {
				return false;
			}
*/

			// public edition
			$public_edition = $this->renderTemplate($this->_template, '', self::PUBLIC_EDITION);
			$path = $this->_concatPath($this->_published_path . $this->_file_path);
			if (false == $this->_write($path, $public_edition)) {
				return false;
			}
		}

        return true;
    }
    
    //hanodng 2013.4.17 将预览和发布分开
    protected function _publishWork($type = 0)
    {
        $type = $type ? $type : $this->_type;
        $template = $this->getTemplate();
        $this->_template = $template->content;

        if (!$this->_template) {
            // TODO: error
			echo 'template error!';
            return false;
        }
		//发布的是文章
		if($type==1){
			//分页处理
			$fpage=new Fpage($this->_config, $this->_vars);		
			$data=$fpage->parseContent();	
			unset($this->_vars['contents']);
			
			//发布每一页
			foreach($data as $var){				
				//关键字替换
				$keyword= new Keyword();
				$keyword->get();
				$keyword->replace($var['content']);
				$this->set('contents',$var['content']);					
				// work edition
				$work_edition = $this->renderTemplate($this->_template, self::WORK_EDITION);
				if ($work_edition === false) {			
					echo $this->error;
					return false;
				}        
				$headers = $this->_appendHeaders();
				$append_html = $this->_appendFiles();
				$work_edition = preg_replace('`</head>`i', $headers . '</head>', $work_edition);
				$work_edition = preg_replace('`</body>`i', $append_html . '</body>', $work_edition);    
				$path = $this->_concatPath($this->_workplace_path . $var['url']);	
				
				if (false == $this->_write($path, $work_edition)) {
					return false;
				}
			}
		}else{
		    if(!preg_match("/\<body.*?>/i",$this->_template)){
		       $this->_template='<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>'.$this->_template.'</body></html>';
		    }
            //$workFilePath=preg_replace("/\.[a-zA-Z]{1,5}/",'.edit$0',$this->_file_path);
            //whui 修改 原先不支持url带点
            $workFilePath=preg_replace("/\.(html|shtml|htm)/",'.edit$0',$this->_file_path);
            $workTemplate = preg_replace("/\<script.*?\>.*?\<\/script\>/","",$this->_template);
            // work edition
			$work_edition = $this->renderTemplate($workTemplate,  self::WORK_EDITION);
			if ($work_edition === false) {
				echo $this->error;
				return false;
			} 
			$headers = $this->_appendHeaders();
			$append_html = $this->_appendFiles();
			$work_edition = preg_replace('`</head>`i', $headers . '</head>', $work_edition);
			$work_edition = preg_replace('`</body>`i', $append_html . '</body>', $work_edition);
            switch($type){
                case 2:
                    $workUrl=$this->_config->site->url.'/focus/focus/preview/fid/'.$this->_vars['cms_page_id'];
                    break;
                case 3:
                    $workUrl=$this->_config->site->url.'/page/pages/preview/pid/'.$this->_vars['cms_page_id'];
                    break;
                default :
                    $workUrl='javascript:alert("未知项目，请联系开发人员");';
            }
            $work_edition = preg_replace('`</html>`i', '</html><script>window.onload=function(){document.getElementsByTagName("body")[0].ondblclick=function(){if(window.confirm("退出编辑模式？")){location.href="'.$workUrl.'"}}}</script>', $work_edition);        
			$path = $this->_concatPath($this->_workplace_path . $workFilePath);
			if (false == $this->_write($path, $work_edition)) {
				return false;
			}
            
            // public edition
			$public_edition = $this->renderTemplate($this->_template, '', self::PUBLIC_EDITION);
			//$public_edition = preg_replace('`</html>`i', '</html><script>window.onload=function(){document.getElementsByTagName("body")[0].ondblclick=function(){if(window.confirm("进入编辑模式？")){Url=location.pathname;Url=Url.replace(/(\.[a-zA-Z]{1,5})/,".edit\$1");location.href="http://"+location.host+Url;}}}</script>', $public_edition);
            //修改正则匹配，精确匹配到.htm.shtml.html
            $public_edition = preg_replace('`</html>`i', '</html><script>window.onload=function(){document.getElementsByTagName("body")[0].ondblclick=function(){if(window.confirm("进入编辑模式？")){Url=location.pathname;Url=Url.replace(/(\.(html|shtml|htm))/,".edit\$1");location.href="http://"+location.host+Url;}}}</script>', $public_edition);
            
            $path = $this->_concatPath($this->_workplace_path . $this->_file_path);
			if (false == $this->_write($path, $public_edition)) {
				return false;
			}
		}
        return true;
    }

    /*
     private function _parseTags($edition = self::WORK_EDITION)
     {
     $regx_modules = '/' . self::TAG_PREFIX . self::TAG_MODULE . '\((.*?)\)' . self::TAG_SUFFIX . '/e';
     $regx_funcs   = '/' . self::TAG_PREFIX . self::TAG_FUNC . '\((.*?)\)' . self::TAG_SUFFIX . '/e';

     $contents = preg_replace($regx_modules, '$this->_tagModuleCallback("\\1")', $this->_template);
     $contents = preg_replace($regx_funcs, '$this->_tagFuncCallback("\\1")', $contents);
     foreach ($this->_modules as &$m) {
     //$m = preg_replace($regx_funcs, '$this->_tagFuncCallback("\\1")', $m);
     $m = mb_convert_encoding($m, 'utf8', 'gbk');
     }

     return $contents;
     }
     */
    
    /**
     * 给模板变量赋值
     *
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $this->_vars[$name] = $value;
    }
    
    /**
     * 解析模板中的模块及函数，返回html
     *
     * @param string $template
     * @param integer $edition
     * @return string
     */
    public function renderTemplate($template = '', $edition = self::WORK_EDITION)
    {
        $regx_modules = '/' . self::TAG_PREFIX . self::TAG_MODULE . '\((.*?)\)' . self::TAG_SUFFIX . '/';
       // echo $regx_modules;
        // replace modules tag
        // $contents = preg_replace($regx_modules, '$this->_tagModuleCallback("\\1", $edition)', $template);
          
          if($edition==self::WORK_EDITION){
            $contents = preg_replace_callback($regx_modules,function ($r){
            //global $edition;
              return $this->_tagModuleCallback($r[1]);
            }, $template);
          }else{
             $contents = preg_replace_callback($regx_modules,function ($r){
            //global $edition;
              return $this->_tagModuleCallback($r[1],2);
            }, $template);
          }
        
        // render template
        /*$_PHP_CODE = str_replace(array(self::TAG_PREFIX, self::TAG_SUFFIX), array('<?php ', ' ?>'), $contents);*/
        self::$current_edition = $edition;
        $_PHP_CODE  = "<?php\n";
        $_PHP_CODE .= "print <<<EOT\n";
        $_PHP_CODE .= str_replace(array(self::TAG_PREFIX, self::TAG_SUFFIX), array("\nEOT;\n", ";\nprint <<<EOT\n"), $contents);
        $_PHP_CODE .= "\nEOT;\n";

        //echo nl2br(htmlspecialchars($_PHP_CODE));
        ob_start();
        extract($this->_vars);	
        $fatal_error = true;
        
        try {
            $result = eval('?>' . $_PHP_CODE);
        } catch(Exception $e) {
            //
        }
       
        $html = ob_get_contents();
        ob_end_clean();
        

		$html = str_replace('<!--ssi#', '<!--#', $html);

        if ($result === false) {
            $this->error = $html . "<br />" . highlight_string($_PHP_CODE, 1);
            //echo "code:<br />" . highlight_string($_PHP_CODE);
            return false;
        }
        
        //首页发布不全debug,请测试完毕后删除此段

        @preg_match("/<html[\w\W]*?>[\w\W]*?<\/html>/i",$html,$indexDebug);
        if(!$indexDebug and $this->_template_id=='1007'){
            $ctime=time();
            @file_put_contents("/www/publish/vodone/debug/indexDebug_".$ctime.".log",$_PHP_CODE);
            @file_pub_contents("/www/publish/vodone/debug/indexHTML_".$ctime.".log",$html);
        }
        //----end------

        return $html;
    }

    /**
     * 取得某模块在某页面上的内容
     *
     * @param integer $oid
     * @param integer $pageid
     * @param integer $type
     * 
     * @return object
     */
    public function getObjectContent($oid, $pageid, $type = 0)
    {
        // type only accept article, focus, page
        $type = $type ? $type : $this->_type;
        if (!$type) {
            return false;
        }
         
        // search by type 'page'
        $obj = $this->getObjectContentByOwner($oid, $pageid, $type, self::OBJ_OWNED_PAGE);        

        // search with template
        if (!$obj) {
            $obj = $this->getObjectContentByOwner($oid, $pageid, $type, self::OBJ_OWNED_TEMPLATE);
        }

        // search with channel
        if (!$obj) {
            $obj = $this->getObjectContentByOwner($oid, $pageid, $type, self::OBJ_OWNED_CHANNEL);
        }

        if (!$obj) {
            return false;
        }

        //$contents = $obj->contents;
        return $obj;
    }
    
    /**
     * 根据模块所属类型取得其内容
     *
     * @param integer $oid
     * @param integer $pageid
     * @param integer $type
     * @param integer $owned
     * 
     * @return object
     */
    public function getObjectContentByOwner($oid, $pageid = 0, $type = self::OBJ_TYPE_PAGE, $owned = self::OBJ_OWNED_PAGE)
    {
        $type = $type ? $type : $this->_type;
		
		$flag= 0;
        if($owned == self::OBJ_OWNED_PAGE) {
            $where = "oid = $oid AND type = $type AND pageid = $pageid AND owned = " . self::OBJ_OWNED_PAGE;				
			$obj = $this->_obj_contents_table->fetchRow($where);			
			if(!$obj->content)$flag++;
			else return $obj;			
        }
		
		if ($owned == self::OBJ_OWNED_TEMPLATE  || $flag) {
            $template_id = $this->_template_id;			
            $where ="oid = $oid AND pageid = $template_id AND owned = " . self::OBJ_OWNED_TEMPLATE;			
			// "oid = $oid AND type = " . self::OBJ_TYPE_TEMPLATE . " AND pageid = $template_id AND owned = " . self::OBJ_OWNED_TEMPLATE;									
			$obj = $this->_obj_contents_table->fetchRow($where);			
			if(!$obj->content)$flag++;
			else return $obj;			
		}
		
        if ($owned == self::OBJ_OWNED_CHANNEL || $flag) {
            $where = "oid = $oid AND pageid= 0 AND owned = " . self::OBJ_OWNED_CHANNEL;								
			$obj = $this->_obj_contents_table->fetchRow($where);
			return $obj;
        }	
       	return;
    }
	
	/**
     * 根据模块所属类型取得需要更新的记录
     *
     * @param integer $oid
     * @param integer $pageid
     * @param integer $type
     * @param integer $owned
     * 
     * @return object
     */
    public function testObjectContentByOwner($oid, $pageid = 0, $type = self::OBJ_TYPE_PAGE, $owned = self::OBJ_OWNED_PAGE)
    {
        if($owned == self::OBJ_OWNED_PAGE) {
            $where = "oid = $oid AND type = $type AND pageid = $pageid AND owned = " . self::OBJ_OWNED_PAGE;								
        }elseif ($owned == self::OBJ_OWNED_TEMPLATE ) {
            $template_id = $this->_template_id;			
            $where ="oid = $oid AND pageid = $template_id AND owned = " . self::OBJ_OWNED_TEMPLATE;					
		}elseif ($owned == self::OBJ_OWNED_CHANNEL) {
            $where = "oid = $oid AND pageid= 0 AND owned = " . self::OBJ_OWNED_CHANNEL;								

        }	
		$obj = $this->_obj_contents_table->fetchRow($where);
       	return $obj;
    }
	
    /**
     * 取得模板
     *
     * @param integer $template_id
     * 
     * @return object
     */   
    public function getTemplate($template_id = 0)
    {
        $template_id = $template_id ? $template_id : $this->_template_id;
        if (!$template_id) {
            return false;
        }

        $template = $this->_templates_table->find($template_id)->current();
        
        return $template;
    }
    
    
    /**
     * 解析模块内容
     *
     * @param string $content
     * @return string
     */
    public function renderObject($content = '')
    {
        return $content;
    }
    
    /**
     * module tag callback
     *
     * @param unknown_type $obj_name
     * @return unknown
     */
    private function _tagModuleCallback($obj_name, $edition = self::WORK_EDITION)
    {
        $obj = $this->_objects_table->findWithName($obj_name);
        if (!$obj) {
            return '';
        }
        
        $obj_content = $this->getObjectContent($obj->oid, $this->_pageid);
        
        $html = $obj_content ? $obj_content->content : '';
        
        /*
        if ($obj->type == 'list') {
            $custom_list_table = new CustomlistTable();
            $list = $custom_list_table->fetchWithOid($obj->oid);
            if ($list) {
                $html = $list->contenthtml;
            }
        }*/
        
        //$this->_modules[$obj->oid] = mb_convert_encoding($html, 'utf8', 'gbk');        
        $this->_modules[$obj->oid] = array();
        $this->_modules[$obj->oid]['type'] = $obj->type;
        $this->_modules[$obj->oid]['name'] = $obj_name;
        
        if ($obj_content) {
            $this->_modules[$obj->oid]['type'] = $obj->type;            
            $this->_modules[$obj->oid][$obj_content->owned] = 1;
        }
        if ($edition == self::WORK_EDITION) {
           if($html == ""){ $html = "<!--#Publish::\$current_oid = {$obj->oid}; \$cms_oid={$obj->oid};#--><a id=\"cms_module_{$obj->oid}\" class=\"module_anchor\">&nbsp;</a>\n$html\r\n"; }
		   else{$html = "<!--#Publish::\$current_oid = {$obj->oid}; \$cms_oid={$obj->oid};#--><a id=\"cms_module_{$obj->oid}\" class=\"module_anchor\"></a>\n$html\r\n";}
        }else{
            $html="<!--#\$cms_oid={$obj->oid};#-->\n$html\r\n";
        }
        return $html;
    }

    /**
     * func tag callbackx
     *
     * @param unknown_type $params
     * @return unknown
     */
    private function _tagFuncCallback($params)
    {
        $params = preg_split('`, *`', trim($params));
        $func = array_shift($params);
        $value = $this->{$func}(join(', ', $params));

        return $value;
    }

    private function _appendHeaders()
    {
        $insert = '';

        foreach ($this->_scripts as $s) {
            $insert .= '<script type="text/javascript" src="' . $this->_cms_public_url .'/scripts/' . $s . '"></script>' . "\n";
        }

        foreach ($this->_styles as $s) {
            $insert .= '<link href="' . $this->_cms_public_url .'/styles/' . $s . '" media="screen" rel="stylesheet" type="text/css" />' . "\n";
        }
        
        // modules array
        $json_modules = json_encode($this->_modules);
        $jscode  = "var cms_pageid = {$this->_pageid}; var cms_type = {$this->_type}; var cms_modules = $json_modules; var EXTRA_MENUS = [];";       
        $insert .= '<script type="text/javascript">' . $jscode . '</script>' . "\n";

        return $insert;
    }

    private function _appendFiles()
    {
        $html = '';
        $path = dirname(__FILE__) . '/files/';
        foreach ($this->_append_files as $file) {
            $html .= file_get_contents($path . $file);
        }

        return $html;
    }

    protected function _write($path, $contents)
    {
    	//exit($path);
        @mkdir(dirname($path), 0755, true);
        if (false === file_put_contents($path, $contents)) {
            return false;
        }

        return true;
    }

    protected function _concatPath()
    {
        $args = func_get_args();
        $path = join('/', $args);

        $path = str_replace('\\', '/', $path);
		$path = str_replace('//', '/', $path);

        return $path;
    }
    
    /*
    private function __call($method, $args)
    {
        if (!function_exists($method)) {
            try {
                require_once LIBRARY_PATH . 'Publish/functions/' . $method . '.php';
            } catch(Exception $e) {
                return 'unknow cms func';
            }
        }

        array_unshift($args, $this);

        $value = call_user_func_array($method, $args);

        return $value;
    }
*/
}

function CMS_FUNCTION()
{
    $args = func_get_args();
    $func = array_shift($args);
    
    $func_path = LIBRARY_PATH . 'Publish/functions/' . $func . '.php';
    if (!function_exists($func)) {
        if (!is_file($func_path)) {
            return false;
        }
        
        try {
            require_once $func_path;
        } catch(Exception $e) {
            return 'unknow cms func';
        }
    }
    
    return call_user_func_array($func, $args);
}

//检测提交的模块代码时使用
function CMS_FUNCTION_Check(){return array();}
