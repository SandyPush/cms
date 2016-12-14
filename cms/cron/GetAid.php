<?PHP 
// --------------------------------------------------------------------------
// File name   : GetAid.php
// Description : 通过sql获取新闻id
// Requirement : PHP4/PHP5 ([url]http://www.php.net[/url])
// Copyright(C), zhangjinguo, 2009, All Rights Reserved.
// Author: zhangjinguo ([email]zhangjinguo@titan24.com[/email]) 
// 例子 php GetAid.php vodone "select aid from article where cid=1004"
// --------------------------------------------------------------------------

error_reporting($_GET['debug']);
date_default_timezone_set('Aisa/shanghai');

$stime=microtime(true);  //计算运行时间 开始

$link = mysql_connect('cms.db.v1cn', 'cms','cmsv1cn')or die("Could not connect: " . mysql_error());
$db= $_GET['db']? $_GET['db']: "cms_".$argv[1];
mysql_select_db($db, $link);
mysql_query("set names utf8");
$aids="";
$query = mysql_query($argv[2]);
$data= $temp= array();
while ($row = mysql_fetch_assoc($query)){
	$aids.=" ".$row['aid'];
}

$cmd="php /www/cms/cron/publish_".$argv[1].".php -a".$aids;

fwrite(STDOUT, "您要执行的是：".$cmd."\n");
fwrite(STDOUT, "请检查命令是否正确？（Y/N）");

$answer = trim(fgets(STDIN));
	if($answer == "y" || $answer == "Y"){
		//exec($cmd);
		$out=PassThru($cmd);
		echo $out;

	}
	else{
		exit();
	}

mysql_close($link);

//计算运行时间 结束
$etime=microtime(true);//获取程序执行结束的时间   
$total=$etime-$stime;   //计算差值   

$str_total = var_export($total, TRUE);   
if(substr_count($str_total,"E")){   
    $float_total = floatval(substr($str_total,5));   
    $total = $float_total/100000;     
}
echo "$total".'秒';    

?>
