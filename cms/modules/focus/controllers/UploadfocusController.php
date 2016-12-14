<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'page/models/Page.php';
require_once MODULES_PATH . 'focus/models/UploadFocus.php';
require_once MODULES_PATH . 'category/models/Category.php';


class Focus_UploadfocusController extends BaseController
{
    protected $_db;
    protected $_focus_table;
	private $uploadtype = 'gif, jpg, jpeg, png, swf, htm, html, xml, css, js,zip,tpl';

    public function init()
    {
        $db = $this->getChannelDbAdapter();
        $this->_db = $db;
        Zend_Db_Table::setDefaultAdapter($db);  
        $this->_focus_table = new FocusTable();
		$this->_focus_table->uploadtype= $this->uploadtype;
		$this->_focus_table->base_path= $this->getChannelConfig($this->channel)->path->published;
    	$this->_focus_table->base_url= $this->getChannelConfig($this->channel)->url->published;
    }

  
    public function createAction()
    {
        $this->_checkPermission('focus', 'add');
        $this->view->headTitle('上传方式创建专题');
        $this->view->headScript()->appendFile('/scripts/jquery/ui.js');
        $this->view->headLink()->appendStylesheet('/scripts/jquery/datepicker.css');        
    
    	#分类列表
    	$this->view->category_options=Category::getOptions($this->_db,'顶级类别');
                
		$this->view->focus = array('url'=> date("YmdHis"));
        if ($this->isPost())
        {
            $focus = array(
                'name' => $this->_getParam('name'),
                'title' => '',
                'keywords' => '',
                'description' => $this->_getParam('desc'),
                'url' => $this->_getParam('url'),
                'islink' => 0,
                'template' => '',
                'cid' => (int) $this->_getParam('cid'),
                'image' => $this->_getParam('image'),
                'star' => $this->_getParam('star',0),
                'star_image' => $this->_getParam('star_image'),
            //'star' => $this->_getParam('star'),
                'starttime' => $this->_getParam('starttime').date(' H:i:s'),
                'endtime' => $this->_getParam('endtime').date(' H:i:s'),
                'uid' => $this->_user['uid'], // todo
                'status' => $this->_getParam('status'),
            );
        	$this->view->focus = $focus;
            #验证url是否已存在
            $Page=new PageTable($this->_db);
            if($Page->checkUrlExist($focus['url']))
            {
                $this->error('和栏目URL冲突');
                return false;
            }
            if($this->_focus_table->checkUrlExist($focus['url']))
            {
                $this->error('和其他专题URL冲突');
                return false;
            }
            if (false === $this->_focus_table->insert($focus)) {
                $this->error($this->_focus_table->error);  
                return false;
            }
			 makedir($this->_focus_table->base_path.'ztm/'.$focus['url']);
            $this->flash('专题创建成功', '/focus/focus/');
        }
    }
    
    public function editAction()
    {
        $this->_checkPermission('focus', 'edit');
        $fid = (int) $this->_getParam('fid', 0);
        if (!$focus = $this->_focus_table->find($fid)->current()) {
            $this->error('请指定专题');
        }
    
        $focus = $focus->toArray();
        $focus['starttime'] = substr($focus['starttime'], 0, 10);
        $focus['endtime'] = substr($focus['endtime'], 0, 10);
        $this->view->headTitle('修改专题');
        $this->view->headScript()->appendFile('/scripts/jquery/ui.js');
        $this->view->headLink()->appendStylesheet('/scripts/jquery/datepicker.css');

        $this->view->focus = $focus;
        
    	#分类列表
    	$this->view->category_options=Category::getOptions($this->_db,'顶级类别');
        if ($this->isPost())
        { 
            
            $focus_new = array(
                'fid' => $fid,
                'name' => $this->_getParam('name'),
                'title' => '',
                'keywords' => '',
                'description' => $this->_getParam('desc'),
                'url' => $this->_getParam('url', ''),
                'islink' => 0,
                'image' => $this->_getParam('image'),
                'star' => $this->_getParam('star'),
                'star_image' => $this->_getParam('star_image'),
                'template' => '',
                'cid' => (int) $this->_getParam('cid'),
                'starttime' => $this->_getParam('starttime').date(' H:i:s'),
                'endtime' => $this->_getParam('endtime').date(' H:i:s'),
                'status' => $this->_getParam('status'),
            );
            #验证url是否已存在
            $Page=new PageTable($this->_db);
            if($Page->checkUrlExist($focus_new['url']))
            {
                $this->error('和栏目URL冲突');
                return false;
            }
            if($this->_focus_table->checkUrlExist($focus_new['url'],$fid))
            {
                $this->error('和其他专题URL冲突');
                return false;
            }
            
            if (false === $this->_focus_table->edit($focus_new, "fid = $fid")) {
				$this->error($this->_focus_table->error);           
                return false;
            }
            makedir($this->_focus_table->base_path.'ztm/'.$focus['url']);
            $this->flash('专题修改成功', '/focus/focus/');
        }
    }

	public function listAction()
    {
        $this->_checkPermission('focus', 'edit');
        $fid = (int) $this->_getParam('fid', 0);	
        if (!$focus = $this->_focus_table->find($fid)->current()) {
            $this->error('请指定专题');
        }
        $focus = $focus->toArray();		
		$directory= $this->_getParam('directory', $focus['url']);
		$this->view->files = $this->_focus_table->getFileList($focus['fid'], $directory);
		$this->view->directory= $directory;
		$this->view->fid= $fid;
        $this->view->parent= substr($directory,0, strrpos($directory, "/"));			
    }
	
	public function downloadAction(){
		$fid = (int) $this->_getParam('fid', 0);
		$path = trim($this->_getParam('path'));
		$filename = trim($this->_getParam('filename'));
        if (!$focus = $this->_focus_table->find($fid)->current()) {
            $this->error('请指定专题');
        }
		$filename = str_replace('/', '', $filename);
		$filename = str_replace("\\", '', $filename);
		$htmfile = $path;
		$pos = strpos($htmfile, $this->_focus_table->base_path);		
		if ($pos === false) {
			header("HTTP/1.1 403 Bad Request");
			exit();
		}

		if (!file_exists($htmfile)) {
			header("HTTP/1.1 404 Not Found");
			exit();
		}
		$content_len = sprintf("%u", filesize($htmfile));
		function is_ie() {
			$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
			if ((strpos($useragent, 'opera') !== false) ||
				(strpos($useragent, 'konqueror') !== false)) return false;
			if (strpos($useragent, 'msie ') !== false) return true;
			 return false;
		}
		if (is_ie()) {
			// leave $filename alone so it can be accessed via the hook below as expected.
			$filename = rawurlencode($filename);
		}
		while(ob_get_length() !== false) @ob_end_clean(); 
		header('Pragma: public');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		header('Content-Transfer-Encoding: binary'); 
		header('Content-Encoding: none');
		header('Content-type: text/html');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header("Content-length: $content_len");
		readfile($htmfile);
		exit;
	}
	
	public function uploadfileAction(){
		$fid = (int) $this->_getParam('fid', 0);
		$directory = trim($this->_getParam('directory'));
  
        $path = $this->_focus_table->base_path.'ztm/';

        if ($_FILES['file']['tmp_name']) {
            $filename = $_FILES['file']['name'];
            $extension = strtolower(substr(strrchr($filename, "."), 1));
            // 对 zip 文件单独处理
            if ($extension == 'zip') {
                $filename = $path . "$directory/" . $filename;
                !(UploadFile($_FILES['file']['tmp_name'], $filename)) && $this->error('focus_file_cannot_upload',"/focus/uploadfocus/list/fid/{$fid}/?directory={$directory}",1);
                // 解压后删除
                $this->_focus_table->unzip($fid, $filename, $path, $directory);
                rm($filename);
            }else {
                !preg_match('/(^|\s|,)' . $extension . '(\s|,|$)/i', $this->uploadtype) && $this->error('focus_file_type_wrong',"/focus/uploadfocus/list/fid/{$fid}/?directory={$directory}",1);

                !(UploadFile($_FILES['file']['tmp_name'], $path . "$directory/" . $filename)) && $this->error('focus_file_cannot_upload',"/focus/uploadfocus/list/fid/{$fid}/?directory={$directory}",1);              
            }
			$this->msg('上传成功',"/focus/uploadfocus/list/fid/{$fid}/?directory={$directory}"); 
        }
		$this->msg('上传失败',"/focus/uploadfocus/list/fid/{$fid}/?directory={$directory}"); 
		exit;
	}

	public function removefileAction(){
		$fid = (int) $this->_getParam('fid', 0);
		$path = trim($this->_getParam('path'));
		$filename = trim($this->_getParam('filename'));
        if (!$focus = $this->_focus_table->find($fid)->current()) {
            $this->error('请指定专题');
        }
		$filename = str_replace('/', '', $filename);
		$filename = str_replace("\\", '', $filename);
		$htmfile = $path;
		$pos = strpos($htmfile, $this->_focus_table->base_path);		
		if ($pos === false) {
			header("HTTP/1.1 403 Bad Request");
			exit();
		}
		if(is_dir($path)){
			rm($path);	
			$this->msg('',"/focus/uploadfocus/list/fid/{$fid}/?directory={$directory}"); 
		}else{
			if(unlink($path)){
				$this->msg('删除成功',"/focus/uploadfocus/list/fid/{$fid}/?directory={$directory}"); 
			}else{
				$this->msg('删除失败',"/focus/uploadfocus/list/fid/{$fid}/?directory={$directory}"); 
			}
		}
		exit;
	}

	public function archiveAction(){
		$fid = (int) $this->_getParam('fid', 0);		
        if (!$focus = $this->_focus_table->find($fid)->current()) {
            $this->error('请指定专题');
        }
		$focus = $focus->toArray();	
		$dirname= (array)($this->_focus_table->base_path.'ztm/'.$focus['url']);		
        $data= $this->_focus_table->ZipTemplate($dirname);
		header('Content-type: application/octet-stream');
		header('Accept-Ranges: bytes');
		header('Accept-Length: '.strlen($data));
		header('Content-Disposition: attachment;filename='.$focus['url'].'_Files.zip');
		echo $data;			
		exit;
	}

	public function error($str,$url,$flag=0){
		 echo "<script>";
		 if($str)echo "alert('{$str}');";
		 if($url)echo "window.location.href='{$url}'";
		 echo "</script>"; 
		 if($flag)exit;
	}

	public function msg($str,$url){
		$this->error($str,$url);		
	}
}
