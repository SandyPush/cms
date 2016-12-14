<?php
/** @see BaseController */
require_once 'BaseController.php';
require_once MODULES_PATH . 'count/models/Counts.php';
require_once MODULES_PATH . 'system/models/Users.php';

class Count_IndexController extends BaseController
{
	private $channel_db;
	private $obj;
	private $channel;
	private $channels;
	public function init()
	{
		$channel= $this->_getParam('channel', '');
		if(!$channel){
			$user=Zend_Session::namespaceGet('user');
			$channel=$user['channel'];
		}	
		if($channel)$channel_db= $this->getChannelDbAdapter($channel);
	    else $channel_db = $this->getChannelDbAdapter();
	    $this->channel_db=$channel_db;
		$this->channel=$channel;
		$this->channels=Zend_Registry::get('settings')->channels->toArray();
		unset($this->channels['game'], $this->channels['wireless'], $this->channels['home'],  $this->channels['focus'],  $this->channels['www']);
    	$this->obj=new Counts($channel_db);
	}
	public function indexAction()
	{
        $this->_checkPermission('count', 'index');
        $year=$this->_getParam('year',date('Y'));
        $quarter=$this->_getParam('quarter','01');
        $month=$this->_getParam('month',date('m'));
        $week=$this->_getParam('week',date('W'));
        $day=$this->_getParam('day',date('d'));
        $datetype=$this->_getParam('datetype', 0);
        $end_year=$this->_getParam('end_year',date('Y'));
        $end_month=$this->_getParam('end_month',date('m'));
        $end_day=$this->_getParam('end_day',date('d'));
        $uid=$this->_getParam('uid',0);
		$this->view->datetypes=Counts::datetypes();
		$this->view->datetype=$datetype;		
		$channel= $this->channel;
		$channels=Zend_Registry::get('settings')->channels->toArray();
		$channelname= $channels[$channel];  
		$this->view->channel=$channel;
		$this->view->channelname=$channelname;
		$usernames=UsersTable::usernameList($this->channel_db);
		$this->view->uid=$uid;
		$this->view->usernamelist=(array('-1'=>'全部用户') + $usernames);
		$this->view->years=Counts::yearList();
		$this->view->year=$year;
		$this->view->quarters=Counts::quarterList();
		$this->view->quarter=$quarter;
		$this->view->months=Counts::monthList();
		$this->view->month=$month;
		$this->view->weeks=Counts::weekList();
		$this->view->week=$week;
		$this->view->days=Counts::dayList();
		$this->view->day=$day;
		$this->view->end_year=$end_year;
		$this->view->end_month=$end_month;
		$this->view->end_day=$end_day;
//		$para_str="&uid=".$uid."&datetype=".$datetype."&year=".$year."&month=".$month."&day=".$day."&quarter=".$quarter."&week=".$week."&end_year=".$end_year."&end_month=".$end_month."&=end_day=".$end_day;
//		$this->view->para_str=$para_str;
		if($uid>0)$usernames=array($uid=>$usernames[$uid]);
		$date_array=array(
			'year'=>$year,
			'month'=>$month,
			'day'=>$day,
			'week'=>$week,
			'quarter'=>$quarter,
			'end_year'=>$end_year,
			'end_month'=>$end_month,
			'end_day'=>$end_day,
		);
		$this->obj->formatDate($datetype,$date_array);
		$channels=Zend_Registry::get('settings')->channels->toArray();
		$data= $this->obj->countData($uid, 0, $this->channels);		
		$this->view->data=$data;
	}

	public function channelAction()
	{
        $this->_checkPermission('count', 'channel');
        $year=$this->_getParam('year',date('Y'));
        $quarter=$this->_getParam('quarter','01');
        $month=$this->_getParam('month',date('m'));
        $week=$this->_getParam('week',date('W'));
        $day=$this->_getParam('day',date('d'));
        $datetype=$this->_getParam('datetype', 0);
        $end_year=$this->_getParam('end_year',date('Y'));
        $end_month=$this->_getParam('end_month',date('m'));
        $end_day=$this->_getParam('end_day',date('d'));     
		$this->view->datetypes=Counts::datetypes();
		$this->view->datetype=$datetype;	
		$this->view->years=Counts::yearList();
		$this->view->year=$year;
		$this->view->quarters=Counts::quarterList();
		$this->view->quarter=$quarter;
		$this->view->months=Counts::monthList();
		$this->view->month=$month;
		$this->view->weeks=Counts::weekList();
		$this->view->week=$week;
		$this->view->days=Counts::dayList();
		$this->view->day=$day;
		$this->view->end_year=$end_year;
		$this->view->end_month=$end_month;
		$this->view->end_day=$end_day;	
		$channels=Zend_Registry::get('settings')->channels->toArray();
		
		$date_array=array(
			'year'=>$year,
			'month'=>$month,
			'day'=>$day,
			'week'=>$week,
			'quarter'=>$quarter,
			'end_year'=>$end_year,
			'end_month'=>$end_month,
			'end_day'=>$end_day,
		);		
		$this->obj->formatDate($datetype,$date_array);
		$data=$this->obj->channelData($channels);
		$this->view->data=$data;
	}

	public function pvAction()
	{
        $this->_checkPermission('count', 'pv');
		$page=$this->_getParam('page',1); 
        $year=$this->_getParam('year',date('Y'));
        $quarter=$this->_getParam('quarter','01');
        $month=$this->_getParam('month',date('m'));
        $week=$this->_getParam('week',date('W'));
        $day=$this->_getParam('day',date('d'));
        $datetype=$this->_getParam('datetype', 0);
        $end_year=$this->_getParam('end_year',date('Y'));
        $end_month=$this->_getParam('end_month',date('m'));
        $end_day=$this->_getParam('end_day',date('d'));     
		$this->view->datetypes=Counts::datetypes();
		$this->view->datetype=$datetype;	
		$this->view->years=Counts::yearList();
		$this->view->year=$year;
		$this->view->quarters=Counts::quarterList();
		$this->view->quarter=$quarter;
		$this->view->months=Counts::monthList();
		$this->view->month=$month;
		$this->view->weeks=Counts::weekList();
		$this->view->week=$week;
		$this->view->days=Counts::dayList();
		$this->view->day=$day;
		$this->view->end_year=$end_year;
		$this->view->end_month=$end_month;
		$this->view->end_day=$end_day;			
		
		$date_array=array(
			'year'=>$year,
			'month'=>$month,
			'day'=>$day,
			'week'=>$week,
			'quarter'=>$quarter,
			'end_year'=>$end_year,
			'end_month'=>$end_month,
			'end_day'=>$end_day,
		);		
		$this->obj->formatDate($datetype,$date_array);
		$searchDb= $this->getChannelDbAdapter('search');
		$data=$this->obj->pvData($searchDb, $this->channel, $page, 30);
		$this->view->data= $data['data'];
		$this->view->count= $data['count'];		
		$search_para='&datetype='.$datetype.'&year='.$year.'&month='.$month.'&day='.$day.'&week='.$week.'&quarter='.$quarter.'&end_year='.$end_year.'&end_month='.$end_month.'&end_day='.$end_day;
		$total = $data['total'];
		$perpage= 30;
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__'.$search_para);
		$this->view->pagebar = $pagebar;
	}

	public function yuegaoAction()
	{
        $this->_checkPermission('count', 'yuegao');
        $year=$this->_getParam('year',date('Y'));
        $quarter=$this->_getParam('quarter','01');
        $month=$this->_getParam('month',date('m'));
        $week=$this->_getParam('week',date('W'));
        $day=$this->_getParam('day',date('d'));
        $datetype=$this->_getParam('datetype', 0);
        $end_year=$this->_getParam('end_year',date('Y'));
        $end_month=$this->_getParam('end_month',date('m'));
        $end_day=$this->_getParam('end_day',date('d'));
        $uid=$this->_getParam('uid',0);
		$this->view->datetypes=Counts::datetypes();
		$this->view->datetype=$datetype;
		$this->view->years=Counts::yearList();
		$this->view->year=$year;
		$this->view->quarters=Counts::quarterList();
		$this->view->quarter=$quarter;
		$this->view->months=Counts::monthList();
		$this->view->month=$month;
		$this->view->weeks=Counts::weekList();
		$this->view->week=$week;
		$this->view->days=Counts::dayList();
		$this->view->day=$day;
		$this->view->end_year=$end_year;
		$this->view->end_month=$end_month;
		$this->view->end_day=$end_day;
		$date_array=array(
			'year'=>$year,
			'month'=>$month,
			'day'=>$day,
			'week'=>$week,
			'quarter'=>$quarter,
			'end_year'=>$end_year,
			'end_month'=>$end_month,
			'end_day'=>$end_day,
		);
		$this->obj->formatDate($datetype,$date_array);
		$data=$this->obj->yuegaoData();
		$this->view->data=$data;
		$search_para='&datetype='.$datetype.'&year='.$year.'&month='.$month.'&day='.$day.'&week='.$week.'&quarter='.$quarter.'&end_year='.$end_year.'&end_month='.$end_month.'&end_day='.$end_day.'&channel='.$channel;
	}
	
	public function listAction()
	{
		require_once MODULES_PATH . 'contents/models/Article.php';	
		$article= new Article($this->channel_db);	
		$channel= $this->channel;
        $perpage = 30;
        $page = $this->_getParam('page', 1);
        $page = max($page, 1);
        $article->setPage($page);
        $article->setPerpage($perpage);
        $year=$this->_getParam('year',date('Y'));
        $quarter=$this->_getParam('quarter','01');
        $month=$this->_getParam('month',date('m'));
        $week=$this->_getParam('week',date('W'));
        $day=$this->_getParam('day',date('d'));
        $datetype=$this->_getParam('datetype', 0);
        $end_year=$this->_getParam('end_year',date('Y'));
        $end_month=$this->_getParam('end_month',date('m'));
        $end_day=$this->_getParam('end_day',date('d'));
        $uid=$this->_getParam('uid',0);
        $author=$this->_getParam('author',0);
		$this->view->datetypes=Counts::datetypes();
		$this->view->datetype=$datetype;
		$usernames=UsersTable::usernameList($this->channel_db);
		$this->view->uid=$uid;
		$this->view->usernamelist=(array('-1'=>'全部用户') + $usernames);
		$this->view->years=Counts::yearList();
		$this->view->year=$year;
		$this->view->quarters=Counts::quarterList();
		$this->view->quarter=$quarter;
		$this->view->months=Counts::monthList();
		$this->view->month=$month;
		$this->view->weeks=Counts::weekList();
		$this->view->week=$week;
		$this->view->days=Counts::dayList();
		$this->view->day=$day;
		$this->view->end_year=$end_year;
		$this->view->end_month=$end_month;
		$this->view->end_day=$end_day;
		$this->view->push_url=$this->getChannelConfig($channel)->url->published;
		$date_array=array(
			'year'=>$year,
			'month'=>$month,
			'day'=>$day,
			'week'=>$week,
			'quarter'=>$quarter,
			'end_year'=>$end_year,
			'end_month'=>$end_month,
			'end_day'=>$end_day,
		);
		$this->obj->formatDate($datetype,$date_array);
        #搜索
        $search_para='&datetype='.$datetype.'&year='.$year.'&month='.$month.'&day='.$day.'&week='.$week.'&quarter='.$quarter.'&end_year='.$end_year.'&end_month='.$end_month.'&end_day='.$end_day.'&channel='.$channel;
		$search_para.='&uid='.$uid;
		$article->setWhere("postdate BETWEEN ".$this->obj->startDate()." AND ".$this->obj->endDate());
		if(!empty($uid))$article->setWhere("uid=".$uid);
		if(!empty($author))$article->setWhere("type=1 AND author='".$author."'");
        $article->setOrder('postdate');
    	$result=$article->getPageList();
		$channels=Zend_Registry::get('settings')->channels->toArray();
		$channelname= $channels[$channel];
    	$this->view->result=$result;
		$this->view->channel=$channelname;
    	$this->view->search_para=$search_para;
        $total = $article->getCount();
        $pagebar = Util::buildPagebar($total, $perpage, $page, '?page=__page__'.$search_para);
        $this->view->pagebar = $pagebar;
	}

	public function exportAction(){
		require_once MODULES_PATH . 'contents/models/Article.php';	
		$article= new Article($this->channel_db);	
		$channel= $this->channel;
		$push_url= $this->getChannelConfig($channel)->url->published;

        $year=$this->_getParam('year',date('Y'));
        $quarter=$this->_getParam('quarter','01');
        $month=$this->_getParam('month',date('m'));
        $week=$this->_getParam('week',date('W'));
        $day=$this->_getParam('day',date('d'));
        $datetype=$this->_getParam('datetype', 0);
        $end_year=$this->_getParam('end_year',date('Y'));
        $end_month=$this->_getParam('end_month',date('m'));
        $end_day=$this->_getParam('end_day',date('d'));
        $uid=$this->_getParam('uid',0);
        $author=$this->_getParam('author',0);	
		
		$date_array=array(
			'year'=>$year,
			'month'=>$month,
			'day'=>$day,
			'week'=>$week,
			'quarter'=>$quarter,
			'end_year'=>$end_year,
			'end_month'=>$end_month,
			'end_day'=>$end_day,
		);
		$this->obj->formatDate($datetype,$date_array);      
       
		$article->setWhere("postdate BETWEEN ".$this->obj->startDate()." AND ".$this->obj->endDate());
		if(!empty($uid))$article->setWhere("uid=".$uid);
		if(!empty($author))$article->setWhere("type=1 AND author='".$author."'");
        $article->setOrder('postdate');
    	$result=$article->getList();
		$channels=Zend_Registry::get('settings')->channels->toArray();
		$cname= $channels[$channel];
		$buffer= array();

		/*
		$hash= md5(time());		
		$DownloadDir= $this->getChannelConfig($this->channel)->path->published; 
		$DownloadName= $cname.'_'.$result[0]['realname'].'_'.date('YmdHis',time()).'.csv';
		$DownloadFlie= $DownloadDir.$hash.'.csv';		

		$handle = fopen($DownloadFlie, "a+");	

		$title= array('ID' , '标题', '作者', '发布时间', '浏览量', '稿件字数');	
		//fputs($handle, "ID, 标题, 作者, 发布时间, 浏览量, 稿件字数 \r");
		fputcsv($handle, array());
		fputcsv($handle, $title);		
		foreach($result as &$r){
			$r['url']= $r['islink']? '': $push_url.$r['url'];
			$r['postdate']= date('Y-m-d H:i:s',$r['postdate']);	
			$buffer= array($r['aid'], $r['title'], $r['author'], $r['postdate'], $r['pv'], $r['content_length']);
			fputcsv($handle, $buffer);
		}	
		fclose($handle);
        Header('Content-type:application/force-download,charset=utf-8');
        Header('Accept-Ranges:bytes');
        Header('Accept-Length:' . filesize($DownloadFlie));
        Header('Content-Disposition: attachment; filename="'.$DownloadName.'"');
		$handle = fopen($DownloadFlie, "r");
        echo fread($handle, filesize($DownloadFlie));
        fclose($handle);
		if(is_file($DownloadFlie))unlink($DownloadFlie);
		*/	
		if(empty($result))$this->error('无记录');
		require_once MODULES_PATH . 'count/models/Export.php';
		require_once LIBRARY_PATH . 'PHPExcel/PHPExcel/IOFactory.php';	
		require_once LIBRARY_PATH . 'PHPExcel/PHPExcel/Writer/Excel5.php';
		$DownloadName= $cname.'_'.$result[0]['realname'].date('(YmdHis)',time()).'.xls';
		$title= array('ID' , '标题', '作者', '发布时间', '浏览量', '稿件字数');
		//iconv_recursion("GB2312", "UTF-8", &$title);		
		foreach($result as &$r){
			$r['url']= $r['islink']? $r['url']: $push_url.$r['url'];
			$r['postdate']= date('Y-m-d H:i:s',$r['postdate']);	
			$buffer[]= array($r['aid'], $r['title'], $r['author'], $r['postdate'], $r['pv'], $r['content_length'], $r['url']);			
		}
		//iconv_recursion("GB2312", "UTF-8", &$buffer);
		Header('Content-type:application/force-download,charset=utf-8');
		header('Content-Disposition: attachment;filename="'.$DownloadName.'"');
		header('Cache-Control: max-age=0');
		set_time_limit(0);
		ini_set("memory_limit", "180M");
		$objPHPExcel= GetObjPHPExcel($cname.'_'.$result[0]['realname'], $title, $buffer);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); 	
		exit;
	}

	public function error($str='', $flag=1){
		 echo "<script>";
		 if($str)echo "alert('{$str}');";
		 echo "window.opener=null;";
		 echo "window.open('','_self');";	
		 echo "window.close();";
		 echo "</script>"; 
		 if($flag)exit;		
	}
}
