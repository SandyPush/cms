<?php
require_once 'Zend/Db/Table.php';
class Qa extends Zend_Db_Table
{
	private $db;
	protected $_name='auquestion';

	public function __construct($db)
	{
		$this->db= $db;
		parent::__construct();	
	}

	public function getList($where, $start=0,$offset=0){
		$sql="SELECT * FROM auquestion WHERE  $where ORDER BY postdate DESC";			
		if($offset > 0) $sql.=" limit ".$start.", ".$offset;		
		$data= $this->db->fetchAll($sql);
		//foreach($data as &$var){
			//$var['postdate']= date('Y-m-d H:i:s', $var['postdate']);			
		//}
		return $data;
	}

	public function Count($where){
		$sql="SELECT COUNT(*) AS nums from auquestion WHERE $where";
		$data= $this->db->fetchAll($sql);
		return $data[0]['nums'];
	}

	public function delete($where){
		if(!$where)return;
		$sql="delete from auquestion where $where";			
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
		if(strpos($list, ',')===FALSE)return " $row = '$list'";
		$list= "'".str_replace(",","','",$list)."'";
		return " $row IN ($list)";		
	}	
}