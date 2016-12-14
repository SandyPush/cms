<?php
//执行任意查询,比如sb亚运会赛程数据
function Query($sql)
{
	if ($sql == '') {
        return false;
    }
	if (strpos(strtolower($sql), 'select')!== 0) {
        return false;
    }
	$sql= preg_replace(array("/insert/i", "/update/i", "/delete/i","/ALTER/i","/TRUNCATE/i","/DROP/i"), '', $sql);
	$env_config = new Zend_Config_Ini(CONFIG_FILE);	
	$db = Zend_Db::factory('PDO_MYSQL', $env_config->matchdb);	
	$db->query('set names utf8');		
	$data = $db->fetchAll($sql);
	return $data;
}