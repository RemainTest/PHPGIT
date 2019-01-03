<?php

/**
 * Enquirers Details DB Class
 * 
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2012 FormaServe Systems Ltd 
 * @project     Training
 * 
 * Fix
 */
class Application_Model_DbTable_Enquirers extends Zend_Db_Table_Abstract {

    protected $_name = 'ENQUIRERS';
    protected $_primary = 'ID';

    public function init() {

        $this->_currentUser = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_USER');
    }

    /**
     * Count number of Enquirers for an enquiry
     * 
     * @param int $enq_no Enquiry Number
     * @return int count of records found
     */
    public function countEnquirers($enq_no) {

        $select = $this->select();
        $select->from($this->_name, 'Count(*) as count')
                ->where('ENQ_NO = ' . $enq_no);
        $row = $this->fetchRow($select);

        return $row->count;
    }

    /**
     * Get All Enquirers for an Enquiry
     * 
     * @param int $enq_no Enquiry Number
     * 
     * @return array|null Array of details or NULL if not found
     */
    public function getEnquirers($enq_no) {

        $select = $this->select()
                ->from($this->_name, '*')
                ->where('ENQ_NO = ' . $enq_no)
                ->order('ADD_TS DESC')
        ;

        $row = $this->fetchAll($select);

        if (!$row) {
            return null;
        }

        return $row;
    }

    /**
     * Add an Enquirer
     * 
     * @param 
     */
    public function addEnquirer($enq_no, $name) {


//        $msg = 'into addEnquirier for the model';
//
//        fss_log::addLog($msg);

        $data = array(
            'ENQ_NO' => (int) $enq_no,
            'ADD_USER' => $this->_currentUser,
            'NAME' => trim($name)
        );

        $this->insert($data);
    }

}
