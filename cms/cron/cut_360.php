<?php
// crontab: /bin/php cut_360.php > /www/publish/vodone/zt/xwdyy/index2.shtml

$contents = file_get_contents("/www/publish/vodone/zt/xwdyy/index.shtml");

//$contents = str_replace('<img src="http://image.v1.cn/vodone/contentarea/2013/08/19/799bad5a3b_1376884469.jpg">', '<img src="http://image.v1.cn/vodone/contentarea/2013/09/13/016fae38dd_1379045113.jpg">', $contents);
$contents = str_replace('http://image.v1.cn/vodone/contentarea/2013/08/19/799bad5a3b_1376884469.jpg', 'http://image.v1.cn/vodone/contentarea/2013/09/25/44bcf4c549_1380073794.jpg', $contents);
$contents = str_replace('<!--#include virtual="/ssi/contentHeader_common.html"-->', '', $contents);
$contents = str_replace('<!--#include virtual="/ssi/footer_common.html"-->', '', $contents);
$contents = str_replace('<!---->', '', $contents);
$contents = preg_replace('/\r/', '', $contents);
$contents = preg_replace('/\n\n/', '', $contents);
$items = explode("<!-- cut360 -->", $contents);
echo $items[0];
?>
<!-- START baidu v1.0 -->
<script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3Fcb44eb450c53c91a1cc1d2511f5919b3' type='text/javascript'%3E%3C/script%3E"));
</script>
<!-- END baidu v1.0 -->
</body></html>
