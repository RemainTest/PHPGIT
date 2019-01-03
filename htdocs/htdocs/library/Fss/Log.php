<?php

/**
 * FormaServe General Utilities
 * 
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2013 FormaServe Systems Ltd 
 * @project     f_Helpdesk
 * @category    Utilities
 * @package     Helpdesk
 * @subpackage  Utilities
 * @version     1.0.0
 * @since       File available since 1.0.0
 * @link        http://www.formaserve.co.uk 
 * 
 */
class Fss_Log {
    /*
     * Write a note to the log.  Will only write an entry if the config file allows it
     *  (SYSTEM	LOG_INFO = Y)
     * 
     * @param string $msg Message to log
     * @param int    $sev Message severity - defaults to 6 (Informational)
     * 
     * 
     * EMERG   = 0;  // Emergency: system is unusable
     * ALERT   = 1;  // Alert: action must be taken immediately
     * CRIT    = 2;  // Critical: critical conditions
     * ERR     = 3;  // Error: error conditions
     * WARN    = 4;  // Warning: warning conditions
     * NOTICE  = 5;  // Notice: normal but significant condition
     * INFO    = 6;  // Informational: informational messages
     * DEBUG   = 7;  // Debug: debug messages
     * 
     */

    public static function addLog($msg, $sev = Zend_Log::INFO) {

        If (trim($msg <> '')) {

            $log = Zend_Registry::get('LOG_INFO');
            
            
            If ($log) {
                
            // If an informational message, check if logging switched off
            If ($sev == 6) {

                /*                If (!getLogging()) {
                  return;
                  } */
            }

            $today = date("Y-m-d");
            $log = 'formaserve/log/fss_' . $today . '.log';
            //$msg = PHP_EOL . $msg ;

            $logger = new Zend_Log();
            $writer = new Zend_Log_Writer_Stream($log);
            $logger->addWriter($writer);

            $logger->log($msg, $sev);
            
            }

        }
    }

}

/*

 */
?>
