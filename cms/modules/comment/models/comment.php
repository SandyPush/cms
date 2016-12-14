<?php

class Comment
{
	private $db;
	public function __construct($db)
	{
		$this->db= $db;;
	}
	public function __destruct()
	{
	}

	public function getCommentList($ctypeid, $tid, $author, $keyword, $page, $perpage){
		$add= "";	
		$start= ($page - 1) * $perpage;
		//if(!$ctypeid)return;
		if($ctypeid) $add.= " and a.ctypeid IN($ctypeid)";
		if($tid) $add.= " and a.tid= '$tid'";
		if($author) $add.= " and a.author= '$author'";
		if($keyword) $add.= " and INSTR(a.message,'$keyword')> 0";
		$sql="select a.*,(select url from news where tid= a.tid and ctypeid=a.ctypeid limit 1) url,(select title from news where tid= a.tid and ctypeid=a.ctypeid limit 1) title from comment a where a.id>0 $add and  a.audited!=2 order by a.id desc limit $start, $perpage";	
		$data= $this->db->fetchAll($sql);	
		foreach($data as $k=> $v)$data[$k]['time']= date('Y-m-d H:i:s',$v['postdate']);		
		return $data;
	}

	public function delComment($ids){
		$sql="update comment  set audited='2' where id in($ids)";	
		if($this->db->query($sql)){
			return true;		
		}else{
			return false;
		}
	}

	public function getCommentById($id){
		$sql="select tid, ctypeid as ctype, location from comment where id='$id' limit 1";		
		$row= $this->db->fetchAll($sql);		
		return $row[0];
	}

	public function getCount($ctypeid,$tid, $author, $keyword){
		//if(!$ctypeid)return;
		$add= "";	
		if($ctypeid) $add.= " and ctypeid IN($ctypeid)";
		if($tid) $add.= " and tid= '$tid'";
		if($author) $add.= " and author= '$author'";
		if($keyword) $add.= " and message like '%$keyword%'";
		$sql="select count(*) as num from comment a where 1 $add and a.audited!=2 order by a.id desc";		
		$row= $this->db->fetchAll($sql);		
		return $row[0]['num'];
	}
}