<?php

/**
 * Customers Details DB Class
 * 
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2012 FormaServe Systems Ltd 
 * @project     Training
 * 
 */
class Application_Model_DbTable_Customers extends Zend_Db_Table_Abstract {

    protected $_name = 'OMCUS';
    protected $_primary = 'ID';

    public function init() {

        $this->_currentUser = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_USER');
    }

    /**
     * Count number of Customers for an enquiry
     * 
     * @param int $enq_no Enquiry Number
     * @return int count of records found
     */
    public function countCustomers($enq_no) {

        $select = $this->select();
        $select->from($this->_name, 'Count(*) as count')
                ->where('USCUSN = ' . $enq_no);
        $row = $this->fetchRow($select);

        return $row->count;
    }

    /**
     * Get All Customers for an Enquiry
     * 
     * @param int $enq_no Enquiry Number
     * 
     * @return array|null Array of details or NULL if not found
     */
    public function getECustomers($enq_no) {

        $select = $this->select()
                ->from($this->_name, '*')
                ->where('USCUSN = ' . $enq_no)
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
            'USCUSN' => (int) $enq_no,
            'ADD_USER' => $this->_currentUser,
            'NAME' => trim($name)
        );

        $this->insert($data);
    }

}
