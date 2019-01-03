<?php

/**
 * Log Detail Class
 * 
 * Used for all DB access of the Log Details Table
 *  
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2012 FormaServe Systems Ltd 
 * @project     Training
 * 
 */
class Application_Model_DbTable_LogDetails extends Zend_Db_Table_Abstract {

    protected $_name = 'LOG_DETAILS';
    protected $_primary = 'ENQ_NO';

    
    public function init() {

        $this->_currentUser = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_USER');
    }

    
    public function getLog($ticket_no, $log_no) {

        $select = $this->select()
                ->from($this->_name, '*')
                ->where('ENQ_NO = ?', $ticket_no)
                ->where('LOG_NO = ?', $log_no)
                ->order('LOG_NO DESC')
        ;

        $row = $this->fetchRow($select);

        if (!$row) {
            return null;
        }

        return $row;
    }

    /**
     * Add a log entry
     * 
     * Firstly, it must get the last log entry to increment the log counter by 10
     * 
     * @param int $ticket_no Ticket Number
     * @param string $entry Log Entry
     * @param string $attach Y/N field - Y if any attachments are included
     * @param string $edit Y/N field - Y if the log entry can be edited by the user
     * @param string $audit Y/N field - Y if the log entry is an automatically generated audit entry
     * 
     */
    public function addLog($no, $entry, $attach = 'N', $edit = 'N', $audit = 'N') {

        //Zend_Debug::dump($data);
        
        // only write a log entry if there is something to log!
        if (strip_tags($entry) <> '') {

            $log_no = 10;

            $db = Zend_Db_Table::getDefaultAdapter();

            $sql = "Select Max(log_no) From Log_Details where ENQ_NO = " . (int) $no  ;
            //echo "<p>$sql</p>" ;
            
            $result = $db->fetchOne($sql);

            If ($result > 0) {
                $log_no = $result + 10;
            }

            If ($edit == 'Y') {
                $edit_flag = 1;
            } else {
                $edit_flag = 0;
            }

            If ($attach == 'Y') {
                $attach_flag = 1;
            } else {
                $attach_flag = 0;
            }

            If ($audit == 'Y') {
                $audit_flag = 1;
            } else {
                $audit_flag = 0;
            }

            $data = array(
                'ENQ_NO' => $no,
                'LOG_USER' => $this->_currentUser,
                'LOG_NO' => $log_no,
                'ENTRY' => $entry,
                'EDIT_FLAG' => $edit_flag,
                'ATTACH_FLAG' => $attach_flag,
                'AUDIT_FLAG' => $audit_flag
            );

            $this->insert($data);
            //Zend_Debug::dump($data);
        }

        return;
    }

    /**
     * Count number of log entries for a ticket
     * 
     * @param int $ticket_no Ticket Number
     * @return int count of records found for the specified ticket number
     */
    public function countLog($no) {

        $select = $this->select();
        $select->from($this->_name, 'Count(*) as log_count');
        $select->where('ENQ_NO = ?', $no);

        $row = $this->fetchRow($select);

        return $row->log_count;
    }

    /**
     * Update an existing log entry for a ticket
     * 
     * @param int $ticket_no Ticket Number
     * @param int $log_no Log Number
     * @param string $entry The new entry to update the log with
     * @return
     */
    public function updateLog($no, $log_no, $entry) {

        $data = array(
            'ENTRY' => $entry,
            'CHANGE_USER' => $_SERVER['REMOTE_USER']
        );

        $where = array(
            'ENQ_NO = ?' => $no,
            'LOG_NO = ?' => $log_no
        );

        $this->update($data, $where);

        return;
    }

}

