<?php

/**
 * Students DB Class
 * 
 * Used for all DB access of the Students Table
 *  
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2012 FormaServe Systems Ltd 
 * @project     Training
 * 
 */
class Application_Model_DbTable_Students extends Zend_Db_Table_Abstract {

    protected $_name = 'STUDENTS';
    protected $_primary = 'ENQUIRY_NUMBER';

    public function getStudents($enq_no) {

        $select = $this->select()
                ->from($this->_name, '*')
                ->where('ENQUIRY_NUMBER = ?', $enq_no)
                ->order(array('UPPER(SURNAME)',
                        'UPPER(FIRST_NAME)'))
        ;

        $rows = $this->fetchAll($select);

        return $rows;
    }

    public function getAStudent($id) {

        $select = $this->select()
                ->from($this->_name, '*')
                ->where('ID = ?', $id)
        ;

        $row = $this->fetchRow($select);

        return $row;
    }

    /**
     * Update Student Details
     * 
     */
    public function updateStudent($enq_no, $id, $first, $surname, $email, $content, $instructor, $facilities, $publish, $web, $comments) {

        $data = array(
            'FIRST_NAME' => trim(ucfirst(strtolower($first))),
            'SURNAME' => trim(ucfirst(strtolower($surname))),
            'EMAIL' => trim($email),
            'ASSESMENT_CONTENT' => $content,
            'ASSESMENT_INSTRUCTOR' => $instructor,
            'ASSESMENT_FACILITIES' => $facilities,
            'PUBLISHING_AGREEMENT' => $publish,
            'WEB_SHOW' => $web,
            'COMMENTS' => trim($comments)
        );

        $where = array(
            'ENQUIRY_NUMBER = ?' => $enq_no,
            'ID = ?' => $id
        );

        $this->update($data, $where);

        $entry = "Student: " . trim($first) . " " . trim($surname) . " Updated";

        // Log Table
        $tblLog = new Application_Model_DbTable_LogDetails();
        $tblLog->addLog($enq_no, $entry, 'N', 'N', 'Y');

        return;
    }

    /**
     * Count number of students for an enquiry
     * 
     */
    public function countStudents($enq_no) {

        $select = $this->select();

        $select->from($this->_name, 'Count(*) as count')
                ->where('ENQUIRY_NUMBER = ?', $enq_no)
        ;

        $row = $this->fetchRow($select);

        return $row->count;
    }

    /**
     * Add a student
     * 
     */
    public function addStudent($enq_no, $first, $surname, $email, $content, $instructor, $facilities, $agreement, $web ,$comments) {

        $data = array(
            'ENQUIRY_NUMBER' => (int) $enq_no,
            'FIRST_NAME' => trim(ucfirst(strtolower($first))),
            'SURNAME' => trim(ucfirst(strtolower($surname))),
            'EMAIL' => trim($email),
            'ASSESMENT_CONTENT' => (int) $content,
            'ASSESMENT_INSTRUCTOR' => (int) $instructor,
            'ASSESMENT_FACILITIES' => (int) $facilities,
            'PUBLISHING_AGREEMENT' => (int) $agreement,
            'WEB_SHOW' => (int) $web,
            'COMMENTS' => trim($comments)
        );

        $this->insert($data);

        $entry = "Student: " . trim($first) . " " . trim($surname) . " Added";

        // Log Table
        $tblLog = new Application_Model_DbTable_LogDetails();
        $tblLog->addLog($enq_no, $entry, 'N', 'N', 'Y');

        return;
    }

    /**
     * Delete a student
     * 
     */
    public function deleteStudent($enq_no, $id, $first, $surname) {

        $where = array('ID = ?' =>  $id);

        $this->delete($where);

        $entry = "Student: " . trim($first) . " " . trim($surname) . " Deleted";

        // Log Table
        $tblLog = new Application_Model_DbTable_LogDetails();
        $tblLog->addLog($enq_no, $entry, 'N', 'N', 'Y');

        return;
    }

}

