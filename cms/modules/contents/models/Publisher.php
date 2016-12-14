<?php

class Publisher
{
	const TABLE = "article_source";
	private $db;//数据库连接
	private $error_message = "";//错误信息
	private $id = 0;//文章来源ID
	private $name = "";//文章来源名称
	private $icon = "";//文章来源图标
	private $url = "";//文章来源url
	
	public function __construct($db,$id=NULL)
	{
		$this->db=$db;
		if(!empty($id))
		{
			$this->init($id);
		}
	}
	public function init($id)
	{
		$sql="select * from article_source where id='".$id."'";
		$result=$this->db->fetchRow($sql);
        if(empty($result))return false;//通过判断errorMessage为空且返回false确定取得数据为空
        $this->setId($result['id']);
        $this->setName($result['name']);
        $this->setIcon($result['img']);
        $this->setUrl($result['url']);
	}
	//设置文章来源ID
	public function setId($id)
	{
		$this->id=$id;
	}
	//获取文章来源ID
	public function getId()
	{
		return $this->id;
	}
	//设置文章来源名称
	public function setName($name)
	{
		$this->name=$name;
	}
	//获取文章来源名称
	public function getName()
	{
		return $this->name;
	}
	//设置图标
	public function setIcon($icon)
	{
		$this->icon=$icon;
	}
	//获取图标
	public function getIcon()
	{
		return $this->icon;
	}
	//设置文章来源url
	public function setUrl($url)
	{
		$this->url=$url;
	}
	//获取文章来源url
	public function getUrl()
	{
		return $this->url;
	}
	//添加文章来源
	public function add()
	{
		$this->db->insert(self::TABLE,
			array('name' => $this->getName(),
				  'img' => $this->getIcon(),
				  'url' => $this->getUrl(),
			)
		);
		$insertId=$this->db->lastInsertId();
		$this->setId($insertId);
		return $insertId;
	}
	//修改文章来源
	public function modify()
	{
		$array=array(
			'name'=>$this->getName(),
			'img'=>$this->getIcon(),
			'url'=>$this->getUrl(),
		);
		$this->db->update(self::TABLE,$array,"id='".$this->getId()."'");
	}
	//删除文章来源
	public function delete()
	{
		$sql="delete from article_source where id='".$this->getId()."'";
		$this->db->query($sql);
	}
	//获取文章来源列表
	public function getList()
	{
		$sql="select * from article_source";
		$result=$this->db->fetchAll($sql);
		return $result;
	}
	//获取生成select文章来源列表
	public function getSelect()
	{
		$result=array(0=>"文章来源");
		$list=$this->getList();
		if(empty($list))return false;
		foreach($list as $value)
		{
			$result[$value['id']]=$value['name'];
		}
		return $result;
	}
	public function getIdByName($name)
	{
		if(!$name)return;
		$id=$this->db->fetchOne("SELECT id FROM ".self::TABLE." WHERE name='".trim($name)."'");
		if(empty($id))
		{
			$this->setName($name);
			$this->add();
			$id=$this->getId();
		}
		return $id;
	}
	public static function getHtmlName($db,$id)
	{
		$row=$db->fetchRow($db->select()->from(self::TABLE,'*')->where("id='".$id."'"));
		if(!empty($row['url']))
			$str='<a href="'.$row['url'].'">'.$row['name'].'</a>';
		else
			$str=$row['name'];
		return $str;
	}
}