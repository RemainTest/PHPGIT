<?php

/**
 * FormaServe - Db Profiler
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
class Fss_DbProfiler extends Zend_Db_Profiler {

    protected $_lastQueryText;
    protected $_lastQueryType;

    /**
     * Zend_Log instance
     * @var Zend_Log
     */
    protected $_log;

    /**
     * counter of the total elapsed time
     * @var double 
     */
    protected $_totalElapsedTime;

    /**
     * The filename to save the queries
     *
     * @var string
     */
    protected $_filename;

    /**
     * The file handle
     *
     * @var resource
     */
    protected $_handle = null;

    /**
     * Class constructor
     *
     * @param string $filename
     */
    public function __construct($enabled = false) {
        parent::__construct($enabled);

        $today = date("Y-m-d");
        $filename = 'formaserve/log/fss_db_' . $today . '.log';
        $this->_filename = $filename;

        $this->_log = new Zend_Log();
        $writer = new Zend_Log_Writer_Stream($filename);
        $this->_log->addWriter($writer);
    }

    /**
     * Intercept the query end and log the profiling data.
     *
     * @param  integer $queryId
     * @throws Zend_Db_Profiler_Exception
     * @return void
     */
    public function queryEnd($queryId) {

        $state = parent::queryEnd($queryId);

        if (!$this->getEnabled() || $state == self::IGNORED) {
            return;
        }

        // get profile of the current query
        $profile = $this->getQueryProfile($queryId);

        // update totalElapsedTime counter
        $this->_totalElapsedTime += $profile->getElapsedSecs();

        // create the message to be logged
        $message = "\r\nElapsed Secs: " . round($profile->getElapsedSecs(), 5) . "\r\n";
        $message .= "Query: " . $profile->getQuery() . "\r\n";

        // log the message as INFO message
        $this->_log->info($message);
    }

    /**
     * Change the profiler status. If the profiler is not enabled no
     * query will be written to the destination file
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled) {

        parent::setEnabled($enabled);

        /*
          if ($this->getEnabled()) {
          if (!$this->_handle) {
          if (!($this->_handle = @fopen($this->_filename, "a"))) {
          throw new Exception("Unable to open filename {$this->_filename} for query profiling");
          }
          }
          } else {
          if ($this->_handle) {
          @fclose($this->_handle);
          }
          } */
    }

    public function queryStart($queryText, $queryType = null) {
        $this->_lastQueryText = $queryText;
        $this->_lastQueryType = $queryType;

        return null;
    }

    public function getQueryProfile($queryId) {
        return null;
    }

    public function getLastQueryProfile() {
        $queryId = parent::queryStart($this->_lastQueryText, $this->_lastQueryType);

        return parent::getLastQueryProfile();
    }

}
