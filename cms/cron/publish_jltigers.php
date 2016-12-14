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
$channel='jltigers';
$env_config=new Zend_Config_Ini(ROOT_PATH . 'config.'.$channel.'.ini');
$settings = new Zend_Config_Ini(SETTINGS_FILE);

Zend_Registry::set('env_config', $env_config);
Zend_Registry::set('channel_config', $env_config);
Zend_Registry::set('settings', $settings);


require_once LIBRARY_PATH . 'Publish/Abstract.php';
require_once LIBRARY_PATH . 'Publish/Article.php';
require_once LIBRARY_PATH . 'Publish/Page.php';
require_once LIBRARY_PATH . 'Publish/Focus.php';
require_once LIBRARY_PATH . 'Publish/Album.php';

$opts = new Zend_Console_Getopt('afph');
$opts->setHelp(
    array(
        'a' => '文章',
        'f' => '专题',
        'p' => '栏目',
        'h' => '帮助'
    )
);

try {
    $flags = $opts->getOptions();
    $ids = $opts->getRemainingArgs();
} catch(Exception $e) {
    echo $opts->getUsageMessage();
    exit(1);
}

$type = $flags[0];

foreach ($ids as $id) {
    switch ($type) {
        case 'p':
            $pub = new Publish_Page(Zend_Registry::get('env_config'), $id);
            break;
        case 'f';
            $pub = new Publish_Focus(Zend_Registry::get('env_config'), $id);
            break;
        case 'a':
            $pub = new Publish_Article(Zend_Registry::get('env_config'), $id);
            break;
    }
   
    try {
        $pub->publish();
    } catch (Exception $e) {
        //echo "$type$id error:";
    }
    
    echo "$type$id done\n";
}

echo "\nall done\n";
