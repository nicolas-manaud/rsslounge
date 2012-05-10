<?php

/**
 * Default errorcontroller given by Zend_Tool
 *
 * @package    application_controllers
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class ErrorController extends Zend_Controller_Action
{
    /**
     * shows the error stack
     *
     * @return void
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

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
        
        // database error
        if($errors->exception instanceof Zend_Db_Adapter_Exception) {
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Database error';
        }

        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
    }

}

