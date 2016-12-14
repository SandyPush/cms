<?php

class Problem
{
	private $db;
	public function __construct($db)
	{
		$this->db= $db;;
	}
	public function __destruct()
	{
	}

	public function getProblemList($vid){	
		$sql="select * from problem where vid='$vid' order by orderby Asc";			
		$data= $this->db->fetchAll($sql);	
		foreach($data as $k=> $v){
			switch($data[$k]['status']){
				case 0:$data[$k]['status']='正常';
					break;
				case 1:$data[$k]['status']='删除';
					break;			
			}
		}
		return $data;
	}

	public function getProblem($pid){
		$sql="select * from problem where pid='$pid'";			
		$data= $this->db->fetchAll($sql);		
		return $data[0];
	}

	public function updateProblem($pid,$title,$orderby,$status){
		$sql="update problem set title='$title',orderby='$orderby',status='$status' where pid='$pid'";		
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function createProblem($vid,$title,$orderby){
		$sql="insert into problem (vid,title,orderby,status)values('$vid','$title','$orderby', '0')";		
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function delProblem($ids){
		$sql="update problem set status ='1' where pid in($ids)";	
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}
}