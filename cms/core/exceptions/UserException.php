<?php
require_once 'Zend/Exception.php';

class UserException extends Zend_Exception
{
    protected $goto;
    
    public function __construct($message = null, $code = 0, $goto = 'javascript: history.go(-1)')
    {
        $this->message = $message;
        $this->goto = $goto;
    }   
    
    public function getUrl()
    {
        return $this->goto;
    }
}
