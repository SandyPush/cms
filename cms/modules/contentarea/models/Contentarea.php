<?php
require_once 'Zend/Db/Table.php';
class Contentarea extends Zend_Db_Table{
	public $db;
	public $error = '';
	protected $_name='contentarea';

	public function __construct($db){
		$this->db=$db;
		parent::__construct();		
	}

	private function getSqlData($sql){
		$query=$this->db->query($sql);
		$result=$query->fetchAll();
		return $result;
	}

	public function Count($oid){
		$sql="SELECT COUNT(*) AS nums from contentarea WHERE oid='$oid' AND pid=''";
		$result=$this->getSqlData($sql);
		return $result[0]['nums'];
	}

	public function getList($oid, $start=0,$offset=0){
		$sql="SELECT * FROM  contentarea  WHERE oid='$oid'  AND pid='' ORDER BY aid ASC";
		if($offset > 0) $sql.=" limit ".$start.", ".$offset;
		$result=$this->getSqlData($sql);
		return $result;
	}

	public function insert(array $data){         
        if ($this->fetchRow("name = '$data[name]' AND oid= '$data[oid]' AND pid= '$data[pid]'")) {
            $this->error = "对不起, 你提交的名为'$data[caption]'的属性值在数据库内存在相同的name,oid,pid, 建议修改其ID值,即name字段.";			
            return false;
        }            
        
        return parent::insert($data);
    }

	public function uploadfile($tmp_name, $filename) {     
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
?>