<?php

/**
 * Maintenance
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

class MaintenanceController extends Zend_Controller_Action {

    public function init() {

        $this->view->headTitle('f_Helpdesk Maintenance');
    }

    public function indexAction() {

        // include page specific js & css
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/maintenance.js'));

        if ($this->getRequest()->isPost()) {

            $maint = $this->getRequest()->getPost('maint');

            // Now delete it!
            if ($maint == 'Confirm') {

                $log_date = $this->getRequest()->getParam('log_date');

                If ($log_date) {

                    $this->view->count = $this->deleteLogs($log_date);
                    $this->view->log_date = $log_date;

                    // Show results
                    $this->view->log_popup = "true";
                }
            }

            // All done!
            //$this->_helper->redirector->gotourl('/maintenance/index/');
        }
    }

    /**
     * Used to delete log files over a certain age
     * 
     * 
     * 
     */
    public function deleteLogs($date) {

        $i = 0;
        $start = $date;
        $end = date("dMY");

        $datetime1 = new DateTime($start);
        $datetime2 = new DateTime($end);
        $interval = $datetime1->diff($datetime2);

        $days = $interval->days;

        // Get log location
        $tblConfig = new Application_Model_DbTable_Configuration();
        $dir = $tblConfig->getConfiguration('SYSTEM', 'LOG_LOCATION');

        $unixtime = time();
        $baseline = $unixtime - ( 86400 * $days );

        //Zend_Debug::dump(gmdate("Y-m-d", $unixtime));
        //Zend_Debug::dump(gmdate("Y-m-d", $baseline));

        $files = array();

        foreach (new DirectoryIterator($dir) as $fileInfo) {

            if ($fileInfo->getFileName() == "." || $fileInfo->getFileName() == "..")
                continue;

            If ($fileInfo->getCTime() < $baseline) {
                //$files[$fileInfo->getFileName()] = $fileInfo->getCTime();
                $full_name = trim($dir) . $fileInfo->getFileName();

                try {
                    $rc = unlink($full_name);
                    // Echo "<p>Deleted file: $full_name</p>";
                    $i++;
                } catch (Exception $e) {
                    echo 'Unable to Delete File', $e->getMessage(), "\n";
                }
            }
        }

        //$files[$fileInfo->getFileName()] = $fileInfo->getCTime();
        //Zend_Debug::dump($files);

        Return $i;
    }

}

