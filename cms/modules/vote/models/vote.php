<?php

class Vote
{
	private $db;
	public function __construct($db)
	{
		$this->db= $db;;
	}
	public function __destruct()
	{
	}

	public function getVotetList($channel,$title,$starttime, $endtime, $status ,$user,$page,$perpage){
		$add= "";			
		$start= ($page - 1) * $perpage;
		if($channel) $add.= " and channel= '$channel'";
		if($title) $add.= " and title like'%$title%'";
		if($user) $add.= " and insertuser = '$user'";
		if($starttime) $add.= " and starttime > '$starttime'";
		if($endtime) $add.= " and endtime < '$endtime'";
		if($status) $add.= " and status = '$status'";
		$sql="select * from vote where 1 $add order by vid desc limit $start, $perpage";			
		$data= $this->db->fetchAll($sql);	
		foreach($data as $k=> $v){
			$data[$k]['starttime']= date('Y-m-d H:i:s',$v['starttime']);	
			$data[$k]['endtime']= date('Y-m-d H:i:s',$v['endtime']);
			switch($data[$k]['status']){
				case 1:$data[$k]['status']='正常';
					break;
				case 2:$data[$k]['status']='过期';
					break;
				case 3:$data[$k]['status']='关闭';
					break;
				case 4:$data[$k]['status']='停止';
					break;
				case 5:$data[$k]['status']='删除';
					break;
			}
		}
		return $data;
	}

	public function getVote($vid){
		$sql="select * from vote where vid='$vid'";			
		$data= $this->db->fetchAll($sql);		
		return $data[0];
	}

	public function updateVote($vid,$title,$starttime, $endtime, $status ,$user){
		$sql="update vote set title='$title',starttime='$starttime',endtime='$endtime',status='$status',edituser='$user' where vid='$vid'";		
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function createVote($channel,$title,$starttime, $endtime, $status ,$user){
		$sql="insert into vote(channel,title,starttime,endtime,status,insertuser)values('$channel','$title','$starttime', '$endtime', '$status' ,'$user')";			
		if($this->db->query($sql)){
			return $vid= $this->db->lastInsertId();					
		}else{
			return false;
		}
	}

	public function delVote($ids){
		$sql="update vote set status ='5' where vid in($ids)";	
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function getCount($channel,$title,$starttime, $endtime, $status ,$user){
		$add= "";	
		if($channel) $add.= " and channel= '$channel'";
		if($title) $add.= " and title like'%$title%'";
		if($user) $add.= " and insertuser = '$user'";
		if($starttime) $add.= " and starttime > '$starttime'";
		if($endtime) $add.= " and endtime < '$endtime'";
		if($status) $add.= " and status = '$status'";
		$sql="select count(*) as num from vote where 1 $add order by vid desc";		
		$row= $this->db->fetchAll($sql);		
		return $row[0]['num'];
	}
}