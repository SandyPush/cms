<?php
/** @see Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

class LayoutPlugin extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$modules = Zend_Registry::get('settings')->modules->toArray();
        
    	$_module = $this->getRequest()->getModuleName();
    	$_controller = $this->getRequest()->getControllerName();
    	$_action = $this->getRequest()->getActionName();
    	/*
    	$menu_config = new Zend_Config_Ini(MENU_CONFIG_FILE);
    	$menu = '<ul>';
    	foreach ($menu_config->toArray() as $module => $controllers) {
    	    $class = $module == $_module ? ' class="current"' : '';
    	    $menu .= '<li ' . $class . 'id="menu_' . $module . '">';
    	    $menu .= '<h3>' . $controllers['name'] . '</h3>';
    	    unset($controllers['name']);
    	    foreach ($controllers as $controller => $actions) {
    	      
    	        if (preg_match('/ext_+/', $controller)) {
    	            $menu .= '<div class="menu_sep"></div>';
    	            continue;
    	        }
    	        
    	        $menu .= '<ul>';
    	        if (preg_match('/ext_+/', $controller)) {
    	           $item = $controllers[$controller];
    	           $menu .= '<li><a href="' . $item['link'] . '" target="_blank">' . $item['name'] . '</a></li>';
    	           $menu .= '</ul>';
    	           continue;
    	        }
    	        
    	        foreach ($actions as $act => $item) {
    	            $id = 'menu_' . $module . '_' . $controller . '_' . $act;
    	            $class = ($module == $_module && $controller == $_controller && $act == $_action) ? ' class="current"' : '';
    	            $menu .= '<li><a ' . $class . 'href="' . $item['link'] . '" id="' . $id . '">' . $item['name'] . '</a></li>';
    	        }
    	        $menu .= '</ul>';
    	    }
    	    $menu .= '</li>';
    	}
    	
    	//$menu = 'menu';
    	
    	$this->getResponse()->append('menu', $menu);
		*/
    }
}