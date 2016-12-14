<?php
/** @see Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

class ErrorController extends Zend_Controller_Action
{
    public function errorAction()
    {
        $this->view->layout()->disableLayout();
        $this->getResponse()->clearBody();
        //$this->view->setScriptPath('../application/sys/views/scripts/');
        
        $errors = $this->_getParam('error_handler');
        
        $type = get_class($errors->exception);
        $this->view->type = $type;
        
        if ($type == 'UserException')
        {
            $this->view->message = $errors->exception->getMessage();
            $this->view->url = $errors->exception->getUrl();

            return true;
        }
                                
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error                
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }
        
        $this->view->env       = $this->getInvokeArg('env');
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
    }
}