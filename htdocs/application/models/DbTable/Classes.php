<?php

/**
 * Classes Details DB Class
 * 
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2017 FormaServe Systems Ltd 
 * @project     Training
 * 
 */
class Application_Model_DbTable_Classes extends Zend_Db_Table_Abstract {

    protected $_name = 'CLASSES';
    protected $_primary = 'CODE';

    public function init() {

        $this->_currentUser = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_USER');
    }

    /**
     * Count number of Classes for an enquiry
     * 
     * @param int $enq_no Enquiry Number
     * @return int count of records found
     */
    public function countClasses($enq_no) {

        $select = $this->select();
        $select->from($this->_name, 'Count(*) as count');
        $select->where('ENQUIRY_NUMBER = ' . $enq_no);
        $row = $this->fetchRow($select);

        return $row->count;
    }

    /**
     * Get All Classes
     * 
     * @return array|null Array of product details or NULL if not found
     */
    public function getClasses() {

        $select = $this->select()
                ->from($this->_name, '*')
                ->order('CODE')
        ;

        $row = $this->fetchAll($select);

        if (!$row) {
            return null;
        }

        return $row;
    }

    public function getTitle($code) {

        $row = $this->fetchRow(
                $this->select()
                        ->where('CODE = ?', $code)
        );

        return $row->TITLE;
    }

    /**
     * Get a class details from a course code, if not found, give defaults
     *   
     * @param string $code
     * @return $row Class details
     */
    public function getClass($code = NULL) {

        If ($code) {
            $rowset = $this->fetchRow(
                    $this->select()
                            ->where('CODE = ?', $code)
            );

            if ($rowset) {
                $row = $rowset;
            } else {
                $row = $this->getClassDefaults();
            }
        } else {
            $row = $this->getClassDefaults();
        }

        return $row;
    }

    /**
     * getClassDefaults - Get defaults for a course, if no course code supplied
     * @return object Class defaults
     */
    public function getClassDefaults() {

        $row = array(
            'CODE' => NULL,
            'WEB_SHOW' => 1,
            'TITLE' => NULL,
            'INTRO' => NULL,
            'COST' => 0,
            'NO_DAYS' => 1,
            'PREREQ' => NULL,
            'AUDIENCE' => NULL,
            'FURTHER' => NULL,
            'OUTLINE' => NULL,
            'COMMENTS' => NULL,
            'CHANGE_USER' => NULL
        );

        return $row;
    }

    /**
     * Update Class Details
     * 
     * @param Lots!
     * @return 
     */
    public function updateClass(
    $web_show, $code, $title, $intro, $cost, $no_days, $prereq, $audience, $further, $outline, $comments
    ) {

        $data = array(
            'CODE' => $code,
            'WEB_SHOW' => (int) $web_show,
            'TITLE' => trim($title),
            'INTRO' => trim($intro),
            'COST' => (int) $cost,
            'NO_DAYS' => (int) $no_days,
            'PREREQ' => trim($prereq),
            'AUDIENCE' => trim($audience),
            'FURTHER' => trim($further),
            'OUTLINE' => trim($outline),
            'CHANGE_USER' => $this->_currentUser,
            'COMMENTS' => trim($comments)
        );

        $where['CODE = ?'] = $code;

        //$this->update($data, $where);

        /*
         * Check to see if course already exists
         */

        $select = $this->select();
        $select->from($this->_name, 'Count(*) as count')
                ->where('CODE = ?', $code)
        ;

        $row = $this->fetchRow($select);

        if ($row->count == 1) {
            $this->update($data, $where);
        } else {
            $this->insert($data);
        }
    }

    /**
     * Add Class Details
     * 
     * @param Lots!
     * @return 
     */
    public function addClass(
    $web_show, $code, $title, $intro, $cost, $no_days, $prereq, $audience, $further, $outline, $comments
    ) {

        $data = array(
            'WEB_SHOW' => (int) $web_show,
            'CODE' => trim($code),
            'TITLE' => trim($title),
            'INTRO' => trim($intro),
            'COST' => (int) $cost,
            'NO_DAYS' => (int) $no_days,
            'PREREQ' => trim($prereq),
            'AUDIENCE' => trim($audience),
            'FURTHER' => trim($further),
            'OUTLINE' => trim($outline),
            'CHANGE_USER' => $current_user,
            'ADD_USER' => $this->_currentUser,
            'COMMENTS' => trim($comments)
        );

        $this->insert($data);
    }

    /*
     * Delete A Course
     * 
     * @param string Course Code
     * @return 
     */

    public function deleteClass($code = NULL) {

        //$msg = "Into the deleteClass model! - Code is $code";
        //fss_log::addLog($msg);

        If ($code) {

            $where = array(
                'CODE = ?' => $code,
            );

            $this->delete($where);
        }

        return;
    }

}