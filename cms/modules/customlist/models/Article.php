<?php
require_once 'Zend/Db/Table.php';
class Article extends Zend_Db_Table{	
	protected $_name='article';
	public $db;
	public $error = '';

	public function __construct($db){
		$this->db= $db;
		parent::__construct();		
	}

	private function getSqlData($sql){
		$query= $this->db->query($sql);
		$result= $query->fetchAll();
		return $result;
	}

	public function Count($sqladd, $db, $tableadd=''){
		$this->db->query("use $db");
		$sql="SELECT COUNT(*) AS nums from article a $tableadd where 1 $sqladd AND a.status>=1";		
		$result= $this->getSqlData($sql);
		return $result[0]['nums'];
	}

	public function getList($sqladd, $start=0, $offset=0, $db, $tableadd=''){
		$this->db->query("use $db");
		$this->db->query("set names utf8");
		$sql="SELECT a.*,b.name as cname FROM  article a left join categories b on a.cid=b.cid $tableadd where 1 $sqladd AND a.status>=1 ORDER BY a.postdate DESC";			
		if($offset > 0) $sql.=" limit ".$start.", ".$offset;		
		$result=$this->getSqlData($sql);		
		return $result;
	}

	public function getSelect(){
		require_once MODULES_PATH . 'category/models/Category.php';		
		$result= Category::getOptions($this->db,'顶级类别');
		return $result;
	}

	public function Char_cv($msg){	
		$msg = str_replace('&amp;','&',$msg);
		$msg = str_replace('&nbsp;',' ',$msg);	
		$msg = str_replace("'",'&#39;',$msg);
		$msg = str_replace("<","&lt;",$msg);
		$msg = str_replace(">","&gt;",$msg);
		$msg = str_replace("\t","   &nbsp;  &nbsp;",$msg);
		$msg = str_replace("\r","",$msg);
		$msg = str_replace("   "," &nbsp; ",$msg);
		return $msg;
	}
}
?>