<?php
require_once 'Zend/Db/Table.php';
class Column extends Zend_Db_Table
{
	private $db;
	protected $_name='column';
	public $base_url;
	public $base_path;

	public function __construct($db)
	{
		$this->db= $db;
		parent::__construct();	
	}

	public function getList($where, $start=0,$offset=0){
		$sql="SELECT * FROM `column` WHERE $where ORDER BY id DESC";			
		if($offset > 0) $sql.=" limit ".$start.", ".$offset;		
		$data= $this->db->fetchAll($sql);
		//foreach($data as &$var){
			//$var['postdate']= date('Y-m-d H:i:s', $var['postdate']);			
		//}
		return $data;
	}

	public function Count($where){
		$sql="SELECT COUNT(*) AS nums from `column` WHERE $where";
		$data= $this->db->fetchAll($sql);
		return $data[0]['nums'];
	}

	public function delete($where){
		if(!$where)return;
		$sql="delete from `column` where $where";			
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function makeArray($str){		
		return	explode(",", trim($str));	
	}

	public function makeWhere($list, $row){
		if(!$list)return;
		if(strpos($list, ',')===FALSE)return " $row = '$list'";
		$list= "'".str_replace(",","','",$list)."'";
		return " $row IN ($list)";		
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

		makedir(dirname($filename));
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
}