<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function _initLogging() {

        // Need to get DB adapter first as its not initaliased at this stage
        $this->bootstrap('db'); // Bootstrap the db resource from configuration

        $db = $this->getResource('db');

        // General Logging required?
        $tblConfig = new Application_Model_DbTable_Configuration();
        $text = $tblConfig->getConfiguration('SYSTEM', 'LOG_INFO');

        $log = FALSE;

        If ($text == 'Y') {
            $log = TRUE;
        }

        Zend_Registry::set('LOG_INFO', $log);
    }

    protected function _initVersion() {

        include_once 'FssLibrary.php';

        $msg = getCurrentUser() . " Logged Into f_Training";
        Fss_Log::addLog($msg);

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');

        $frontend = array(
            'lifetime' => 86400,
            'automatic_serialization' => true
        );

        $backend = array(
            'cache_dir' => './FormaServe/Cache'
        );

        $cache = Zend_Cache::factory('Core', 'File', $frontend, $backend);

        Zend_Registry::set('cache', $cache);

        //Cache table metadata
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
    }

    protected function _initMail() {
        $tr = new Zend_Mail_Transport_Smtp("81.142.39.106", array('port' => 25)) ;
        Zend_Mail::setDefaultTransport($tr) ;
    }

    protected function _initConstants() {
        $options = $this->getOption('constants');

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if (!defined($key)) {
                    define($key, $value);
                }
            }
        }
    }

    protected function _initABD() {

        if (!defined('ABD')) {
            $msg = "Checking ABD failed - Contact FormaServe Systems";
            Fss_Log::addLog($msg);
            die();
        }
    }

}



