<?php
class View_Helper_Param
{
    public function param($key, $default = '')
    {
        $g = isset($_GET[$key]) ? $_GET[$key] : $default;
        $p = isset($_POST[$key]) ? $_POST[$key] : $g;
        
        return $p;
    }
}