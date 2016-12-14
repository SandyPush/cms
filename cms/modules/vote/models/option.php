<?php

class Option
{
	private $db;
	public function __construct($db)
	{
		$this->db= $db;;
	}
	public function __destruct()
	{
	}

	public function getOptionList($pid){	
		$sql="select * from `option`  where pid='$pid' order by orderby Asc";		
		$data= $this->db->fetchAll($sql);	
		foreach($data as $k=> $v){
			switch($data[$k]['status']){
				case 0:$data[$k]['status']='正常';
					break;
				case 1:$data[$k]['status']='删除';
					break;			
			}
			switch($data[$k]['ctype']){
				case 0:$data[$k]['ctype']='单选';
					break;
				case 1:$data[$k]['ctype']='复选';
					break;			
			}
		}
		return $data;
	}

	public function getOption($oid){
		$sql="select * from `option` where oid='$oid'";			
		$data= $this->db->fetchAll($sql);		
		return $data[0];
	}

	public function updateOption($oid,$title,$orderby,$click,$other,$ctype,$status){
		$sql="update `option` set title='$title',orderby='$orderby',click='$click', other='$other',ctype='$ctype',status='$status' where oid='$oid'";	
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function createOption($pid,$title,$orderby,$click,$other,$ctype){
		$sql="insert into `option` (pid,title,orderby,status,click,other,ctype)values('$pid','$title','$orderby', '0', '$click', '$other', '$ctype')";		
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function delOption($ids){
		$sql="update `option` set status ='1' where oid in($ids)";	
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}
}