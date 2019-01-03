<?php

/**
 * Error Handling
 * 
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright © 1990-2013 FormaServe Systems Ltd 
 * @project     f_Helpdesk
 * @category    Tickets
 * @package     f_Helpdesk
 * @subpackage  Tickets
 * @version     1.0.0
 * @since       File available since 1.0.0
 * @link        http://www.formaserve.co.uk 
 * 
 */

class ErrorController extends Zend_Controller_Action {

    private $_notifier;
    private $_error;
    private $_environment;

    public function init() {

        parent::init();

        $bootstrap = $this->getInvokeArg('bootstrap');

        $environment = $bootstrap->getEnvironment();
        $error = $this->_getParam('error_handler');
        $mailer = new Zend_Mail();
        $session = new Zend_Session_Namespace();
        $database = $this->getInvokeArg('bootstrap')->getResource('db');
        $profiler = $database->getProfiler();

        $this->_notifier = new Fss_Error(
                $environment, $error, $mailer, $session, $profiler, $_SERVER
        );

        $this->_error = $error;
        $this->_environment = $environment;
    }

    public function errorAction() {

        $errors = $this->_getParam('error_handler');
        //$this->view->headTitle('Training - Errors');
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/error.js'));
        $this->_helper->layout->enableLayout();
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Unable to find page';
                break;

            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application Error!';
                break;
        }

        $this->message1 = $this->view->message;
        $this->message2 = $errors->exception->getmessage();

        fss_log::addLog('Error: ' . $this->message1, $priority);
        fss_log::addLog('Message: ' . $this->message2, $priority);

        if (isset($this->view->exception)) {
            $this->error1 = $this->view->exception->getMessage();
            fss_log::addLog($this->error1, $priority);
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
        $this->request = $errors->request;

        $this->error2 = $this->view->exception->getTraceAsString();
        $this->error3 = var_export($this->view->request->getParams(), true);

        fss_log::addLog($this->view->exception->getTraceAsString(), $priority);
        fss_log::addLog(var_export($this->view->request->getParams(), true), $priority);

        $this->_notifier->notify();
    }

    public function getLog() {

        $bootstrap = $this->getInvokeArg('bootstrap');

        if (!$bootstrap->hasResource('Log')) {
            return false;
        }

        $log = $bootstrap->getResource('Log');

        return $log;
    }

}

