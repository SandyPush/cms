<?php

class Category
{
	const TABLE = 'categories';//主数据表
	private $db;//数据库连接
	private $error_message = "";//错误信息
	private $id = 0;//cid类别ID
	private $name = "";//类别名称
	private $publisher_dir = "";//发布路径
	private $parent_id = 0;//父级类别ID
	private $type="";//类型
	private $channel_id=0;//绑定栏目ID
	private $status = 0;//状态
	
	
	public function __construct($db,$cid=NULL)
	{
		$this->db=$db;
		if(!empty($cid))
		{
			$this->init($cid);
		}
	}
	public function __destruct()
	{
	}
	public function init($cid)
	{
		$select=$this->db->select();
		$select->from(self::TABLE,'*')
			   ->where('cid=?',$cid)
			   ->limit(1);
		$sql=$select->__toString();
		$result=$this->db->fetchRow($sql);
        if(empty($result))return false;//通过判断errorMessage为空且返回false确定取得数据为空
        $this->setId($result['cid']);
        $this->setName($result['name']);
        $this->setPublisherDir($result['pub_dir']);
        $this->setParentId($result['parent']);
        $this->setType($result['type']);
        $this->setChannelId($result['bind_id']);
        $this->setStatus($result['status']);
	}
	//设置类别ID
	public function setId($id)
	{
		$this->id=$id;
	}
	//获取类别ID
	public function getId()
	{
		return $this->id;
	}
	//设置类别名称
	public function setName($name)
	{
		$this->name=$name;
	}
	//获取类别名称
	public function getName()
	{
		return $this->name;
	}
	//设置发布路径
	public function setPublisherDir($dir)
	{
		$this->publisher_dir=$dir;
	}
	//获取发布路径
	public function getPublisherDir()
	{
		return $this->publisher_dir;
	}
	//设置父类别ID
	public function setParentId($parent_id)
	{
		$this->parent_id=$parent_id;
	}
	//获取父类别ID
	public function getParentId()
	{
		return $this->parent_id;
	}
	//设置类型
	public function setType($type)
	{
		$this->type=$type;
	}
	//获取类型
	public function getType()
	{
		return $this->type;
	}
	//设置绑定栏目ID
	public function setChannelId($channel_id)
	{
		$this->channel_id=$channel_id;
	}
	//获取绑定栏目ID
	public function getChannelId()
	{
		return $this->channel_id;
	}
	//设置状态
	public function setStatus($status)
	{
		$this->status=$status;
	}
	//获取状态
	public function getStatus()
	{
		return $this->status;
	}
	//添加类别
	public function add()
	{
		$this->db->insert(self::TABLE,
			array('name' => $this->getName(),
				   'pub_dir' => $this->getPublisherDir(),
				   'parent' => $this->getParentId(),
				   'type' => $this->getType(),
				   'bind_id' => $this->getChannelId(),
				   'status' => $this->getStatus()));
		return $this->db->lastInsertId();
	}
	//修改类别
	public function modify()
	{
		$this->db->update(self::TABLE,
			array('name' => $this->getName(),
				  'parent' => $this->getParentId(),
				  'pub_dir' => $this->getPublisherDir(),
				  'type' => $this->getType(),
				  'bind_id' => $this->getChannelId()
			),
			"cid='".$this->getId()."'");
	}
	//删除类别
	public function delete()
	{
		$this->db->update(self::TABLE,array('status' => 0), 'cid='.$this->getId());
	}
	//隐藏类别
	public function hide()
	{
		$this->db->update(self::TABLE,array('status' => 2), 'cid='.$this->getId());
	}
	//恢复显示类别
	public function display()
	{
		$this->db->update(self::TABLE,array('status' => 1), 'cid='.$this->getId());
	}
	//获取分类列表
	public static function getList($db,$type=NULL,$parent=NULL,$level=NULL)
	{
		$select = $db->select();
		$select->from(self::TABLE,'*');
//		$select->where('status >=1');2009/10/16 修改为只取status=1的分类
		$select->where('status=1');
		if(isset($type))$select->where("type=?",$type);
		if(isset($parent))$select->where("parent=?",$parent);
		if(isset($level))$select->where("level=?",$level);
		$select->order("order ASC");
		$sql=$select->__toString();
		//echo "<br>sql = " . $sql . "<br>";
		$result=$db->fetchAll($sql);
		return $result;
	}
	//按级别获取分类列表
	public static function getAllList($db,$type=0)
	{
		$where="";
		if(!empty($type))$where.=" AND type=".$type;
		return $db->fetchAll('SELECT * FROM '.self::TABLE.' a WHERE a.status>=1'.$where.' order by `order` ASC');
	}
	#刷新类别级别及排序
	public static function refresh($db,$parent_cid=0,$level=0)
	{
		static $order=0;
		$stmt=$db->query('SELECT * FROM '.self::TABLE.' a WHERE a.status>=1 AND a.parent='.$parent_cid);
		while($row=$stmt->fetch())
		{
			$db->update(self::TABLE,array('level'=>$level,'order'=>$order),'cid='.$row['cid']);
			$order++;
			$arr2=self::refresh($db,$row['cid'],($level+1));
		}
	}
	//推送分类列表
	public static function listByLevel($db,$parent_cid=0)
	{
		$data=array();
		$data[$parent_cid]=$db->fetchAll('SELECT *,(SELECT COUNT(*) FROM '.self::TABLE.' WHERE parent=a.cid) childs FROM '.self::TABLE.' a WHERE status>=1 AND parent='.$parent_cid);
		if(empty($data[$parent_cid]))return $data;
		foreach($data[$parent_cid] as $value)
		{
			$data2=self::listByLevel($db,$value['cid']);
			if(empty($data2))continue;
			$data+=$data2;
		}
		return $data;
	}
	//获取用于select的列表
	public static function getOptions($db,$firstOption='请选择分类')
	{
		$options=array(0 => $firstOption);
		$all_list=self::getAllList($db,1);
		foreach($all_list as $value)
		{
			$options[$value['cid']]=str_repeat('---',$value['level']).$value['name'].'('.$value['cid'].')';
		}
		return $options;
	}
	//生成推送分类JS文件
	public static function makePushCategoryFile($db,$file)
	{
		$contents='<div class="chl" id="push_category_list" style="display:none">'."\n";
		$list_by_level=self::listByLevel($db);
		foreach($list_by_level as $key => $value)
		{
			if(empty($value))continue;
			$contents.='<div id="category_'.$key.'" style="display:none">'."\n";
				foreach($value as $row)
				{
					if($row['childs'] > 0) $gif='nolines_plus.gif';else $gif='nolines_minus.gif';
					$contents.='<span>'."\n";
					$contents.='<img src="/theme/default/image/'.$gif.'" dataSrc="category_'.$row['cid'].'" />'."\n";
					$contents.='<label><input type="checkbox" name="push_cid[]" value="'.$row['cid'].'" />'.$row['name'].'</label>'."\n";
					$contents.='</span>'."\n";
				}
			$contents.='</div>'."\n";
		}
		$contents.='</div>'."\n";
		file_put_contents($file,$contents);
	}

	//获取分类类型列表
	public function getTypeSelect()
	{
		return array(1=>'新闻',2=>'位置');
	}
	//获取父级分类信息
	public function getParent($id)
	{
		$sql="select * from categories a where cid=(select parent from categories where cid='".$id."')";
		$result=$this->db->fetchRow($sql);
		if(empty($result))return false;
		return $result;
	}
	//获取所有父级(即包括祖先级)分类信息,第一组数据为顶级分类
	public function getAllParents($id)
	{
		$result=array();
		$parent_info=$this->getParent($id);
		if(empty($parent_info))return $result;
		$result[]=$parent_info;
		$result=array_merge($this->getAllParents($parent_info['cid']),$result);
		return $result;
	}
	//获取所有父级(即包括祖先级)分类ID
	public function getAllParentsId($id,$self=false)
	{
		$allParents=$this->getAllParents($id);
		$ids=array();
		if($self)$ids=array($id);
		if(empty($allParents)) return $ids;
		foreach($allParents as $row) $ids[]=$row['cid'];
		return $ids;
	}
	//获取顶级分类ID
	public function topCid($cid)
	{
		$allParentsId=$this->getAllParentsId($cid);
		if(empty($allParentsId))return false;
		return $allParentsId[0];
	}
}
