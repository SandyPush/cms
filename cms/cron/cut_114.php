<?php
// crontab: /bin/php cut_114.php > /www/publish/vodone/zt/2014lh/index114.shtml

$contents = file_get_contents("/www/publish/vodone/zt/2014lh/index.shtml");

$contents = str_replace('http://image.v1.cn/vodone/contentarea/2014/02/25/59260c0981_1393319525.jpg', 'http://image.v1.cn/vodone/20140228/86916.jpg', $contents);
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
