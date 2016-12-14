<?php
require_once 'Zend/Db/Table.php';
class Debate extends Zend_Db_Table
{
	private $db;
	protected $_name='debate';
	public $base_url;
	public $base_path;

	public function __construct($db)
	{
		$this->db= $db;
		parent::__construct();	
	}

	public function __destruct()
	{
	}

	public function getList($channel, $start=0,$offset=0){
		$sql="SELECT * FROM debate WHERE channel='$channel' ORDER BY id DESC";
		if($offset > 0) $sql.=" limit ".$start.", ".$offset;
		$data= $this->db->fetchAll($sql);
		foreach($data as &$var){
			$var['level']= $var['level']==1?'首页推荐':' ---- ';
			$var['status']= $var['status']==0?'正常':'已删除';
		}
		return $data;
	}

	public function Count($channel){
		$sql="SELECT COUNT(*) AS nums from debate WHERE channel='$channel'";
		$data= $this->db->fetchAll($sql);
		return $data[0]['nums'];
	}

	public function delete($id){
		$sql="update debate set status ='1' where id ='$id'";	
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function publish($data){
		$sql="update debate set status ='1' where id ='$id'";	
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function upload($file) {   
		if(!file)return;
		$ext=explode(".", $file['name']);	
		$fileext=strtolower(end($ext));		
		if(($fileext!='jpg' && $fileext!='gif' && $fileext!='png')|| $file['size']==0)return;		
		
		$time=date('Y/m/d',time());
		$filename=substr(md5($file['name']),0,10).'_'.time().rand(1,10).'.'.$fileext;
		$filedir=$this->base_path.'/'.$time.'/';
		$filedir=str_replace(array("//","\\","\\\\"),'/',$filedir);

		$fileurl=$this->base_url.'/'.$time.'/'.$filename;
		$filename=$filedir.$filename;	
		$tmp_name=$file['tmp_name']; 

		$this->makedir(dirname($filename));
		$url= $this->upfile($tmp_name, $filename)?$fileurl:'';
		return $url;
	}

	public function upfile($tmp_name, $filename) {     
        if(strpos($filename,'..') !== false || eregi("\.php$", $filename)) {
            return false;
        }    
        if(function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name, $filename)) {
            @chmod($filename, 0777);
            return true;
        }elseif(@copy($tmp_name, $filename)) {
            @chmod($filename, 0777);
            return true;
        }elseif(is_readable($tmp_name)) {
            file_put_contents($filename, file_get_contents($tmp_name));
            if(file_exists($filename)){
                @chmod($filename, 0777);
                return true;
            }
        }
        return false;
    }

	public function makedir($dir, $mode = 0755) {
		if (is_dir($dir) || @mkdir($dir, $mode)) return true;
		if (!$this->makedir(dirname($dir), $mode)) return true;
		return @mkdir($dir, $mode);
	}
}