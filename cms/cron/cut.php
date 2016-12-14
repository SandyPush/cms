<?php
// Author: xuchao@vi.cn
// crontab: /bin/php cut.php > /www/publish/vodone/news/index2.shtml
// 

$contents = file_get_contents("/www/publish/vodone/news/index.shtml");

$items = explode("<!-- cutcut -->", $contents);
echo str_replace('<script type="text/javascript" src="http://pic.v1.cn/cms/v1_static/js/newsChannel.js"></script>', '', $items[0])."\n";
// http://pic.v1.cn/cms/v1_static/images/line_news.png
?>
<script>
$(document).ready(function() {
    //最热排行
    shrinkBox('#hotRank .top10ListBox_page01',1);
    shrinkBox('#hotRank .top10ListBox_page02',2);
    
    //焦点图
    initFeatureSlide('#feature-slide-block');
    initFeatureSlide('#feature-slide-block2',true);
 
    //最热排行Tab
    switchTab('#hotRank .wgt-tab-s');
});
</script>
<style>
.wrapBox1000 {
    margin: 0 auto;
    width: 980px;
}
.newscenterBoxArea .tuvenContent .rBox .singelBox {
    float: left;
    height: 140px;
    line-height: 18px;
    margin-bottom: 0;
    padding: 4px 0 8px 20px;
    position: relative;
    width: 136px;
    overflow:hidden;
}
.newscenterBoxArea .tuvenTitle .cBox {
    background: url("http://pic.v1.cn/cms/v1_static/images/line_news.png") repeat scroll 0 0 transparent;
    float: left;
    height: 24px;
    padding-right: 10px;
    padding-top: 11px;
    text-align: right;
    width: 575px;
    overflow:hidden;
}
.newscenterBoxArea .tuvenContent .rBox {
    float: left;
    padding-top: 12px;
    width: 470px;
}
.wrapBox1000 .newscenterBoxArea {
    float: left;
    width: 710px;
}
.wrapBox1000 .newscenterBoxArea .tuvenContent {
    width: 710px;
}
.newscenterBoxArea .tuvenContent .fourBox .singelBox {
    float: left;
    height: 160px;
    margin-bottom: 0;
    margin-right: 18px;
    padding: 0 0 10px 0;
    position: relative;
    text-align: center;
    width: 166px;
}
.newscenterBoxArea .tuvenContent .fourBox {
    float: left;
    overflow: hidden;
    padding-top: 15px;
    width: 710px;
}
.rightPlayBox .play {
    background: url("http://pic.v1.cn/cms/v1_static/common/images/play_s.png") repeat scroll 0 0 transparent;
    height: 26px;
    left: 5px;
    overflow: hidden;
    position: absolute;
    top: 75px;
    width: 26px;
    z-index: 100;
}
.rightPlayBox .mask {
    background: url("http://pic.v1.cn/cms/v1_static/common/images/mask.png") repeat scroll 0 0 transparent;
    height: 24px;
    left: 0;
    line-height: 24px;
    position: absolute;
    text-align: center;
    top: 102px;
    width: 224px;
    overflow: hidden;
}
.wrapBox1000 .doubleBox .rightpicBox img {
    display:block;
}
</style>
<?php
$body = explode('<div class="contact">', $items[2]);
echo $body[0]."\n</div>\n</div>\n";
?>
<!-- START baidu v1.0 -->
<script type="text/javascript">
var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3Fcb44eb450c53c91a1cc1d2511f5919b3' type='text/javascript'%3E%3C/script%3E"));
</script>
<!-- END baidu v1.0 -->
<script type="text/javascript" src="http://s8.qhimg.com/!2dd30042/postIfrHeight.js"></script>
</body></html>
