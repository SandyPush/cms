<?php
require_once 'Zend/Db/Table.php';

class FocusTable extends Zend_Db_Table
{
    protected $_name = 'focus';
    protected $_primary = 'fid';
    public $error = '';
	public $base_url;
	public $base_path;
	public $uploadtype;
    private $_fields = array(
        'name' => '名称', 
		'description' => '简介',
        'url' => '目录',
        'starttime' => '开始时间',
        'endtime' => '结束时间'
    );   

    public function insert(array $data)
    {
        // starttime, endtime
        foreach (array('name','url','starttime', 'endtime') as $field) {
            if ('' === $data[$field]) {
                $this->error = $this->_fields[$field] . ' 不能为空';
                return false;
            }
        }
           
        if ($this->fetchRow("name = '$data[name]'")) {
            $this->error = '已存在以' . $data['name'] . '命名的专题';
            return false;
        }

        if (strtotime($data['starttime'] > strtotime($data['endtime']))) {
            $this->error = '开始时间不能大于结束时间';
            return false;
        }
        
        if (!Util::checkUrl($data['url'])) {
            $this->error = 'url地址不合法，请不要包含styles,scripts,images,img等关键字';
            return false;
        }
                
        /*
        if (!preg_match('/^https?:\/\//', $data['url'])) {
            $data['url'] = 'http://' . $data['url'];
        }
        */
                
        return parent::insert($data);
    }
    
    public function edit(array $data, $where)
    {
            // starttime, endtime
        foreach (array('name', 'url') as $field) {
            if ('' === $data[$field]) {
                $this->error = $this->_fields[$field] . ' 不能为空';
                return false;
            }
        }        
       
        if ($this->fetchRow("name = '$data[name]' AND fid != " . $data['fid'])) {
            $this->error = '已存在以' . $data['name'] . '命名的专题';
            return false;
        }

        if (strtotime($data['starttime'] > strtotime($data['endtime']))) {
            $this->error = '开始时间不能大于结束时间';
            return false;
        }
        
        if (!Util::checkUrl($data['url'])) {
            $this->error = 'url地址不合法，请不要包含styles,scripts,images,img等关键字';
            return false;
        }
                
        /*
        if (!preg_match('/^https?:\/\//', $data['url'])) {
            $data['url'] = 'http://' . $data['url'];
        }
        */
        
        unset($data['fid']);
        
        return parent::update($data, $where);
    }
    
    
    public function delete($fid)
    {
        return parent::update(array('status'=>0), 'fid='.$fid);
    }

    public function checkUrlExist($url,$id=NULL)
    {
        $select = $this->select()->from($this->_name, 'COUNT(*)')->where('url=?',$url);
        if(isset($id))$select->where('fid!='.$id);
        return $this->_db->fetchOne($select);
    }

	public function getFileList($focusid, &$directory) {  
        $dir = $this->base_path . "ztm/".$directory;
        $files = array();
        $files_img = "/theme/default/image/file/";
		if(!is_dir($dir))return;
        $d = dir($dir);
        while ($filename = $d->read()) {
            if($filename == '.' || $filename == '..') continue;
            // 目录处理
            if(is_dir($dir . '/' . $filename)) {
                // data 目录是内部隐藏的，不处理。
                if (($directory == '') && ($filename == 'data')) continue;				
                $files[] = array (
                    'type' => 'dir',
                    'name' => $filename,
                    'icon' => $files_img . "dir.gif",
                    'size' => '&nbsp;',
                    'time' => '&nbsp;',
                    'url' => "/focus/uploadfocus/list/fid/{$focusid}/?directory=$directory/$filename",
					'path' => $this->base_path."ztm/$directory/$filename",
                );
            }
            // 文件处理
            else {
                $fileext = strtolower(end(explode('.', $filename)));
                $icon = $files_img . $fileext . ".gif";
                !file_exists($icon) && $icon = $files_img . "none.gif";
                $size = filesize($dir . '/' . $filename);
                $size = ceil($size / 1024);
                $filetime = filemtime($dir . '/' . $filename);
                $filetime = date("y-m-d H:i:s", $filetime);
                $files[] = array(
                    'type' => $fileext,
                    'name' => $filename,
                    'icon' => $icon,
                    'size' => $size." kB",
                    'time' => $filetime,
                    'url' => $this->base_url."/ztm/$directory/$filename",
					'path' => $this->base_path."ztm/$directory/$filename",
                );
            }
        }
        $d->close();
        usort($files, array('FocusTable', 'filecompare'));
        return $files;
    }

	public function filecompare($a, $b) {
        // 目录排首位
        if($a['type'] == 'dir' && $b['type'] == 'dir') {
            return strcasecmp($a['name'], $b['name']);
        }
        elseif ($a['type'] == 'dir' && $b['type'] != 'dir') {
            return -1;
        }
        elseif ($a['type'] != 'dir' && $b['type'] == 'dir') {
            return 1;
        }
        // htm 文件排次位
        elseif($a['type'] == 'htm' && $b['type'] == 'htm') {
            return strcasecmp($a['name'], $b['name']);
        }
        elseif ($a['type'] == 'htm' && $b['type'] != 'htm') {
            return -1;
        }
        elseif ($a['type'] != 'htm' && $b['type'] == 'htm') {
            return 1;
        }
        // html 文件排第三位
        elseif($a['type'] == 'html' && $b['type'] == 'html') {
            return strcasecmp($a['name'], $b['name']);
        }
        elseif ($a['type'] == 'html' && $b['type'] != 'html') {
            return -1;
        }
        elseif ($a['type'] != 'html' && $b['type'] == 'html') {
            return 1;
        }
        // 其它文件按文件名排序
        else {
            return strcasecmp($a['name'], $b['name']);
        }
    }

	public function unzip($focusid, $file, $path, $directory) {
        $zip = zip_open($file);
        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
                if (zip_entry_filesize($zip_entry) > 0) {
                    $entry_name = zip_entry_name($zip_entry);
                    $complete_path = $path . "$directory/" . dirname($entry_name);
                    $complete_name = $path . "$directory/" . $entry_name;
                    // 如果目录已经存在，并且不是 data 目录，则直接解压缩
                    if(file_exists($complete_path)) {
                        $this->unzip_file($focusid, $path, $directory, $complete_name, $entry_name, $zip, $zip_entry);
                    }
                    else { 
                        // 建立目录
                        $tmp = '';
                        foreach(explode('/' , $complete_path) as $k) {
                            $tmp .= $k . '/';
                            if(!file_exists($tmp)) {
                                mkdir($tmp, 0777); 
                            }
                        }
                        // 解压缩
                        $this->unzip_file($focusid, $path, $directory, $complete_name, $entry_name, $zip, $zip_entry);
                    }
                }
            }
            zip_close($zip);
        }
    }

	/* 内部函数，解压缩具体文件 */
    public function unzip_file($focusid, $path, $directory, $complete_name, $entry_name, $zip, $zip_entry) {
        // 获取扩展名
        $extension = strtolower(substr(strrchr($complete_name, "."), 1));
        // 过滤不允许上传的类型
        if (preg_match('/(^|\s|,)' . $extension . '(\s|,|$)/i', $this->uploadtype)) {
            if (zip_entry_open($zip, $zip_entry, "r")) {
                file_put_contents($complete_name, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
                zip_entry_close($zip_entry);
            }  
        }
    }
	public function ZipTemplate($filelist){
		require ROOT_PATH . 'core/utilities/class_phpzip.php';				
		if(is_array($filelist)){
			$faisunZIP = new PHPzip;
			$time= time();		
			if($faisunZIP->startfile(DATA_PATH."{$time}.zip")){
				$filenum = 0;				
				foreach($filelist as $file){
					$filenum += $this->listfiles($faisunZIP, $file);
				}
				$faisunZIP->createfile();	
				$content= file_get_contents(DATA_PATH."{$time}.zip", $content);				
			}
		}
		@unlink(DATA_PATH."{$time}.zip");	
		return $content;
	}

	public function listfiles($faisunZIP, $dir="."){	
		$sub_file_num = 0;
		if(is_file("$dir")){
		  if(realpath($faisunZIP ->gzfilename)!=realpath("$dir")){
			$faisunZIP-> addfile(implode('',file("$dir")),"$dir");
			return 1;
		  }
			return 0;
		}
		
		$handle=opendir("$dir");
		while ($file = readdir($handle)) {
		   if($file=="."||$file=="..")continue;
		   if(is_dir("$dir/$file")){
			 $sub_file_num += $this->listfiles($faisunZIP, "$dir/$file");
		   }
		   else {
		   	   if(realpath($faisunZIP ->gzfilename)!=realpath("$dir/$file")){
			     $faisunZIP -> addfile(implode('',file("$dir/$file")),"$dir/$file");
				 $sub_file_num ++;
				}
		   }
		}
		closedir($handle);
		if(!$sub_file_num) $faisunZIP -> addfile("","$dir/");
		return $sub_file_num;
	}
}