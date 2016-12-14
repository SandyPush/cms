<?php
require_once 'Zend/Db/Table.php';
class Customlist extends Zend_Db_Table{
	public $db;
	public $error = '';
	private $MaxTimeWait = 600;
	private $LimitNum =50;
	protected $_name='customlist';

	public function __construct($db){
		$this->db=$db;
		parent::__construct();		
	}

	private function getSqlData($sql){
		$query=$this->db->query($sql);
		$result=$query->fetchAll();
		return $result;
	}

	public function buildBigNewsList($bignews) {		
		$list = '';
		for ($i = 0; $i < count($bignews); $i++) {
			$bignewslist = $bignews[$i];
			$list .= '<' . $bignewslist['tag'] . ">\r\n";
			for ($j = 0; $j < count($bignewslist['items']); $j++) {
				$bignewslistitem = $bignewslist['items'][$j];
				$bignewslistitem['tag']= $bignewslistitem['tag']? $bignewslistitem['tag']: 'li';
				$list .= '  <' . $bignewslistitem['tag'] . ">";
				for ($k = 0; $k < count($bignewslistitem['items']); $k++) {
					$anchoritem = $bignewslistitem['items'][$k];
					if ($anchoritem['href']) {
						$list .= '<a href="' . htmlspecialchars($anchoritem['href']) . '" target="_blank"';
					}
					else {
						$list .= '<span';
					}
					if ($anchoritem['class']) {
						$list .= ' class="' . htmlspecialchars($anchoritem['class']) . '"';
					}
					$list .= ' title="' . htmlspecialchars($anchoritem['title']) . '">' . htmlspecialchars($anchoritem['title']);
					if ($anchoritem['href']) {
						$list .= "</a> ";
					}
					else {
						$list .= "</span> ";
					}
				}
				$list .= '</' . $bignewslistitem['tag'] . ">\r\n";
			}
			$list .= '</' . $bignewslist['tag'] . ">\r\n";
		}
		return $list;
	}

	public function getBigNews($oid,$pid=''){
		$query="SELECT * FROM `customlist` WHERE oid='$oid' AND pid='$pid' ORDER BY lastdate DESC LIMIT 1";
		$data=$this->getSqlData($query);		
		$return = $data[0];
		if($return)return $return;
	}

	public function getBigNewsByTime($oid,$time,$pid=''){
		$query="SELECT * FROM `customlist` WHERE oid='$oid' AND pid='$pid' AND lastdate='$time' ORDER BY lastdate DESC LIMIT 1";		
		$data=$this->getSqlData($query);		
		$return = $data[0];
		if($return)return $return;
	}

	public function getBigNewsSelect($oid,$pid=''){		
		$add=(int)$LimitNum?"LIMIT $this->LimitNum":'';
		$sql="SELECT id,pid,lastdate,lastuid FROM `customlist` WHERE oid='$oid'  AND pid='$pid'  ORDER BY lastdate DESC $add";

		$query=$this->db->query($sql);
		$result='';
		$arr=array();
		while($data = $query->fetch()){
			$time=date('Y-m-d H:i:s',$data['lastdate']);
			$user=$data['lastuid']?"|$data[lastuid]":'';
			$result.= "<option value=\"/pid/$data[pid]/oid/$oid/date_time/$data[lastdate]\" >$time$user</option>"; 
			$arr[]=$data;
		}
		$count=count($arr)-1;
		$thetime=$arr[$count]['lastdate'];
		$add=$thetime?"AND lastdate < $thetime":'';
		$sqldel="delete from `customlist` where oid='$oid'  AND pid='$pid' $add";
		$this->db->query($sqldel);
		unset($arr,$count,$sql,$sqldel);
		return $result;
	}

	public function saveBigNews($oid,$lastuid,$customlist,$pid='') {		
		$content = addslashes(serialize($customlist));		
		$contenthtml=addslashes($this->buildBigNewsList($customlist));
		$time = time();
		$query="INSERT INTO `customlist` (oid,pid,content,contenthtml,lastdate,lastuid) VALUES ('$oid','$pid','$content','$contenthtml',$time,'$lastuid')";			
		if($this->db->query($query)){
			return true;
		}
	}
	
	public function iconv_deep($in_charset, $out_charset, $value) {
		if (is_array($value)) {
			foreach ($value as $k => $v) {
				$value[$k] = $this->iconv_deep($in_charset, $out_charset, $v);
			}
			return $value;
		}else {
			return iconv($in_charset, $out_charset, $value);
		}
	}

	public function stripslashes_deep($value) {
        return is_array($value) ? array_map(array("Customlist","stripslashes_deep"),  $value) : stripslashes($value);
    }
}
?>