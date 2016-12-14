<?php
require_once '../core/constants.php';

//error_reporting(ENV == 'development' ? E_ALL | E_STRICT : 0);
date_default_timezone_set('Asia/Shanghai');

set_include_path(LIBRARY_PATH . PATH_SEPARATOR
. ROOT_PATH . 'core/' . PATH_SEPARATOR
. ROOT_PATH . 'core/utilities/' . PATH_SEPARATOR
. get_include_path());

require_once ROOT_PATH . 'core/utilities/util.class.php';

require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

Zend_Loader::loadClass('Zend_Config');

//$env_config = new Zend_Config_Ini(CONFIG_FILE);
$user=Zend_Session::namespaceGet('user');
#$channel=$user['channel'];
$channel='qsl';
$env_config=new Zend_Config_Ini(ROOT_PATH . 'config.'.$channel.'.ini');
$settings = new Zend_Config_Ini(SETTINGS_FILE);

Zend_Registry::set('env_config', $env_config);
Zend_Registry::set('settings', $settings);
