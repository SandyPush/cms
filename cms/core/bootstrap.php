<?php
/*
 * bootstrap
 */
require_once 'constants.php';

error_reporting(ENV == 'development' ? 7 : 0);
setlocale(LC_ALL,NULL);
date_default_timezone_set('Asia/Shanghai');
ini_set('session.gc_maxlifetime', 12 * 3600);
//echo ini_get('session.gc_maxlifetime');

set_include_path(LIBRARY_PATH . PATH_SEPARATOR
. ROOT_PATH . 'core/' . PATH_SEPARATOR
. ROOT_PATH . 'core/plugins/' . PATH_SEPARATOR
. ROOT_PATH . 'core/utilities/' . PATH_SEPARATOR
. ROOT_PATH . 'core/exceptions/' . PATH_SEPARATOR
. get_include_path());
require_once ROOT_PATH . 'core/utilities/util.class.php';
require_once ROOT_PATH . 'core/plugins/UserPlugin.php';
require_once ROOT_PATH . 'core/plugins/LayoutPlugin.php';
require_once ROOT_PATH . 'core/session.php';
require_once 'Zend/Loader.php';

Zend_Loader::registerAutoload();

Zend_Loader::loadClass('Zend_Layout');
Zend_Loader::loadClass('Zend_Config');
Zend_Loader::loadClass('Zend_Debug');

// controllers_path
$controllers_path = array();
$env_config = new Zend_Config_Ini(CONFIG_FILE);
$settings = new Zend_Config_Ini(SETTINGS_FILE);
foreach ($settings->modules as $module => $name)
{
	$controllers_path[$module] = ROOT_PATH . 'modules/' . $module . '/controllers/';
}

// layout
$layout_options = array(
    'layout' => 'content',
    'layoutPath' => ROOT_PATH . 'modules/system/views/layouts',
);
$layout = Zend_Layout::startMvc($layout_options);

// view helpers path
$layout->getView()->addHelperPath(ROOT_PATH . 'core/helpers/', 'View_Helper_');

Zend_Registry::set('env_config', $env_config);
Zend_Registry::set('settings', $settings);

// session
$db = Util::getDbAdapter($env_config->db);
Zend_Db_Table_Abstract::setDefaultAdapter($db);

$config = array(
    'name'           => 'session', 
    'primary'        => 'id', 
    'modifiedColumn' => 'modified',
    'dataColumn'     => 'data',
    'lifetimeColumn' => 'lifetime' 
);
Zend_Session::setSaveHandler(new Titan_Session_SaveHandler_DbTable($config));

//for swfupload
if (isset($_POST["PHPSESSID"])) {
	Zend_Session::setId($_REQUEST["PHPSESSID"]);
}

//start your session! 
Zend_Session::start();

// clean globals
//set_magic_quotes_runtime(0);
foreach (array('_POST', '_GET', '_REQUEST', '_COOKIE') as $val) {
    Util::cleanGlobals(${$val});
}

// front controller
$front = Zend_Controller_Front::getInstance();
//$front->setBaseUrl('/CMS/cms2/code/public');
$front->setControllerDirectory($controllers_path);
$front->setDefaultModule('system');

if(Zend_Session::namespaceIsset('user')){
   $user=Zend_Session::namespaceGet('user');
}else{
   $specialSource=array(
			//游戏spider导入
                        "192.168.9.122"=>array("uid"=>999,
                                            "username"=>"spiderForHGame",
                                            "realname"=>"H5游戏",
                                            "email"=>"handong@v1.cn",
                                            "channel"=>$_GET["channel"]
                                            ),
                        );

//file_put_contents("/www/img/log.txt",$_SERVER['REMOTE_ADDR']."\r\n",FILE_APPEND);
   $clientIP=$_SERVER['HTTP_X_REAL_IP'] ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
   if($specialSource[$clientIP] and $_GET["channel"]){
      $user['uid']=$specialSource[$clientIP]['uid'];
      $user['username']=$specialSource[$clientIP]['username'];
      $user['realname']=$specialSource[$clientIP]['realname'];
      $user['email']=$specialSource[$clientIP]['email'];
      $user['channel']=$specialSource[$clientIP]['channel'];
      $_SESSION['user']=$user;
   }else{
      $front->registerPlugin(new UserPlugin())->registerPlugin(new LayoutPlugin());
   }
}

if($user['channel'])
{
    $channel_config=new Zend_Config_Ini(ROOT_PATH . 'config.'.$user['channel'].'.ini');
	$channel_db = Zend_Db::factory('PDO_MYSQL', $channel_config->db->toArray());
	Zend_Db_Table_Abstract::setDefaultAdapter($channel_db);
	$channel_db->query("SET NAMES 'utf8'");
	Zend_Registry::set('channel_db', $channel_db);
}

//$front->throwExceptions(false);
$front->setParam('env', ENV);
