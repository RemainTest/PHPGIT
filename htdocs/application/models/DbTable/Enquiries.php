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
class Application_Model_DbTable_Enquiries extends Zend_Db_Table_Abstract {

    protected $_name = 'ENQUIRIES';
    protected $_primary = 'ENQ_NO';

    public function init() {

        $this->_currentUser = Zend_Controller_Front::getInstance()->getRequest()->getServer('REMOTE_USER');
        $this->_current_timestamp = getCurrentTimestamp();
    }

    /**
     * Get Enquiry Details
     * 
     * @param int $no Enq Number
     * @return Array Details
     * @throws Exception 
     */
    public function getEnquiry($enq_no) {

        $row = $this->fetchRow('ENQ_NO = ' . $enq_no);

        if (!$row) {
            throw new Exception("Could not find enquiry");
        }
        return $row->toArray();
    }

    /**
     * Create a new enquiry with minimal info
     * 
     * @return int  number of new enquiry
     */
    public function addEnquiry() {

        $db = Zend_Db_Table::getDefaultAdapter();

        // set default for follow up date to 7 days in the future
        $datetime = new DateTime();
        $datetime->add(new DateInterval('P7D'));
        $follow_up = $datetime->format('Y-m-d');

        $data = array(
            'ADD_USER' => $this->_currentUser,
            'CHANGE_USER' => $this->_currentUser,
            'FOLLOW_UP' => $follow_up
        );

        //Zend_Debug::dump($data);
        $this->insert($data);

        // Get new Enquiry no
        $no = $db->lastInsertId();

        // Adding a new Enquiry auto generates a log entry
        $entry = "Enquiry Created";

        // Log Table
        $tblLog = new Application_Model_DbTable_LogDetails();
        $tblLog->addLog($no, $entry, 'N', 'N', 'Y');

        return $no;
    }

    /**
     * Update Details
     * 
     * @param Array Array of update parameters
     */
    public function updateEnquiry($enquiry) {

        //Zend_Debug::dump($enquiry);

        $current_timestamp = getCurrentTimestamp();
        $enq_no = $enquiry['enq_no'];
        $statusCode = $enquiry['status'];
        $follow_up = $enquiry['follow_up'];

        If (trim($follow_up == '')) {
            $follow_up = NULL;
        }

        // Dont convert date if its NULL
        If ($follow_up <> NULL) {
            $follow_up = convertFullDateToISO($follow_up);
        }

        $prevStatus = $this->getStatus($enq_no);

        // Only sort out follow up date if not cleared by user
        If ($follow_up) {

            If ($prevStatus <> $statusCode) {

                // Sort out follow up date depending on status
                switch ($statusCode) {

                    // Enquiry, chase in 7 days
                    case 10:
                        $datetime = new DateTime();
                        $datetime->add(new DateInterval('P7D'));
                        $follow_up = $datetime->format('Y-m-d');
                        break;

                    // Booked, no follow up date
                    case 30:
                        $follow_up = NULL;
                        break;

                    // Unsuccessful, chase in 3 months
                    case 80:
                        $datetime = new DateTime();
                        $datetime->add(new DateInterval('P3M'));
                        $follow_up = $datetime->format('Y-m-d');
                        break;

                    // Closed, chase in 6 months
                    case 90:
                        $datetime = new DateTime();
                        $datetime->add(new DateInterval('P6M'));
                        $follow_up = $datetime->format('Y-m-d');
                        break;
                }
            }
        }

        $data = array(
            'ENQUIRER' => $enquiry['enquirer'],
            'COMPANY' => $enquiry['company'],
            'EMAIL' => $enquiry['email'],
            'TELEPHONE' => $enquiry['telephone'],
            'LOCATION' => $enquiry['location'],
            'PRICE' => $enquiry['price'],
            'STATUS' => $enquiry['status'],
            'DATE_GIVEN1' => convertFullDateToISO($enquiry['date_given1']),
            'DATE_GIVEN2' => convertFullDateToISO($enquiry['date_given2']),
            'DATE_GIVEN3' => convertFullDateToISO($enquiry['date_given3']),
            'DATE_GIVEN4' => convertFullDateToISO($enquiry['date_given4']),
            'DATE_GIVEN5' => convertFullDateToISO($enquiry['date_given5']),
            'CHASED_DATE' => convertFullDateToISO($enquiry['chased_date']),
            'PO' => $enquiry['po'],
            'CHANGE_USER' => $this->_currentUser,
            'FOLLOW_UP' => $follow_up,
            'COMMENTS' => $enquiry['comments'],
            'ADDRESS' => $enquiry['address'],
            'WEBSITE' => $enquiry['website'],
            'PAYMENT' => $enquiry['payment'],
            'POST_CODE' => $enquiry['post_code'],
            'EXPENSES' => $enquiry['expenses']
        );

        //Zend_Debug::dump($data);
        //die() ;
        //return ;

        $where['ENQ_NO = ?'] = $enq_no;

        $this->update($data, $where);

        $entry = "Enquiry Details Updated";

        // Log Table
        $tblLog = new Application_Model_DbTable_LogDetails();
        $tblLog->addLog($enq_no, $entry, 'N', 'N', 'Y');

        return;
    }

    /**
     * Count the number of alerts for a user
     * 
     * @param string|NULL $user User to count the alerts for.  If not passed the current user is used
     * @return int Number of Alerts
     */
    public function countAlerts($user = NULL) {

        If (!$user) {
            $user = $this->_currentUser ;
        }

        $today = date("Y-m-d");

        $select = $this->select();
        $select->from($this->_name, 'Count(*) as alert_count');
        $select->where('FOLLOW_UP < ?', $today);
        $select->where('STATUS < ?, 70');

        //echo "<p>SQL: $select </p>" ;
        $row = $this->fetchRow($select);

        return $row->alert_count;
    }

    /**
     * Get the maximum enquiry number on file
     * 
     * @return int Maximum number of the last enquiry
     */
    public function getMaxEnquiryNo() {


        $max = $this->fetchRow(
                $this->select()
                        ->from($this, array(new Zend_Db_Expr('max(enq_no) as maxNo')))
        );

        Return $max->MAXNO;
    }

    /**
     * Create a PDF for an Enquiry Assesment
     * 
     * @param int $enq_no Enquiry Number
     * 
     * @return string Fully qualified file name of the created PDF
     */
    public function createAssesmentPDF($enq_no) {

        $file_name = 'docs/Assesment_' . $enq_no . '.pdf';

        $enq_row = $this->getEnquiry($enq_no);

        // Get courses
        $tblTrainingCourses = new Application_Model_DbTable_TrainingCourses();
        $training_courses = $tblTrainingCourses->getAllCourses($enq_no);

        // Get students
        $tblStudents = new Application_Model_DbTable_Students();
        $students = $tblStudents->getStudents($enq_no);

        // Courses
        $tblClasses = new Application_Model_DbTable_CourseStudents();

        $header = "FormaServe Training - Course Assesments";

        // Got all the stuff now do the PDF print bits
        $page_count = 1;

        $pdf = new Zend_Pdf ();

        // Create A4 page
        $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);

        $pdf->properties['Title'] = "Training Assesments";
        $pdf->properties['Author'] = "FormaServe Systems Ltd";
        $pdf->properties['Subject'] = COPYRIGHT;


        // Define font resources
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $font_bold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $font_italic = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);

        // Logo
        $image = Zend_Pdf_Image::imageWithPath('docs/images/fss_logo.jpg');

        $pageHeight = $page->getHeight();
        $pageWidth = $page->getWidth();

        $imageHeight = 25;
        $imageWidth = 200;

        $topPos = $pageHeight - 30;
        $leftPos = 48;

        $bottomPos = $topPos - $imageHeight;
        $rightPos = $leftPos + $imageWidth;

        $page->drawImage($image, $leftPos, $bottomPos, $rightPos, $topPos);

        $col1 = 50;
        $col2 = 130;
        $col3 = 310;
        $col4 = 425;
        $col5 = 450;
        $col6 = 500;
        $end = 550;

        $y = $bottomPos - 50;

        // Header
        $page->setFont($font_bold, 18)
                ->drawText($header, 45, $y);

        // Enquiry Details
        $y = $y - 28;
        $page->setFont($font, 10)
                ->drawText('Number:', $col1, $y)
                ->drawText($enq_row['ENQ_NO'], $col2, $y)
        ;

        $y = $y - 20;
        $page->setFont($font, 10)
                ->drawText('Enquirer:', $col1, $y)
                ->drawText($enq_row['ENQUIRER'], $col2, $y)
        ;

        $y = $y - 20;
        $page->setFont($font, 10)
                ->drawText('Company:', $col1, $y)
                ->drawText($enq_row['COMPANY'], $col2, $y)
        ;

        $y = $y - 20;
        $page->setFont($font, 10)
                ->drawText('Telephone', $col1, $y)
                ->drawText($enq_row['TELEPHONE'], $col2, $y)
        ;

        $y = $y - 20;
        $page->setFont($font, 10)
                ->drawText('Location', $col1, $y)
                ->drawText($enq_row['LOCATION'], $col2, $y)
        ;

        $y = $y - 35;
        $page->setFont($font, 11)
                ->drawText('Course Details', 45, $y);

        $page->setLineWidth(0.5);
        $page->drawLine(45, ($y - 5), 550, ($y - 5));

        foreach ($training_courses as $details) :

            $tblClasses = new Application_Model_DbTable_Classes();
            $full_course = trim($details->COURSE_CODE) . ' - ' . $tblClasses->getTitle($details->COURSE_CODE);

            $y = $y - 20;
            $page->setFont($font, 9)
                    ->drawText('Course:', $col1, $y)
                    ->drawText($full_course, ($col1 + 75), $y)
            ;

            $y = $y - 20;
            $page->setFont($font, 9)
                    ->drawText('Date:', $col1, $y)
                    ->drawText(getFullDate($details->START_DATE), ($col1 + 75), $y)
            ;

            $y = $y - 20;
            $page->setFont($font, 9)
                    ->drawText('No Of Days:', $col1, $y)
                    ->drawText($details->NO_OF_DAYS, ($col1 + 75), $y)
            ;

            $y = $y - 20;
            $page->setFont($font, 9)
                    ->drawText('Venue:', $col1, $y)
                    ->drawText($details->VENUE, ($col1 + 75), $y)
            ;

            $y = $y - 20;
            $page->setFont($font, 9)
                    ->drawText('Instructor:', $col1, $y)
                    ->drawText('Andy Youens', ($col1 + 75), $y)
            ;

        endforeach;

        $y = $y - 35;
        $page->setFont($font, 11)
                ->drawText('Students', 45, $y);

        $page->setLineWidth(0.5);
        $page->drawLine(45, ($y - 5), 550, ($y - 5));

        $y = $y - 25;
        $page->setFont($font_bold, 10)
                ->drawText('Student', $col1, $y)
                ->drawText('Course Content', ($col2 + 55), $y)
                ->drawText('Instructor', $col3, $y)
                ->drawText('Facilities (If at FSS)', $col4, $y)
        ;

        foreach ($students as $details) :

            $tblConfig = New Application_Model_DbTable_Configuration();
            $name = Trim($details->FIRST_NAME) . ' ' . $details->SURNAME;

            $y = $y - 20;
            $page->setFont($font_italic, 9)
                    ->drawText($name, $col1, $y)
            ;

            $page->setFont($font, 9)
                    ->drawText($tblConfig->getConfiguration('ASSESMENT', $details->ASSESMENT_CONTENT), ($col2 + 55), $y)
                    ->drawText($tblConfig->getConfiguration('ASSESMENT', $details->ASSESMENT_INSTRUCTOR), $col3, $y)
                    ->drawText($tblConfig->getConfiguration('ASSESMENT', $details->ASSESMENT_FACILITIES), $col4, $y)
            ;

            if (trim($details->COMMENTS) <> '') {

                $y = $y - 20;
                $comments = 'Comments: ' . $details->COMMENTS;
                $page->setFont($font, 9)
                        ->drawText($comments, ($col1 + 5), $y)
                ;
            }

        endforeach;

        // Add page to document
        $pdf->pages [] = $page;

        // Save file
        $pdf->save($file_name, TRUE);

        return $file_name;
    }

    /**
     * Get the status of any enquiry
     * 
     * @param int $enq_no Enquiry number
     * @return int Status code
     */
    public function getStatus($enq_no = 0) {

        $row = $this->fetchRow('ENQ_NO = ' . $enq_no);

        if (!$row) {
            throw new Exception("Could not find enquiry");
        }

        return $row->STATUS;
    }

    /**
     * Copy an existing enquiry
     * 
     * @param int $enq_no enquiry number
     * @return int new enquiry number
     */
    public function copyEnquiry($enq_no = 0) {

        $new_enq_no = 0;

        If ($enq_no > 0) {
            $enquiry = $this->fetchRow('ENQ_NO = ' . $enq_no);

            if (!$enquiry) {
                throw new Exception("Could not find enquiry to copy");
            } else {
                $this->_currentUser = $_SERVER['REMOTE_USER'];
                $db = Zend_Db_Table::getDefaultAdapter();

                // set default for follow up date to 7 days in the future
                $datetime = new DateTime();
                $datetime->add(new DateInterval('P7D'));
                $follow_up = $datetime->format('Y-m-d');

                $data = array(
                    'ENQUIRER' => $enquiry['ENQUIRER'],
                    'COMPANY' => $enquiry['COMPANY'],
                    'EMAIL' => $enquiry['EMAIL'],
                    'TELEPHONE' => $enquiry['TELEPHONE'],
                    'LOCATION' => $enquiry['LOCATION'],
                    'ADDRESS' => $enquiry['ADDRESS'],
                    'WEBSITE' => $enquiry['WEBSITE'],
                    'POST_CODE' => $enquiry['POST_CODE'],
                    'ADD_USER' => $this->_currentUser,
                    'CHANGE_USER' => $this->_currentUser,
                    'FOLLOW_UP' => $follow_up
                );

                //Zend_Debug::dump($data);
                $this->insert($data);

                // Get new Enquiry no
                $new_enq_no = $db->lastInsertId();

                // Adding a new Enquiry auto generates a log entry
                $entry = "Enquiry Copied From Enquiry Number: # $enq_no";

                // Log Table
                $tblLog = new Application_Model_DbTable_LogDetails();
                $tblLog->addLog($new_enq_no, $entry, 'N', 'N', 'Y');
            }
        }


        return $new_enq_no;
    }

    /**
     * Mark an enquiry as spoilt
     * 
     * @param int $enq_no enquiry number
     */
    public function spoiltEnquiry($enq_no = 0) {

        If ($enq_no > 0) {

            $this->_currentUser = $_SERVER['REMOTE_USER'];

            $data = array(
                'STATUS' => 0,
                'CHANGE_USER' => $this->_currentUser
            );


            $where['ENQ_NO = ?'] = $enq_no;

            $this->update($data, $where);

            $entry = "Enquiry Status Set To Spoilt";

            // Log Table
            $tblLog = new Application_Model_DbTable_LogDetails();
            $tblLog->addLog($new_enq_no, $entry, 'N', 'N', 'Y');
        }

        return;
    }

}
