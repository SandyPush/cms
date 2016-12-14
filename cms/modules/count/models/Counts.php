<?php

class Counts
{
	const TABLE = 'count_by_day';//主数据表
	const ARTICLE ='article';
	const FOCUS = 'focus';
	const USERS = 'users';
	private $start_date;
	private $end_date;
	private $db;//数据库连接
	public function __construct($db)
	{
		$this->db=$db;
		$this->start_date=date("Ymd");
		$this->end_date=date("Ymd");
	}
	public function __destruct()
	{
	}
	public static function datetypes()
	{
		return array('day'=>'日','week'=>'周','month'=>'月','quarter'=>'季度','year'=>'年','custom'=>'时间段');
	}
	public static function yearList()
	{
		$years=array();
		for($i=date("Y");$i>=2001;$i--)$years[$i]=$i;
		return $years;
	}
	public static function weekList()
	{
		$weeks=array();
		for($i=1;$i<=53;$i++){$i=sprintf('%02d',$i);$weeks[$i]=$i;}
		return $weeks;
	}
	public static function monthList()
	{
		$months=array();
		for($i=1;$i<=12;$i++){$i=sprintf('%02d',$i);$months[$i]=$i;}
		return $months;
	}
	public static function quarterList()
	{
		$quarters=array();
		for($i=1;$i<=4;$i++){$i=sprintf('%02d',$i);$quarters[$i]=$i;}
		return $quarters;
	}
	public static function dayList()
	{
		$days=array();
		for($i=1;$i<=31;$i++){$i=sprintf('%02d',$i);$days[$i]=$i;}
		return $days;
	}
	public function startDate($date=NULL)
	{
		if(!isset($date))return $this->start_date;
		$this->start_date=$date;
	}
	public function endDate($date=NULL)
	{
		if(!isset($date))return $this->end_date;
		$this->end_date=$date;
	}
	public function countData($uid,$yuegao=0, $channels= array())
	{
		$data= array();
		foreach($channels as $key=> $var){
			$current_db= 'cms_'.$key;
			$this->db->query("use $current_db ;");	
			$tbl_article="SELECT * FROM ".self::ARTICLE." WHERE  postdate BETWEEN '".$this->start_date."' AND '".$this->end_date."'";
			if($yuegao>0)$tbl_article.=" AND type=1";
			$tbl_focus="SELECT COUNT(*) FROM ".self::FOCUS." WHERE  starttime BETWEEN '".date('Y-m-d H:i:s',$this->start_date)."' AND '".date('Y-m-d H:i:s',$this->end_date)."'";
			$sql="SELECT u.uid,u.realname,IFNULL(COUNT(a.aid),0) article,(".$tbl_focus." AND uid=u.uid) focus,IFNULL(SUM(a.pv),0) pv,name_const('comments',0),IFNULL(SUM(a.content_length),0) content_length FROM ".self::USERS." u LEFT  JOIN (".$tbl_article.") a ON u.uid=a.uid";
			$sql.= $uid>0? " WHERE u.uid=".$uid : " WHERE u.usergroup >2";	
			$sql.="  GROUP BY u.uid";
			
			$row= $this->db->fetchAll($sql);	
			foreach($row as $k=>$v){
				$buf2[$v['uid']]['article']+= $v['article'];	
				$buf2[$v['uid']]['pv']+= $v['pv'];	
				$buf2[$v['uid']]['focus']+= $v['focus'];	
				$buf2[$v['uid']]['comments']+= $v['comments'];	
				$buf2[$v['uid']]['content_length']+= $v['content_length'];	


				$buf[$v['uid']][$key]= $v;		
				$buf[$v['uid']][$key]['channelname']= $var;	
				unset($v);
			}
		}

        if(!is_array($buf)) $buf=array();
		foreach($buf as $key=> $var){	
			foreach($var as $k=>$v){
				$buf[$v['uid']][$key]['sumarticle']= $buf2[$v['uid']]['article'];	
				$buf[$v['uid']][$key]['sumpv']= $buf2[$v['uid']]['pv'];	
				$buf[$v['uid']][$key]['sumfocus']= $buf2[$v['uid']]['focus'];	
				$buf[$v['uid']][$key]['sumcomments']= $$buf2[$v['uid']]['comments'];	
				$buf[$v['uid']][$key]['sumcontent_length']= $buf2[$v['uid']]['content_length'];
				unset($v);
			}
		}
		unset($buf2);				
		return $buf;
	}

	public function  channelData($channels){
		$data= array();
		foreach($channels as $key=> $var){
			$current_db= 'cms_'.$key;
			$usergroup= $this->db->fetchAll("SELECT group_concat(uid) AS user ,group_concat(realname) FROM {$current_db}.".self::USERS." WHERE usergroup  >2");	
			$usergroup= $usergroup[0]['user'];
			$groupadd= $usergroup? " AND (uid IN ($usergroup))" :"";
			$tbl_focus="SELECT COUNT(*) FROM {$current_db}.".self::FOCUS." WHERE  starttime BETWEEN '".date('Y-m-d H:i:s',$this->start_date)."' AND '".date('Y-m-d H:i:s',$this->end_date)."'".$groupadd;
			$sql="SELECT (".$tbl_focus.") focus,IFNULL(COUNT(aid),0) article,IFNULL(SUM(pv),0) pv,name_const('comments',0),IFNULL(SUM(content_length),0) content_length FROM {$current_db}.".self::ARTICLE." WHERE  postdate BETWEEN '".$this->start_date."' AND '".$this->end_date."'".$groupadd;			
			$row= $this->db->fetchAll($sql);
			$row[0]['channel']= $key;
			$row[0]['channelname']= $var;
			$data[]= $row[0];
		}		
		return $data;
	}

	public function  pvData($db, $channel, $page, $limit){
			$page= $page? $page: 1;
			$start= ($page-1)*$limit;			
			if(!$channel)return;

			if($channel=='www'){$sqladd="";}else{$sqladd=" and a.channel='$channel'";}


			$sql1= "select SUM(a.pv) as count,b.title,b.channel_aid,b.url,b.author,b.realname,b.postdate from click a,article b WHERE a.aid=b.channel_aid and a.channel=b.channel and  a.time BETWEEN '".$this->start_date."' AND '".$this->end_date."'".$sqladd." group by b.channel,b.channel_aid order by count desc limit $start,$limit";
			
			$sql2= "select SUM(a.pv) as count from click a WHERE a.time BETWEEN '".$this->start_date."' AND '".$this->end_date."' ".$sqladd;

			$sql3= "select * from click a WHERE a.time BETWEEN '".$this->start_date."' AND '".$this->end_date."'".$sqladd." group by concat(a.channel,a.aid)";
			
			$row= $db->fetchAll($sql1);	
			$count= $db->fetchAll($sql2);
			$total= $db->fetchAll($sql3);
			$return['data']= $row;
			$return['count']= $count[0]['count'];
			$return['total']= count($total);			
		return $return;
	}

	public function yuegaoData()
	{
		$sql="SELECT author,IFNULL(COUNT(aid),0) article,IFNULL(SUM(pv),0) pv,name_const('comments',0),IFNULL(SUM(content_length),0) content_length FROM ".self::ARTICLE." WHERE  postdate BETWEEN '".$this->start_date."' AND '".$this->end_date."' AND type=1 GROUP BY author";
		return $this->db->fetchAll($sql);
	}

	public function formatDate($type,$date_array)
	{
		switch($type)
		{
			case 'day':
			{
				$start_date=mktime(0,0,0,$date_array['month'],$date_array['day'],$date_array['year']);
				$end_date=mktime(24,0,0,$date_array['month'],$date_array['day'],$date_array['year']);
				break;
			}
			case 'week':
			{
				$start_date=strtotime("+".$date_array['week']." week",mktime(0,0,0,1,1,$date_array['year']));
				$end_date=strtotime("+".($date_array['week']+1)." week",mktime(0,0,1,1,1,$date_array['year']));
				break;
			}
			case 'month':
			{
				$start_date=mktime(0,0,0,$date_array['month'],1,$date_array['year']);
				$end_date=mktime(24,0,0,$date_array['month'],date('t',strtotime("+".(mktime(0,0,1,$date_array['month'],01,$date_array['year']))." month")),$date_array['year']);
				break;
			}
			case 'quarter':
			{
				$start_date=mktime(0,0,0,($date_array['quarter']*3-2),1,$date_array['year']);
				$end_date=mktime(24,0,0,($date_array['quarter']*3),date('t',strtotime("+".(mktime(0,0,1,($date_array['quarter']*3),01,$date_array['year'])))),$date_array['year']);
				break;
			}
			case 'year':
			{
				$start_date=mktime(0,0,0,1,1,$date_array['year']);
				$end_date=mktime(24,0,0,12,31,$date_array['year']);
				break;
			}
			default:
				$start_date=mktime(0,0,0,$date_array['month'],$date_array['day'],$date_array['year']);
				$end_date=mktime(24,0,0,$date_array['end_month'],$date_array['end_day'],$date_array['end_year']);
				break;
		}
		$this->start_date=$start_date;
		$this->end_date=$end_date;
	}
}