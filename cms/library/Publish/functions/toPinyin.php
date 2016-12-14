<?
    require_once LIBRARY_PATH . 'class.pinyin.php';
    function toPinyin($str){
        return pinyin::toPinYin($str);
    }
?>