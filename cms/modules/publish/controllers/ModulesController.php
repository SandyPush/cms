<?php

/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'page/models/Page.php';
require_once MODULES_PATH . 'object/models/Objects.php';
require_once MODULES_PATH . 'object/models/ObjContents.php';
require_once MODULES_PATH . 'object/models/ObjectsRevision.php';
require_once LIBRARY_PATH . 'Publish/Page.php';

class Publish_ModulesController extends BaseController
{
    protected $_db;
    protected $_page_table;
    protected $_obj_contents_table;
    protected $_objects_table;
    protected $params;
    
    protected $_pub;
	protected $channel;
        
    public function init()
    {
        // tables
        $db = $this->getChannelDbAdapter();
        $this->_db = $db;
        
        Zend_Db_Table::setDefaultAdapter($db);
        
        $this->_page_table = new PageTable();
        $this->_obj_contents_table = new ObjContentsTable();
		$this->_obj_revision_table = new ObjectsRevision($db);
        $this->_objects_table = new ObjectsTable();
        
        // common params
        $oid = (int) $this->_getParam('oid');
        $pageid = (int) $this->_getParam('pageid', 0);
        $type = (int) $this->_getParam('type', 0);       
        $owned = (int) $this->_getParam('owned', '');
        
        $this->params = array(
            'oid' => $oid,
            'pageid' => $pageid,
            'type' => $type,
            'owned' => $owned
        );
                
        // publish object
        switch ($this->params['type']) {
            case Publish::OBJ_TYPE_PAGE:
                $pub = new Publish_Page($this->getChannelConfig(), $this->params['pageid']);
                break;
            case Publish::OBJ_TYPE_FOCUS;
                $pub = new Publish_Focus($this->getChannelConfig(), $this->params['pageid']);
                break;
            case Publish::OBJ_TYPE_ARTICLE:
                $pub = new Publish_Article($this->getChannelConfig(), $this->params['pageid']);
                break;
        }
        
        $this->_pub = $pub;
        
        // view options
        /*
        $ajaxContext = $this->_helper->getHelper("ajaxContext")
            ->addActionContext('edit','json')
            ->addActionContext('clear','json')
            //->setAutoJsonSerialization(true)
            ->setAutoDisableLayout(true)
            ->initContext('json');        
        */    
        $this->view->login = true;
        $this->view->layout()->disableLayout();
        
        $this->getResponse()->clearBody();

		$user= Zend_Session::namespaceGet('user');		
		$this->channel= $user['channel'];
    }
    
    public function formAction()
    {
	$this->_checkPermission('modules', 'form-owned-'.$this->params['owned']);
        $this->view->headLink()->appendStylesheet('/styles/module_edit.css');
        $this->view->headScript()->appendFile('/scripts/module_edit.js');
        
        //list($oid, $pageid, $type, $owned) = $this->params;
        extract($this->params);
        
        $obj = $this->_objects_table->find($oid)->current();
        $obj_content = $this->_pub->getObjectContentByOwner($oid, $pageid, $type, $owned);
        
        $cms_functions = simplexml_load_file(LIBRARY_PATH . 'Publish/functions/index.xml'); 
       
        $this->view->params = http_build_query($this->params);   
		$this->view->pageid = $pageid;
        $this->view->object = $obj;
        $this->view->obj_content = $obj_content;
        $this->view->cms_functions = json_encode($cms_functions);
		$this->view->channel= $this->channel;
        
        $config = Zend_Registry::get('channel_config');
        $tplPath=$config->path->published.'ztm/tpl/';
        if ($handle = @opendir($tplPath)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if(strstr($file,'.tpl')){
                        $fileContent=file_get_contents($tplPath.$file);
                        preg_match("/\/\*\*(.*?)\*\//is",$fileContent,$modulesInfo);
                        $modulesInfo=json_decode($modulesInfo[1],1);
                        $modulesInfo['title']=trim($modulesInfo['title']);
                        $modulesInfo['pic']=$config->url->published.'ztm/tpl/'.trim($modulesInfo['pic']);
                        $modulesInfo['content']=preg_replace("/\/\*\*(.*?)\*\//is",'',$fileContent);
                        $list[$file]=$modulesInfo;
                    }
                }
            }
            closedir($handle);
        }
        $this->view->tpl=$list;
    }

	public function reviseAction()
    {  				
		$oid= intval($this->_getParam('oid')); 
		$pageid= intval($this->_getParam('pageid')); 
		$type= intval($this->_getParam('type')); 
		$owned= intval($this->_getParam('owned')); 		
		$param= trim($this->_getParam('param')); 		
		list($uid, $date) = explode('|',$param);
	    !$oid and die();		
	    if(!$date){
		    $data= $this->_obj_revision_table->getList($this->_pub, $oid, $pageid, $type, $owned);
	    }else{
			$data= $this->_obj_revision_table->getContent($oid,$uid,$date);
		} 	
		die($data);	
    }
    
    public function editAction()
    {
        if ($this->_getParam('submit_clear')) {
            $this->_forward('clear');
            return true;
        }
        // params
        $content = $this->_getParam('content', '');
		
        //检查代码
        $_PHP_CODE  = "<?php\n";
        $_PHP_CODE .= "print <<<EOT\n"; 
        $_PHP_CODE .= str_replace(array("<!--#", "#-->"), array("\nEOT;\n", ";\nprint <<<EOT\n"), $content);
        $_PHP_CODE .= "\nEOT;\n";
        $_PHP_CODE=str_replace("CMS_FUNCTION","CMS_FUNCTION_Check",$_PHP_CODE);
        $result = @eval('?>' . $_PHP_CODE);
        $error=error_get_last();
        if($error['type']<8 and strstr($error['file'],'eval()')){
            echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=gb2312\" />第<font color=red> ".$error['line']." </font>行代码产生错误：".$error['message']."<br>".$error['file']."<br><br>";
            preg_match_all("/.*?\n/",htmlentities($_PHP_CODE,ENT_COMPAT ,"UTF-8"),$htmlArray);
            $htmlArray=$htmlArray[0];
            $outHtml="<code style='line-height:20px;font-size:14px'>";
            foreach($htmlArray as $key=>$code){
                $lineNumber=$key+1;
                if($lineNumber==$error['line']){
                    $outHtml.='<span style="background:yellow;padding:2px; ">'.sprintf("%03d", $lineNumber).'.</span><span style="padding: 5px;background:yellow;border-left-color:rgb(0,200,0);border-left-width:2px;border-left-style:solid">'.$code.'<br></span>';
                }else{
                    $outHtml.='<span style="padding:2px; ">'.sprintf("%03d", $lineNumber).".</span><span style='padding: 5px;border-left-color:rgb(0,200,0);border-left-width:2px;border-left-style:solid;'>".$code."<br></span>";
                }
            }
            $outHtml.="</code>";
            echo $outHtml;
            echo "<script>parent.document.getElementById('debug').click();</script>";
			exit;
        }

        extract($this->params);
		$channel= $this->_getParam('channel', '');			
		if($channel!= $this->channel){			
			$this->view->error=1;
			echo "<script>alert('所编辑的模块已不属于当前频道,可能的原因是你在编辑的过程中切换了频道!');window.close();</script>";
			return false;
		}
        
        // old content
        $obj_content = $this->_pub->testObjectContentByOwner($oid, $pageid, $type, $owned);
        
        // new content
        $new_content = array(
            'oid' => $oid,
            'content' => $content,
            'owned' => $owned,
            'lastuid' => $this->_user['uid'],
            //'lastupdate' => TIME_NOW,
        );

        if ($owned == Publish::OBJ_OWNED_TEMPLATE) {
            $template = $this->_pub->getTemplate();            
            $new_content['pageid'] = $template->id;
            $new_content['type'] = Publish::OBJ_TYPE_TEMPLATE; 
			//$new_content['type'] = $type;
        } elseif ($owned == Publish::OBJ_OWNED_CHANNEL) {
            $new_content['pageid'] = 0;
            $new_content['type'] = $type;
        } else {
            $new_content['pageid'] = $pageid;
            $new_content['type'] = $type;
        }
        
		$rev_content= $new_content;
		unset($rev_content['lastuid']);
		$rev_content['uid']= $this->_user['uid'];

        // exists && range + : delete old
        // TODO: rev
        /*while ($obj_content && $owned < $obj_content->owned)  {
            $obj_content->delete();
            $obj_content = $this->_pub->getObjectContent($oid, $pageid);
        }*/
      
        if ($obj_content) {			
            $where = "oid = {$obj_content->oid} AND pageid = {$obj_content->pageid} AND type = {$obj_content->type} AND owned = {$obj_content->owned}";			
            $this->_obj_contents_table->update($new_content, $where);
			$this->_obj_revision_table->insert($rev_content);
        } else {
            $this->_obj_contents_table->insert($new_content);
			$this->_obj_revision_table->insert($rev_content);
        }
        
        // publish page
        //$pub = new Publish_Page($this->getChannelConfig());
		/*先不发布 */
        //if($type==2) $this->_pub->publish();
	    //$this->_pub->publish();
        
        /*
        $obj_content = $this->_pub->getObjectContent($oid, $pageid, $type);
        
        $this->view->content = $this->_pub->renderTemplate($obj_content->content);
        $this->view->object_content = $new_content['content'];
        */
    }
    
    public function clearAction()
    {
        // params
        extract($this->params);
        $content = $this->_getParam('content', '');   
		$channel= $this->_getParam('channel', '');			
		if($channel!= $this->channel){			
			$this->view->error=1;
			echo "<script>alert('所编辑的模块已不属于当前频道,可能的原因是你在编辑的过程中切换了频道!');window.close();</script>";
			echo $this->renderScript('modules/edit.phtml');
			return false;
		}
        
        $obj_content = $this->_pub->getObjectContentByOwner($oid, $pageid, $type, $owned);
        
        if ($obj_content) {
            $where = "oid = {$obj_content->oid} AND pageid = {$obj_content->pageid} AND type = {$obj_content->type} AND owned = {$obj_content->owned}";
            $this->_obj_contents_table->delete($where);
        }
        
        $this->_pub->publish();
        
        echo $this->renderScript('modules/edit.phtml');
        /*
        $obj_content = $this->_pub->getObjectContent($oid, $pageid, $type);
        
        $this->view->content = $obj_content ? $this->_pub->renderObject($obj_content->content) : '';
        $this->view->object_content = '';
        */
    }
}
