<?php

class EnquiriesController extends Zend_Controller_Action {

    public function init() {

        $this->view->headTitle('Training');
        $this->current_user = getCurrentUser();
        $this->today = date("Y-m-d");
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    public function indexAction() {

        $this->view->copyright = COPYRIGHT;

        //include page specific js & css
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/enquiries.js'));

        $paging_qs = "";
        $this->view->paging_qs = "";
        $tab_switch = "";

        //open ticket header table
        $tblEnquiries = new Application_Model_DbTable_Enquiries();
        $this->view->tblConfig = New Application_Model_DbTable_Configuration();

        //main data selection
        $select = $tblEnquiries->select()
                ->from($tblEnquiries, '*')
                ->setIntegrityCheck(true);

        $columns = array('*');

        $this->view->clrA = "color: red;";
        $this->view->clrB = "";
        $this->view->clrC = "";

        // Parameters
        $tab = $this->getRequest()->getParam('f', '1');
        $sel = $this->getRequest()->getParam('sel', 'B');

        $this->view->f = $tab;
        $this->view->sel = $sel;

        // Main Enquiry Screen
        if ($tab == "1") {

            switch ($sel) {

                case "A": // All open

                    $select->where('STATUS < ?', 70);
                    $select->where('STATUS > ?', 0);
                    $select->order('ENQ_NO DESC');
                    $this->view->clrA = "color: blue;";
                    $this->view->clrB = "";
                    $this->view->clrC = "";

                    break;

                case "B": // All, except spoilt 

                    $select->where('STATUS > ?', 0);
                    $select->order('ENQ_NO DESC');
                    $this->view->clrA = "";
                    $this->view->clrB = "color: blue;";
                    $this->view->clrC = "";

                    break;

                case "C": // To Chase

                    //$datetime = new DateTime();
                    //$datetime->add(new DateInterval('P7D'));
                    //$today7 = $datetime->format('Y-m-d');

                    //$select->where('FOLLOW_UP < ?', $today7);
                    $select->where('STATUS > ?', 0);
                    $select->where('FOLLOW_UP IS NOT NULL');
                    $select->order('FOLLOW_UP');
                    $this->view->clrA = "";
                    $this->view->clrB = "";
                    $this->view->clrC = "color: blue;";

                    break;
            }

            $tab_switch = "";
        }

        // Ticket Search
        if ($tab == "2") {

            $columns = array('DISTINCT(ENQUIRIES.ENQ_NO)',
                'ENQUIRER',
                'COMPANY',
                'EMAIL',
                'TELEPHONE',
                'LOCATION',
                'PRICE',
                'STATUS',
                'CHASED_DATE',
                'FOLLOW_UP'
            );

            $select = $tblEnquiries->select()
                    ->from($tblEnquiries, $columns)
            ;

            //$text1 = strtoupper($this->getRequest()->getParam('text1'));
            $text1 = $this->getRequest()->getParam('text1');
            $op1 = $this->getRequest()->getParam('op1');
            $field1 = $this->getRequest()->getParam('field1');
            $text2 = strtoupper($this->getRequest()->getParam('text2'));
            $ao2 = $this->getRequest()->getParam('ao2');
            $op2 = $this->getRequest()->getParam('op2');
            $field2 = $this->getRequest()->getParam('field2');
            $text3 = strtoupper($this->getRequest()->getParam('text3'));
            $ao3 = $this->getRequest()->getParam('ao3');
            $op3 = $this->getRequest()->getParam('op3');
            $field3 = $this->getRequest()->getParam('field3');

            $paging_qs = "/field1/" . $field1 . "/op1/" . $op1 . "/text1/" . $text1 .
                    "/ao2/" . $ao2 . "/field2/" . $field2 . "/op2/" . $op2 . "/text2/" . $text2 .
                    "/ao3/" . $ao3 . "/field3/" . $field3 . "/op3/" . $op3 . "/text3/" . $text3;
            $this->view->paging_qs = $paging_qs;

            $wildcard = false;

            // Sort out date formats
            If (( substr($field1, 0, 5) == 'DATE(') or ( substr($field1, 0, 5) == 'FOLLO' ) or ( $field1 == 'START_DATE')) {
                $text1 = convertDMYToISO(trim($text1));
            }

            If (( substr($field2, 0, 5) == 'DATE(') or ( substr($field2, 0, 5) == 'FOLLO' )) {
                $text2 = convertDMYToISO(trim($text2));
            }

            If (( substr($field3, 0, 5) == 'DATE(') or ( substr($field3, 0, 5) == 'FOLLO' )) {
                $text3 = convertDMYToISO(trim($text3));
            }

            // Text searching
            if (trim($text1 <> "")) {

                switch ($field1) {

                    case "WILDCARD" :
                        $wildcard = true;
                        $select->setIntegrityCheck(false);
                        $select->joinLeft('LOG_DETAILS', 'LOG_DETAILS.ENQ_NO = ENQUIRIES.ENQ_NO', array());
                        $select->where("Contains(ENTRY, '" . trim($text1) . "') = 1");
                        break;

                    case "STATUS" :
                        $select->where("STATUS" . $op1 . getStatusCode(ucwords(strtolower($text1))));
                        break;

                    case "START_DATE" :
                        $select->setIntegrityCheck(false);
                        $select->joinLeft('TRAINING_COURSES', 'TRAINING_COURSES.ENQUIRY_NUMBER = ENQUIRIES.ENQ_NO', array());
                        $select->where('START_DATE' . $op1 . ' ?', $text1);

                        break;

                    case "COURSE" :
                        $select->setIntegrityCheck(false);
                        $select->joinLeft('TRAINING_COURSES', 'TRAINING_COURSES.ENQUIRY_NUMBER = ENQUIRIES.ENQ_NO', array());
                        $courseFlag = TRUE;

                        If (trim($op1) == 'like') {
                            $select->where("UPPER(COURSE_CODE) Like '%" . $text1 . "%'");
                        } Else If (trim($op1) == 'nlike') {
                            $select->where("UPPER(COURSE_CODE) Not Like '%" . $text1 . "%'");
                        } Else {
                            $select->where('UPPER(COURSE_CODE)' . $op1 . ' ?', $text1);
                        }

                        break;

                    case "STUDENT" :
                        $select->setIntegrityCheck(false);
                        $select->joinLeft('STUDENTS', 'STUDENTS.ENQUIRY_NUMBER = ENQUIRIES.ENQ_NO', array());
                        $studentFlag = TRUE;

                        If (trim($op1) == 'like') {
                            $select->where("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Like '%" . $text1 . "%'");
                        } Else If (trim($op1) == 'nlike') {
                            $select->where("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Not Like '%" . $text1 . "%'");
                        } Else {
                            $select->where('UPPER(FIRST_NAME) Concat UPPER(SURNAME)' . $op1 . ' ?', $text1);
                        }

                        break;

                    default :
                        $text1 = strtoupper($text1);

                        If (trim($op1) == 'like') {
                            $select->where(trim($field1) . " Like '%" . $text1 . "%'");
                        } Else If (trim($op3) == 'nlike') {
                            $select->where($field1 . " Not Like '%" . $text1 . "%'");
                        } Else {
                            $select->where($field1 . " " . $op1 . " '" . $text1 . "'");
                        }
                }
            }
            if (trim($text2 <> "") and ! $wildcard) {

                switch ($field2) {

                    case "STATUS" :
                        $select->where("STATUS" . $op2 . getStatusCode(ucwords(strtolower($text2))));
                        break;

                    case "COURSE" :

                        If (!$courseFlag) {
                            $select->setIntegrityCheck(false);
                            $select->joinLeft('TRAINING_COURSES', 'TRAINING_COURSES.ENQUIRY_NUMBER = ENQUIRIES.ENQ_NO', array());
                            $courseFlag = TRUE;
                        }

                        If (trim($op2) == 'like') {
                            if ($ao2 == "and") {
                                $select->where("UPPER(COURSE_CODE) Like '%" . $text2 . "%'");
                            } else {
                                $select->orwhere("UPPER(COURSE_CODE) Like '%" . $text2 . "%'");
                            }
                        } Else If (trim($op2) == 'nlike') {
                            if ($ao2 == "and") {
                                $select->where("UPPER(COURSE_CODE) Not Like '%" . $text2 . "%'");
                            } else {
                                $select->orwhere("UPPER(COURSE_CODE) Not Like '%" . $text2 . "%'");
                            }
                        } Else {
                            $select->where('UPPER(COURSE_CODE)' . $op2 . ' ?', $text2);
                        }

                        break;

                    case "STUDENT" :

                        If (!$studentFlag) {
                            $select->setIntegrityCheck(false);
                            $select->joinLeft('STUDENTS', 'STUDENTS.ENQUIRY_NUMBER = ENQUIRIES.ENQ_NO', array());
                            $studentFlag = TRUE;
                        }

                        If (trim($op2) == 'like') {
                            if ($ao2 == "and") {
                                $select->where("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Like '%" . $text2 . "%'");
                            } else {
                                $select->orwhere("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Like '%" . $text2 . "%'");
                            }
                        } Else If (trim($op2) == 'nlike') {
                            if ($ao2 == "and") {
                                $select->where("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Not Like '%" . $text2 . "%'");
                            } else {
                                $select->orwhere("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Not Like '%" . $text2 . "%'");
                            }
                        } Else {
                            $select->where("TRIM(UPPER(FIRST_NAME)) Concat ' ' Concat UPPER(SURNAME)" . $op2 . ' ?', $text2);
                        }

                        break;

                    default :
                        If (trim($op2) == 'like') {
                            $select->where($field2 . " Like '%" . $text2 . "%'");
                        } Else If (trim($op3) == 'nlike') {
                            $select->where($field2 . " Not Like '%" . $text2 . "%'");
                        } Else {
                            $select->where($field2 . " " . $op2 . " '" . $text2 . "'");
                        }
                }
            }

            if (trim($text3 <> "") and ! $wildcard) {

                switch ($field3) {

                    case "STATUS" :
                        $select->where("STATUS" . $op3 . getStatusCode(ucwords(strtolower($text3))));
                        break;

                    case "COURSE" :
                        If (!$courseFlag) {
                            $select->setIntegrityCheck(false);
                            $select->joinLeft('TRAINING_COURSES', 'TRAINING_COURSES.ENQUIRY_NUMBER = ENQUIRIES.ENQ_NO', array());
                            $courseFlag = TRUE;
                        }

                        If (trim($op3) == 'like') {
                            if ($ao3 == "and") {
                                $select->where("UPPER(COURSE_CODE) Like '%" . $text3 . "%'");
                            } else {
                                $select->orwhere("UPPER(COURSE_CODE) Like '%" . $text3 . "%'");
                            }
                        } Else If (trim($op3) == 'nlike') {
                            if ($ao3 == "and") {
                                $select->where("UPPER(COURSE_CODE) Not Like '%" . $text3 . "%'");
                            } else {
                                $select->orwhere("UPPER(COURSE_CODE) Not Like '%" . $text3 . "%'");
                            }
                        } Else {
                            $select->where('UPPER(COURSE_CODE)' . $op3 . ' ?', $text3);
                        }

                        break;

                    case "STUDENT" :

                        If (!$studentFlag) {
                            $select->setIntegrityCheck(false);
                            $select->joinLeft('STUDENTS', 'STUDENTS.ENQUIRY_NUMBER = ENQUIRIES.ENQ_NO', array());
                            $studentFlag = TRUE;
                        }

                        If (trim($op3) == 'like') {
                            if ($ao3 == "and") {
                                $select->where("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Like '%" . $text3 . "%'");
                            } else {
                                $select->orwhere("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Like '%" . $text3 . "%'");
                            }
                        } Else If (trim($op3) == 'nlike') {
                            if ($ao3 == "and") {
                                $select->where("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Not Like '%" . $text3 . "%'");
                            } else {
                                $select->orwhere("UPPER(FIRST_NAME) Concat UPPER(SURNAME) Not Like '%" . $text3 . "%'");
                            }
                        } Else {
                            $select->where("TRIM(UPPER(FIRST_NAME)) Concat ' ' Concat UPPER(SURNAME)" . $op3 . ' ?', $text3);
                        }

                        break;

                    default :

                        If (trim($op3) == 'like') {
                            $select->where(trim($field3) . " Like '%" . $text3 . "%'");
                        } Else If (trim($op3) == 'nlike') {
                            $select->where(trim($field3) . " Not Like '%" . $text3 . "%'");
                        } Else {
                            $select->where($field3 . " " . $op3 . ' ?', $text3);
                        }
                }
            }

            $tab_switch = "<script type=\"text/javascript\">
            function tab_switch(){
                $(\"#tabs\").tabs( \"option\", \"active\", 1 ); 
			$(\"#field1\").val('" . $field1 . "');
			$(\"#field2\").val('" . $field2 . "');
			$(\"#field3\").val('" . $field3 . "');
			$(\"#op1\").val('" . $op1 . "');
			$(\"#op2\").val('" . $op2 . "');
			$(\"#op3\").val('" . $op3 . "');
			$(\"#ao2\").val('" . $ao2 . "');
			$(\"#ao3\").val('" . $ao3 . "');
			date_popup('1');
			date_popup('2');
			date_popup('3');}
			</script>";

            $this->view->text1 = $text1;
            $this->view->text2 = $text2;
            $this->view->text3 = $text3;
        }

        // Get paging parameter
        $page = $this->getRequest()->getParam('page', 1);

        // Get paging size
        $tblConfig = new Application_Model_DbTable_Configuration();
        $page_count = $tblConfig->getConfiguration('SYSTEM', 'PAGE_SIZE');

        //$select->order('ENQ_NO DESC');
        //$db = Zend_Db_Table::getDefaultAdapter();
        //$db->getProfiler()->setEnabled(true);
        //fss_log::addLog('SQL:' . $select);

        try {
            $records = $tblEnquiries->fetchAll($select);
        } catch (Exception $e) {
            fss_log::addLog('Error:' . $e->getMessage());
            fss_log::addLog('SQL:' . $db->getProfiler()->getLastQueryProfile()->getQuery());
            $parms = $db->getProfiler()->getLastQueryProfile()->getQueryParams();

            // Loop thro parameter list
            foreach ($parms as $option => $value) {
                fss_log::addLog('SQL Parm: ' . $option . ' = ' . $value);
            }

            $db->getProfiler()->setEnabled(false);

            //Fss_Error::notify ;
            //Zend_Controller_Front::throwExceptions() ;
            throw new Exception('Uncaught Exception occurred');
        }


        // get all records
        $records = $tblEnquiries->fetchAll($select);
        $rec_count = $records->count();

        // Build tab text, including record count
        switch ($rec_count) {

            case 0 :
                $tab_text = 'No Enquiries Found';
                break;

            case 1 :
                $tab_text = '1 Enquiry Found';
                break;

            default :
                $rec_count = number_format($rec_count);
                $tab_text = "$rec_count Enquiries Found";
                break;
        }

        // Configure Pagination
        $paginator = Zend_Paginator::factory($records);
        $paginator->setItemCountPerPage($page_count)
                ->setCurrentPageNumber($page);

        // Get records
        $this->view->header = $paginator;

        //$records = $tblHeader->fetchAll($select);
        $this->view->records = $tab_text;
        //$msg = "Tab Switch is:  $tab_switch";
        //fss_log::addLog($msg);

        if ($tab_switch == '') {
            $tab_switch = "<script type=\"text/javascript\">function tab_switch(){}</script>";
        }

        $this->view->tab_switch = $tab_switch;

        // echo("<p>sql = " . $select . "<p>");
    }

    public function updateAction() {

        //include page specific js & css
        $this->view->headScript()->appendFile($this->view->baseUrl('js/enquiries_update.js'));

        //get passed parameter
        If (!isset($enq_no)) {
            $enq_no = $this->getRequest()->getParam('enq_no');
        }

        //open enquiry table
        $tblEnquiries = new Application_Model_DbTable_Enquiries();

        //what has called the function
        $form = $this->getRequest()->getParam('f', NULL);

        $this->view->log_no = 0;
        $this->view->crs_no = 0;
        $this->view->stu_no = 0;
        $this->view->soc_no = 0;
        $this->view->enr_no = 0;
        $this->view->pay_no = 0;
        $this->log_no = 0;
        $this->crs_no = 0;
        $this->stu_no = 0;
        $this->soc_no = 0;
        $this->enr_no = 0;
        $this->pay_no = 0;
        $close_tab = "";
        $map = FALSE;

        // We got an enquiry number yet, only do this if not a copy function
        If ($enq_no & ($form <> 'C')) {

            // Get courses
            $tblTrainingCourses = new Application_Model_DbTable_TrainingCourses();
            $this->view->training_courses = $tblTrainingCourses->getAllCourses($enq_no);
            $enq_courses = $this->view->training_courses;


            $this->view->crs_no = count($this->view->training_courses);
            $this->crs_no = $this->view->crs_no;

            //$msg = "Got $this->crs_no Courses";
            //fss_log::addLog($msg);

            // Get students
            $tblStudents = new Application_Model_DbTable_Students();
            $this->view->students = $tblStudents->getStudents($enq_no);
            $enq_students = $this->view->students;

            $this->view->stu_no = count($this->view->students);
            $this->stu_count = $this->view->stu_no;
        }

        //process actions if button pressed
        if ($form <> "") {
            switch ($form) {

                // Add Enquiry
                case "A":
                    $enq_no = $tblEnquiries->addEnquiry();
                    break;

                // Save Enquiry
                case "B":

                    $enquirer = $this->getRequest()->getParam('ENQUIRER');
                    $current_enquirer = $this->getRequest()->getParam('current_enquirer');

                    $enquiry = array(
                        'enq_no' => $enq_no,
                        'enquirer' => $enquirer,
                        'company' => $this->getRequest()->getParam('COMPANY'),
                        'email' => $this->getRequest()->getParam('EMAIL'),
                        'telephone' => $this->getRequest()->getParam('TELEPHONE'),
                        'location' => $this->getRequest()->getParam('LOCATION'),
                        'price' => $this->getRequest()->getParam('PRICE'),
                        'status' => $this->getRequest()->getParam('STATUS'),
                        'date_given1' => $this->getRequest()->getParam('DATE_GIVEN1'),
                        'date_given2' => $this->getRequest()->getParam('DATE_GIVEN2'),
                        'date_given3' => $this->getRequest()->getParam('DATE_GIVEN3'),
                        'date_given4' => $this->getRequest()->getParam('DATE_GIVEN4'),
                        'date_given5' => $this->getRequest()->getParam('DATE_GIVEN5'),
                        'po' => $this->getRequest()->getParam('PO'),
                        'follow_up' => $this->getRequest()->getParam('FOLLOW_UP'),
                        'comments' => $this->getRequest()->getParam('COMMENTS'),
                        'address' => $this->getRequest()->getParam('ADDRESS'),
                        'website' => $this->getRequest()->getParam('WEBSITE'),
                        'chased_date' => $this->getRequest()->getParam('CHASED_DATE'),
                        'post_code' => $this->getRequest()->getParam('POST_CODE', ''),
                        'expenses' => $this->getRequest()->getParam('EXPENSES', ''),
                        'payment' => $this->getRequest()->getParam('PAYMENT', ''),
                    );

                    if (trim($current_enquirer) <> trim($enquirer)) {

                        $tblEnq = new Application_Model_DbTable_Enquirers();
                        $tblEnq->addEnquirer($enq_no, $current_enquirer);
                    }

                    $tblEnquiries->updateEnquiry($enquiry);


                    // Sort out courses/students
                    $crs_no = $this->getRequest()->getParam('crs_no', 0);

                    //$msg = "Course count is: " . $crs_no;
                    //fss_log::addLog($msg);

                    $all = $this->getRequest()->getParam('all', 'off');

                    // Do we need to update the course/student details?
                    If ($crs_no > 0) {

                        $tblCourseStudents = new Application_Model_DbTable_CourseStudents();
                        $tblTrainingCourses = new Application_Model_DbTable_TrainingCourses();
                        $enq_courses = $tblTrainingCourses->getAllCourses($enq_no);

                        $tblStudents = new Application_Model_DbTable_Students();
                        $enq_students = $tblStudents->getStudents($enq_no);

                        foreach ($enq_courses as $courses) :

                            foreach ($enq_students as $students) :

                                $value = NULL;

                                if ($all == 'on') {
                                    $value = 'on';
                                } Else {
                                    $value = $this->getRequest()->getParam($courses->COURSE_CODE . "_" . Trim($students->ID));

                                    If ($this->getRequest()->getParam(Trim($students->ID)) == 'on') {
                                        $value = 'on';
                                    }
                                }

                                $tblCourseStudents->addDetails($enq_no, $courses->COURSE_CODE, $students->ID, $value);

                                //$msg = "Added Course/Student details: " . $enq_no . ' ' . $courses->COURSE_CODE . ' ' . $students->ID . ' ' . $value;
                                //fss_log::addLog($msg);

                            endforeach;

                        endforeach;
                    }

                    break;

                // Copy Enquiry
                case "C":

                    $tblEnquiries = new Application_Model_DbTable_Enquiries();

                    $existing_enq_no = $enq_no;
                    $enq_no = $tblEnquiries->copyEnquiry($existing_enq_no);

                    break;

                // Close Tab
                case "D":

                    $msg = "Tab is being closed!";
                    fss_log::addLog($msg);
                    $close_tab = "<script type=\"text/javascript\">close_tab();</script>";

                    break;
            }
        }

        //get user
        $this->view->user = $this->current_user;
        $this->view->enq_no = $enq_no;
        $this->enq_no = $enq_no;

        //get header
        $header_row = $tblEnquiries->getEnquiry($enq_no);
        $this->view->header = $header_row;

        // Zend_Debug::dump($header_row) ;

        $selection = $this->getRequest()->getParam('sel');

        $this->view->current_enquirer = $this->view->header["ENQUIRER"];
        $current_enquirer = $this->view->current_enquirer;

        If (trim($header_row['ADDRESS']) <> '') {
            $this->view->map = true;
        } else {
            $this->view->map = false;
            echo "<script type='text/javascript'>$('#header').tabs({disabled: [1]});</script>";
        }

        $tblLog = new Application_Model_DBTable_LogDetails();

        $select = $tblLog->select()
                ->from($tblLog, '*')
                ->where('ENQ_NO = ?', $enq_no)
        ;

        $FSS_Namespace = new Zend_Session_Namespace('FormaServe');

        if (!isset($FSS_Namespace->name)) {
            $FSS_Namespace->name = "FormaServe";
        }

        if (!isset($FSS_Namespace->showAudit)) {
            $FSS_Namespace->showAudit = 0; // Default is to supress auditing
        }

        if (!isset($FSS_Namespace->logOrder)) {
            $FSS_Namespace->logOrder = 'LOG_NO DESC';  // Default to show log descending
        }

        switch ($selection) {

            // Toggle Auditing Log to show auto generated entries or not
            case "ta":

                If ($FSS_Namespace->showAudit == 1) {
                    $FSS_Namespace->showAudit = 0; // User entries only
                } Else {
                    $FSS_Namespace->showAudit = 1;
                }

                break;

            case "tb":

                If ($FSS_Namespace->logOrder == 'LOG_NO DESC') {
                    $FSS_Namespace->logOrder = 'LOG_NO ASC';
                } Else {
                    $FSS_Namespace->logOrder = 'LOG_NO DESC';
                }

                break;
        }

        $select->where('AUDIT_FLAG <= ?', $FSS_Namespace->showAudit);
        $select->order($FSS_Namespace->logOrder);

        $this->view->logs = $tblLog->fetchAll($select);
        $this->view->log_no = count($this->view->logs);
        $this->log_count = $this->view->log_no;

        // Enquirers
        $tblEnq = new Application_Model_DbTable_Enquirers();
        $this->view->enr = $tblEnq->getEnquirers($enq_no);

        $this->view->enr_no = count($this->view->enr);
        $this->enr_count = $this->view->enr_no;

        // Courses
        $tblTrainingCourses = new Application_Model_DbTable_TrainingCourses();
        $this->view->training_courses = $tblTrainingCourses->getAllCourses($enq_no);
        $enq_courses = $this->view->training_courses;

        $this->view->crs_no = count($this->view->training_courses);
        $this->crs_no = $this->view->crs_no;

        // $msg = "Got $this->crs_no Courses";
        // fss_log::addLog($msg);
        // Get students
        $tblStudents = new Application_Model_DbTable_Students();
        $this->view->students = $tblStudents->getStudents($enq_no);
        $enq_students = $this->view->students;

        $this->view->stu_no = count($this->view->students);
        $this->stu_no = $this->view->stu_no;

        // Link Students to Courses
//        $tblCourseStudents = New Application_Model_DbTable_CourseStudents();
//
//        $all = NULL;
//
//        foreach ($enq_courses as $courses) :
//
//            foreach ($enq_students as $students) :
//
//                $value = NULL;
//
//                if ($all == 'on') {
//                    $value = 'on';
//                } Else {
//                    $value = $this->getRequest()->getParam($courses->COURSE_CODE . "_" . Trim($students->ID));
//
//                    If ($this->getRequest()->getParam(Trim($students->ID)) == 'on') {
//                        $value = 'on';
//                    }
//                }
//
//                $tblCourseStudents->addDetails($enq_no, $courses->COURSE_CODE, $students->ID, $value);
//
//            endforeach;
//
//        endforeach;
        // Payment/Expenses Details
        $pay_no = 0;

        If (trim($header_row["PAYMENT"]) <> '') {
            //$msg = "Payment Details Entered: " . $header_row["PAYMENT"];
            //fss_log::addLog($msg);
            $pay_no = 1;
        }

        If (trim($header_row["EXPENSES"]) <> '') {
            //$msg = "Expense Details Entered: " . $header_row["EXPENSES"];
            //fss_log::addLog($msg);
            ++$pay_no;
        }

        $this->view->pay_no = $pay_no;



        //echo("sql = " . $select);

        /**
         * Setup selection for drop down box fields
         */
        // Status values
        $tblParms = new Application_Model_DbTable_Configuration();
        $parameters = 'STATUS';

        $select = $tblParms->select()
                ->from($tblParms, '*')
                ->where('PARAMETERS = ?', $parameters)
                ->order(array('PARM_TEXT'));

        $dropdown = $tblParms->fetchAll($select);

        //
        foreach ($dropdown as $option => $value) {
            $status[] = trim($value->PARM_TEXT);
            $status2[] = trim($value->PARM_VALUE);
        }

        $this->view->status = generateSelect2('STATUS', $status2, $status, getStatusDescription($header_row["STATUS"]), 'N', 'N', 105);

        $this->view->website = NULL;

        If ($this->view->header['WEBSITE'] <> NULL) {
            $this->view->website = "<a href='" . trim($this->view->header['WEBSITE']) . "'>" . trim($this->view->header['WEBSITE']) . "</a>";
        }


        $this->view->close_tab = $close_tab;
    }

    public function spoiltAction() {

        // Stop layout & view
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $enq_no = $this->getRequest()->getParam('enq_no');

        //open enquiry table
        $tblEnquiries = new Application_Model_DbTable_Enquiries();

        $tblEnquiries->spoiltEnquiry($enq_no);

        // All done!
        $this->_helper->redirector->gotourl('/enquiries/index/');
    }

}
