<?php
class Flashppt
{
	const TABLE = 'flashppt';//主数据表
	const TABLE_LINK = 'page_to_flashppt';//主数据表
	protected $db;//数据库连接
	protected $fields=array();#数据表所有字段名
    protected $members=array();#成员名,不设置没有
    public $debug=false;
    
    public function __construct($db, $id=0)
    {
        $this->db=$db;
        $query=$this->db->query("SHOW COLUMNS FROM ".self::TABLE);
        while($row=$query->fetch()) $this->fields[]=$row['Field'];
        if(!empty($id))$this->init($id);
    }
	public function __destruct()
	{
	}
    public function __get($member)
    {
        if(in_array($member,array_keys($this->members)))
        {
            return $this->members[$member]; 
        }
		return false;
    }
    public function __set($member,$value)
    {
		$this->members[$member]=$value; 
    }
    public function __call($func_name, $arguments='')
    {
        in_array($func_name,$this->fields) OR die('Method: '.$func_name.'() is not exists!');
        if(empty($arguments))   
        {
            return $this->members[$func_name];
        }
		$this->members[$func_name]=$arguments[0];
    }
    public function debugMessage($message)
    {
        if($this->debug)
        {
            echo "<br>debugMessage = " . $message . "<br>";
        }
    }
	public function init($id,$pid=NULL)
	{
		$select=$this->db->select();
		$select->from(self::TABLE,'*');
		$select->where('fpid=?',$id);
		$select->limit(1);
		$sql=$select->__toString();
		$this->debugMessage(__CLASS__.':'.__METHOD__.':'.$sql);
        $this->members=$this->db->fetchRow($sql);
        $row=$this->members;
        if(isset($pid))
        {
        	$row['level']=$this->db->fetchOne("SELECT level FROM ".self::TABLE_LINK." WHERE pid='".$pid."' AND fpid=".$id);
        }
        return $row;
	}
	public function listPage($pid,$page=NULL,$perpage=NULL,$oid=0)
	{
		$select=$this->db->select();
		$select->from(self::TABLE_LINK,'*');
		$select->where('pid=?',$pid);
        if($oid){
            $select->where('oid=?',$oid);
        }
		$select->order('level DESC');
		$select->order('id DESC');
		if(isset($page) && isset($perpage))
		{
			$select->limitPage($page,$perpage);
		}
		$result=$this->db->fetchAll($select);
		if(empty($result))return $result;
		foreach($result as $i => &$row)
		{
			$select=$this->db->select()->from(self::TABLE,"*")->where("fpid=?",$row['fpid']);
			$row2=$this->db->fetchRow($select);
			$row=array_merge($row,$row2);
		}
        reset($result);
		return $result;
	}

	public function todayOnHistory($pid, $date)
	{
		$rows = $this->db->fetchAll('SELECT image, image_small banner, title, stitle `date`, description, url FROM flashppt fp, page_to_flashppt pf WHERE pf.pid = ' . $pid . ' AND stitle = \'' . $date . '\' AND fp.fpid = pf.fpid');

		return $rows;
	}

	//给本栏目(专题)添加一个头图
	public function add($data,$pid)
	{
		$fpid=$data['fpid'];
		unset($data['fpid']);
        $oid=$data['oid'];
        unset($data['oid']);
		$level=$data['level'];
		unset($data['level']);
		if(empty($fpid))
		{
			$this->db->insert(self::TABLE,$data);
			$fpid=$this->db->lastInsertId();
			$this->db->insert(self::TABLE_LINK,array('pid'=>$pid,'oid'=>$oid,'fpid'=>$fpid,'level'=>$level));
		}else
		{
			$this->db->update(self::TABLE,$data,'fpid='.$fpid);
			$this->db->update(self::TABLE_LINK,array('pid'=>$pid,'oid'=>$oid,'fpid'=>$fpid,'level'=>$level),"pid=".$pid." AND fpid=".$fpid);
		}
	}
	#清空栏目头图
	public function delete($pid,$fpid,$oid=0)
	{
		$this->db->delete(self::TABLE_LINK,"pid=".$pid." AND fpid=".$fpid." AND oid=".$oid);
		//$this->db->update(self::TABLE,array('status'=>0),"fpid=".$fpid);
	}
	#清空关联pid
	public function deletePage($fpid)
	{
		$this->db->delete(self::TABLE_LINK,"fpid=".$fpid);
	}
	#关联pid
	public function addPage($fpid,$pid,$level=0,$oid=0)
	{
		$this->db->insert(self::TABLE_LINK,array("fpid"=>$fpid,"pid"=>$pid,"oid"=>$oid,"level"=>$level));
	}
	#清空关联fpid
	public function deleteFp($pid,$oid=0)
	{
		$this->db->delete(self::TABLE_LINK,"pid=".$pid." and oid=".$oid);
	}
	#生成xml文件(格式特定)
	public function makeXML($xmlfilepath,$pid)
	{
		$xml='<document paused="0">'."\n";
		$images=$this->listPage($pid);
		if(empty($images))return false;
		foreach($images as $image)
		{
			$xml.='<item>'."\n";
			$xml.='<image>'.$image['image'].'</image>'."\n";
			$xml.='<smallimage>'.$image['image'].'</smallimage>'."\n";
			$xml.='<address>'.$image['url'].'</address>'."\n";
			$xml.='<a>'.$image['description'].'</a>'."\n";
			$xml.='<titile>'.$image['title'].'</titile>'."\n";
			$xml.='</item>'."\n";
		}
		$xml.='</document>'."\n";
		file_put_contents($xmlfilepath.'page'.$pid.'.xml',$xml);
	}
	public function getPids($fpid)
	{
		return $this->db->fetchCol($this->db->select()->from(self::TABLE_LINK,'pid')->where("fpid=".$fpid));
	}
}