<?php
class View_Helper_FormRadioNoWrap
{
    protected $_view = null;
    
    public function formRadioNoWrap($name, $value, $attribs, $options)
    {
        $html = $this->_view->formRadio($name, $value, $attribs, $options);
        return str_replace('<br />', '&nbsp;', $html);
    }
    
    public function setView($view)
    {
        $this->_view = $view;
    }
}