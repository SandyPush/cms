<?php
class View_Helper_ShowErrors
{
	protected $_view = null;
	
    public function showErrors($limit = 1)
    {
    	if (!$this->_view->error_message) {
    		return '';
    	}
    	
    	return '<div class="div_errors" style="width:100%;margin:200px auto;" align="center">' . $this->_view->error_message . '</div>';
    }
    
    public function setView($view)
    {
    	$this->_view = $view;
    }
}