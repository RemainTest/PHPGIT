<?php

/**
 * Course Students DB Class
 * 
 * Used for all DB access 
 *  
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2012 FormaServe Systems Ltd 
 * @project     Training
 * 
 */
class Application_Model_DbTable_CourseStudents extends Zend_Db_Table_Abstract {

    protected $_name = 'COURSE_STUDENTS';
    protected $_primary = 'ID';

    /**
     * Add Course/Student Details
     * 
     * @param int $enq_no Enquiry Number
     * @param int $code Course Code
     * @param int $student Student ID
     * @param string $value ON/OFF Field to indicate if the student is on that course
     */
    public function addDetails($enq_no, $code, $student, $value) {

        If ($value == 'on') {
            $value_flag = 1;
        } else {
            $value_flag = NULL;
        }

        $data = array(
            'ENQ_NO' => $enq_no,
            'COURSE_CODE' => $code,
            'STUDENT_NO' => $student,
            'VALUE' => $value_flag
        );

        $where = array(
            'ENQ_NO = ?' => $enq_no,
            'COURSE_CODE = ?' => $code,
            'STUDENT_NO = ?' => $student
        );

        $select = $this->select();
        $select->from($this->_name, 'Count(*) as count')
                ->where('ENQ_NO = ?', $enq_no)
                ->where('COURSE_CODE = ?', $code)
                ->where('STUDENT_NO = ?', $student)
        ;

        $row = $this->fetchRow($select);

        if ($row->count == 1) {
            $this->update($data, $where);
        } else {
            $this->insert($data);
        }

        return;
    }

    /**
     * Is a Student On A Course?
     * 
     * @param int $enq_no Enquiry Number
     * @param int $code Course Code
     * @param int $student Student ID
     * @return Bool Yes if on course, No if not
     */
    public function isOnCourse($enq_no, $code, $student) {

        //fss_log::addLog('Into the isOnCourse function');
        $flag = FALSE;

        //$db = Zend_Db_Table::getDefaultAdapter();
        //$db->getProfiler()->setEnabled(true);

        //fss_log::addLog('After the getProfiler');
        
        $select = $this->select();

        $select->from($this->_name, '*')
                ->where('ENQ_NO = ?', $enq_no)
                ->where('COURSE_CODE = ?', $code)
                ->where('STUDENT_NO = ?', $student)
        ;

        $row = $this->fetchRow($select);
        
        //fss_log::addLog('SQL:' . $select);

        //try {
            //$row = $this->fetchRow($select);
        //} catch (Exception $e) {
        //    fss_log::addLog('Error:' . $e->getMessage());
        //    fss_log::addLog('SQL:' . $db->getProfiler()->getLastQueryProfile()->getQuery());
        //    $parms = $db->getProfiler()->getLastQueryProfile()->getQueryParams();

            // Loop thro parameter list
            //foreach ($parms as $option => $value) {
              //  fss_log::addLog('SQL Parm: ' . $option . ' = ' . $value);
            //}

            //$db->getProfiler()->setEnabled(false);
        //}

        if (count($row) > 0) {
            If ($row->VALUE == 1) {
                $flag = TRUE;
            }
        }

        return $flag;
    }

}
