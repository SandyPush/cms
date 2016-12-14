<?php
class Keyword extends Zend_Db_Table
{
	protected $_name='ss_keyword';
	private $db;
	
	public function __construct($db)
	{
		$this->db= $db;	
		parent::__construct();	
	}

	public function getOne($where)
	{	
		$result=$this->db->fetchRow("select a.*,(select group_concat(a_word) from ss_associate where k_id= a.k_id group by k_id) as a_word from ss_keyword a where 1 $where");
		return $result;     
	}

	public function getAword($id)
	{	
		$result=$this->db->fetchOne("select group_concat(a_word) from ss_associate where k_id= '$id' group by k_id");
		return $result;     
	}

	public function getChannel($id)
	{	
		$result=$this->db->fetchOne("select channel from ss_keyword where k_id= '$id'");
		return $result;     
	}

	public function addAword($data)
	{	
		$this->db->insert('ss_associate', $data);
		return $this->db->lastInsertId();
	}	

	
	public function delAword($kid, $aword)
	{	
		return $this->db->query("delete from ss_associate where k_id='$kid' and a_word='$aword'");		
	}	

	public function add($data)
	{	
		$this->db->insert('ss_keyword', $data);
		return $this->db->lastInsertId();
	}

	public function modify($data, $id)
	{
		$this->db->update('ss_keyword', $data, "k_id='".$id."'");
	}

	public function delete($id)
	{
		$this->db->query("delete from ss_keyword where k_id='$id'");
	}

	public function clear($id)
	{
		$this->db->query("delete from ss_associate where k_id='$id'");
	}

	public function getAllList($limit= 20, $page= 1, $cid=null, $channel= null, $where= null)
	{
		$where.= $cid? " and a.cid= '$cid'": "";	
		$where.= $channel? " and a.channel= '$channel'": "";
		$page= max($page, 1);
		$start= $limit* ($page-1);
		$lm= " limit $start, $limit";
		$sql= "SELECT a.*,(select name from ss_categories where cid= a.cid) as cname,(select group_concat(a_word) from ss_associate where k_id= a.k_id group by k_id) as a_word FROM ss_keyword a WHERE channel!='' $where order by  k_id desc $lm";			
		return $this->db->fetchAll($sql);
	}

	public function count($cid= null, $channel= null, $where= null){
		$where.= $cid? " and a.cid= '$cid'": "";	
		$where.= $channel? " and a.channel= '$channel'": "";		
		$sql= "SELECT count(*) FROM ss_keyword a WHERE channel!='' $where order by hits desc";			
		return $this->db->fetchOne($sql);
	}
}