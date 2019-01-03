<?php
/**
 * FormaServe - Error Handling
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
class Fss_Error {

    protected $_environment;
    protected $_mailer;
    protected $_session;
    protected $_error;
    protected $_profiler;

    public function __construct(
    $environment, ArrayObject $error, Zend_Mail $mailer, Zend_Session_Namespace $session, Zend_Db_Profiler $profiler, Array $server) {
        $this->_environment = $environment;
        $this->_mailer = $mailer;
        $this->_error = $error;
        $this->_session = $session;
        $this->_profiler = $profiler;
        $this->_server = $server;
    }

    public function getFullErrorMessage() {
                
        $message = "<p>Server Date/Time: " . date("d M Y H:i:s") . "</p>";
        
        if (!empty($this->_server['REMOTE_USER'])) {
            $message .= "<p>User: " . $this->_server['REMOTE_USER'] . "</p>";
        }
        
        if (!empty($this->_server['SERVER_NAME'])) {
            $message .= "<p>Server Name: " . strtoupper($this->_server['SERVER_NAME']) . "</p>";
        }
        
        if (!empty($this->_server['QUERY_STRING'])) {
            $message .= "<p>URL Parameters: " . $this->_server['QUERY_STRING'] . "</p>";
        }
        
        if (!empty($this->_server['SERVER_ADDR'])) {
            $message .= "<p>Server IP: " . $this->_server['SERVER_ADDR'] . "</p>";
        }

        if (!empty($this->_server['HTTP_USER_AGENT'])) {
            $message .= "<p>User agent: " . $this->_server['HTTP_USER_AGENT'] . "</p>";
        }

        if (!empty($this->_server['HTTP_X_REQUESTED_WITH'])) {
            $message .= "<p>Request type: " . $this->_server['HTTP_X_REQUESTED_WITH'] . "</p>";
        }

        $message .= "<p>Request URI: " . $this->_error->request->getRequestUri() . "</p>";

        if (!empty($this->_server['HTTP_REFERER'])) {
            $message .= "<p>Referer: " . $this->_server['HTTP_REFERER'] . "</p>";
        }

        $message .= "<p>Error: " . $this->_error->exception->getMessage() . "</p>";
        $message .= "<p>Trace: " . $this->_error->exception->getTraceAsString() . "</p>";
        $message .= "<p>Request Data: " . var_export($this->_error->request->getParams(), true) . "</p>";

        $it = $this->_session->getIterator();

        $message .= "<p>Session data:</p>";
        
        foreach ($it as $key => $value) {
            $message .= "<p>". $key . ": " . var_export($value, true) . "</p>";
        }
        
        $message .= "<br>";

        return $message;
    }

    public function getShortErrorMessage() {
        $message = '';

        switch ($this->_environment) {
            case 'live':
                $message .= "It seems you have just encountered an unknown issue.";
                $message .= "Our team has been notified and will deal with the problem as soon as possible.";
                break;
            default:
                $message .= "Message: " . $this->_error->exception->getMessage() . "\n\n";
                $message .= "Trace:\n" . $this->_error->exception->getTraceAsString() . "\n\n";
        }

        return $message;
    }

    public function notify() {
        /*
          if (!in_array($this->_environment, array('live', 'stage'))) {
          return false;
          }
         * 
         */

        // Get parameters from application.ini
        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');

        // Get parameters from the config file - use these first
        $tblConfig = new Application_Model_DbTable_Configuration;
        $from = $tblConfig->getConfiguration('SYSTEM', 'SMTP_FROM');
        $from_name = $tblConfig->getConfiguration('SYSTEM', 'SMTP_FROMNAME');
        $smtp_server = $tblConfig->getConfiguration('SYSTEM', 'SMTP_SERVER');
        $smtp_port = $tblConfig->getConfiguration('SYSTEM', 'SMTP_PORT');
        $to_email = $tblConfig->getConfiguration('SYSTEM', 'ERROR_EMAIL');

        // Use application.ini if nuffin in the config file
        $mailkeys = $config->getOption('resources');
        
        If (trim($from) == '') {
            $from = $mailkeys['mail']['defaultFrom']['email'];
        }

        If (trim($from_name) == '') {
            $from_name = $mailkeys['mail']['defaultFrom']['name'];
        }

        If (trim($smtp_server) == '') {
            $smtp_server = $mailkeys['mail']['transport']['host'];
        }

        If (trim($smtp_port) == '') {
            $smtp_server = 25;
        }
                
        $this->_mailer->setFrom($from, $from_name)
                ->setSubject("f_Training - Error Report")
                ->addTo($to_email)
        ;

        // Initialise vars
        $body = "<style>
    h2 {
	color: #5F9EA0 ;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 1em;
	font-weight:normal;
    }
    h3 {
	color: #000066 ;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 0.9em;
	font-weight:normal;
    }
    p {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 0.8em;
    color: #003366 ;
}
    table {
	background-color: whiteSmoke;
	border-radius: 6px;
	-webkit-border-radius: 6px;
	-moz-border-radius: 6px;
    }
    th {
	color: #8FBC8F;
	font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
	font-size: 12px;
	font-style: normal;
	font-weight: normal;
	text-align: left;
	padding: 0 20px;
    }
    td {
	padding: 0 20px;
	line-height: 20px;
	color: #0084B4;
	font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
	font-size: 12px;
	border-bottom: 1px solid #fff;
	border-top: 1px solid #fff;
    }
       </style>";

        $body .= "<head><body>";
        $body .= "<h2>f_Helpdesk Error</h2>";
        $body .= "<p><b>Message:</b> " . $this->getFullErrorMessage() . "</p>";
        $body .= "<br>";
        $body .= '<h2>Please do not reply to this email</h2>';

        $this->_mailer->setBodyHtml($body);

        //Zend_Debug::dump($this->_mailer) ;
        //die() ;
        
        //fss_log::addLog("Error Mail Ready To Send!");
        
        $transport = new Zend_Mail_Transport_Smtp($smtp_server);
        Zend_Mail::setDefaultTransport($transport);
        
        return $this->_mailer->send();
    }

}

