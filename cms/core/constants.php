<?php
/**
 * Constants
 */
define('ROOT_PATH', rtrim(realpath('..'), '/') . '/');
//define('ROOT_PATH',dirname(dirname(__FILE__)).'/');
define('DATA_PATH', ROOT_PATH . 'data/');
define('LIBRARY_PATH', ROOT_PATH . 'library/');
define('MODULES_PATH', ROOT_PATH . 'modules/');
define('WEB_PATH', ROOT_PATH . 'public/');
define('TIME_NOW', empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time());

define('COOKIE_PREFIX', '');
define('ENV', 'development');
define('CONFIG_FILE', ROOT_PATH . 'config.ini');
define('SETTINGS_FILE', ROOT_PATH . 'settings.ini');
define('MENU_CONFIG_FILE', ROOT_PATH . 'menu.ini');

define('ARTICLE_ALBUM_INTERFACE', "http://img2008.titan24.com/interface/album.php?id=%s");
