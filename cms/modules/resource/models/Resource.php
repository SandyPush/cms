<?php

class Resource
{
	const TABLE = 'resource';//主数据表
	private $db;//数据库连接
	protected $fields=array();#数据表所有字段名
    protected $members=array();#成员名,不设置没有
    public $debug=false;
    public $debug_exit=false;
	public function __construct($db,$id=NULL)
	{
        $this->db=$db;
        $fields=$this->db->fetchCol("SHOW COLUMNS FROM ".self::TABLE);
        $this->fields=$fields;
        if(!empty($id))$this->init($id);
	}
    public function __get($member)
    {
        if(isset($this->members[$member]))
        {
            return $this->members[$member]; 
        }
		return false;
    }
    public function __set($member,$value)
    {
		$this->members[$member]=$value; 
    }
    public function __call($func_name, $arguments=NULL)
    {
        in_array($func_name,$this->fields) OR die('Method: '.$func_name.'() is not exists!');
        if(!isset($arguments))   
        {
            return $this->members[$func_name];
        }
		$this->members[$func_name]=$arguments[0];
    }
    public function debugMessage($message)
    {
        if($this->debug)
        {
        	if(is_array($message))
        	{
        		/// debug
				echo "<div align=left> debugMessage = <pre>'";
				print_r($message);
				echo "'</pre></div>";
        	}else
        	{
            	echo "<br>debugMessage = " . $message . "<br>";
            }
            if($this->debug_exit)
            	exit;
        }
    }
	public function init($id)
	{
		$select=$this->db->select();
		$select->from(self::TABLE,'*');
		$select->where('resource_id=?',$id);
		$select->limit(1);
		$sql=$select->__toString();
		$this->debugMessage(__CLASS__.':'.__METHOD__.':'.$sql);
        $this->members=$this->db->fetchRow($sql);
	}
	//添加
	public function add()
	{
		$this->debugMessage($this->members);
		$this->db->insert(self::TABLE,$this->members);
		return $this->db->lastInsertId();
	}
	//修改
	public function modify($id)
	{
		$this->debugMessage(__CLASS__.':'.__METHOD__.':'.$this->members);
		$where = $db->quoteInto('resource_id = ?', $id);
		$rows_affected = $this->db->update(self::TABLE,$this->members,$where);
		return $rows_affected;
	}
	//删除资源
	public function delete($id)
	{
		$where = $db->quoteInto('resource_id = ?', $id);
		$set=array('status' => 0);
		$rows_affected = $this->db->update(self::TABLE,$set,$where);
		return $rows_affected;
	}

	//获取资源类型列表
	public function getTypeSelect()
	{
		//return array(0=>"图片",1=>"文件");
		return array(0=>"图片");
	}
	//获取资源存储路径
	public function getPath($id)
	{
		$sql="select concat(from_unixtime(create_time,'%Y%m%d/'),resource_id,'.',file_ext) as pathfile from ".self::TABLE." where resource_id=".$id;
		$this->debugMessage(__CLASS__.':'.__METHOD__.':'.$sql);
		return $this->db->fetchOne($sql);
	}
	//获取资源缩略图宽高
	public function getSmallWidth()
	{
		return $this->small_width;
	}
	public function getSmallHeight()
	{
		return $this->small_height;
	}
}