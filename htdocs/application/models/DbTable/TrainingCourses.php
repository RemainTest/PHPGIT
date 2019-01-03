<?php

/**
 * Enquiries DB Class
 * 
 * Used for all DB access of the Enquiries Table
 *  
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2012 FormaServe Systems Ltd 
 * @project     Training
 * 
 */
class Application_Model_DbTable_TrainingCourses extends Zend_Db_Table_Abstract {

    protected $_name = 'TRAINING_COURSES';
    protected $_primary = 'COURSE_CODE';

    /**
     * Get All Courses for a Enquiry
     * 
     */
    public function getAllCourses($enq_no) {

        $select = $this->select()
                ->from($this->_name, '*')
                ->where('ENQUIRY_NUMBER = ?', $enq_no)
                ->order('START_DATE ASC')
        ;

        $rows = $this->fetchAll($select);

        if (!$rows) {
            return null;
        }

        return $rows;
    }

    /**
     * Add a course
     * 
     * @return 
     */
    public function addCourse($code, $enq_no, $start_date, $students, $days, $venue, $instructor, $comments) {

        if ($start_date == '') {
            $start_date = null;
        } else {
            $start_date = convertFullDateToISO($start_date);
        }

        $data = array(
            'COURSE_CODE' => $code,
            'ENQUIRY_NUMBER' => $enq_no,
            'START_DATE' => $start_date,
            'NO_OF_STUDENTS' => $students,
            'NO_OF_DAYS' => $days,
            'VENUE' => trim($venue),
            'INSTRUCTOR' => trim($instructor),
            'COMMENTS' => trim($comments)
        );

        $this->insert($data);

        // Adding a new Enquiry auto generates a log entry
        $entry = "Course: " . trim($code) ." Added";

        // Log Table
        $tblLog = new Application_Model_DbTable_LogDetails();
        $tblLog->addLog($enq_no, $entry, 'N', 'N', 'Y');

        return;
    }

    /**
     * Update Course Details
     * 
     */
    public function updateCourse($enq_no, $no, $code, $start_date, $students, $days, $venue, $instructor, $comments )
     {

        if ($start_date == '') {
            $start_date = null;
        } else {
            $start_date = convertFullDateToISO($start_date);
        }

        $data = array(
            'COURSE_CODE' => trim($code),
            'START_DATE' => trim($start_date),
            'NO_OF_STUDENTS' => trim($students),
            'NO_OF_DAYS' => trim($days),
            'VENUE' => trim($venue),
            'INSTRUCTOR' => trim($instructor),
            'COMMENTS' => trim($comments)
        );

        $where['NUMBER = ?'] = $no;

        $this->update($data, $where);

        $entry = "Course: " . $code . " Details Updated";

        // Log Table
        $tblLog = new Application_Model_DbTable_LogDetails();
        $tblLog->addLog($enq_no, $entry, 'N', 'N', 'Y');

        return;
    }

    /**
     * Get A Course
     * 
     */
    public function getACourse($no) {

        $select = $this->select()
                ->from($this->_name, '*')
                ->where('NUMBER = ?', $no)
        ;

        $row = $this->fetchRow($select);

        if (!$row) {
            return null;
        }

        return $row;
    }
    
    /*
     * Delete a Course
     * 
     */

    public function deleteACourse($enq_no = 0, $no = 0, $code = 0) {

        If ($no > 0) {

            $where = array(
                'NUMBER = ?' => $no,
            );

            $this->delete($where);

            $entry = "Course: " . trim($code) . " Deleted";

            // Log Table
            $tblLog = new Application_Model_DbTable_LogDetails();
            $tblLog->addLog($enq_no, $entry, 'N', 'N', 'Y');
        }

        return;
    }

    /**
     * Count number of courses for an enquiry
     * 
     */
    public function countCourses($enq_no) {

        $select = $this->select();

        $select->from($this->_name, 'Count(*) as count')
                ->where('ENQUIRY_NUMBER = ?', $enq_no)
        ;

        $row = $this->fetchRow($select);

        return $row->count;
    }

}

