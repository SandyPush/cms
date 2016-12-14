<?php
class Category extends Zend_Db_Table
{
	const TABLE = 'ss_categories';
	protected $_name='ss_categories';
	private $db;
	
	public function __construct($db)
	{
		$this->db= $db;	
		parent::__construct();	
	}

	public function getOne($cid)
	{
		$select=$this->db->select();
		$select->from(self::TABLE,'*')
			   ->where('cid=?',$cid)
			   ->limit(1);
		$sql=$select->__toString();
		$result=$this->db->fetchRow($sql);
		return $result;     
	}
	
	//添加类别
	public function add($data)
	{	
		$this->db->insert(self::TABLE, $data);
		return $this->db->lastInsertId();
	}
	//修改类别
	public function modify($data, $cid)
	{
		$this->db->update(self::TABLE, $data, "cid='".$cid."'");
	}
	//隐藏或者显示类别
	public function delete($cid, $status=0)
	{
		$this->db->update(self::TABLE,array('status' => $status), 'cid='.$cid);
	}

	//删除类别
	public function remove($cid)
	{
		if(!$cid)return;
		$this->db->query("delete from ss_associate  where k_id in(select k_id from ss_keyword where cid='$cid')");
		$this->db->query("delete from ss_keyword where cid='$cid'");
		$this->db->query("delete from ss_categories where cid='$cid'");
	}
	//按级别获取分类列表
	public static function getAllList($db, $status=null, $channel= null)
	{
		$where= $status? " and a.status= '$status'": "";	
		$where.= $channel? " and a.channel= '$channel'": "";	
		return $db->fetchAll('SELECT * FROM '.self::TABLE.' a WHERE 1 '.$where.' order by `order` ASC');
	}

	//刷新类别级别及排序
	public static function refresh($db, $parent_cid=0, $level=0)
	{
		static $order=0;
		$stmt=$db->query('SELECT * FROM '.self::TABLE.' a WHERE 1 AND a.parent='.$parent_cid);
		while($row= $stmt->fetch())
		{
			$db->update(self::TABLE,array('level'=>$level,'order'=>$order),'cid='.$row['cid']);
			$order++;
			$arr= self::refresh($db, $row['cid'],($level+1));
		}
	}

	//获取用于select的列表
	public static function getOptions($db, $firstOption='请选择分类', $status=0, $channel= null)
	{
		$options= array(0 => $firstOption);
		$all_list=self::getAllList($db,$status, $channel);
		foreach($all_list as $value)
		{
			$options[$value['cid']]= str_repeat('---',$value['level']).$value['name'].'('.$value['cid'].')';
		}
		return $options;
	}	
}