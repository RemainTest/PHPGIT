<?php

class DashboardController extends Zend_Controller_Action {

    public function init() {

        $this->view->headTitle('Training');
    }

    public function indexAction() {

        // include page specific js & css
        $this->view->headScript()->appendFile('https://www.google.com/jsapi');
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/dashboard.js'));


        $months = explode(" ", "Zer Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec");
        $this->view->current_year = date("Y");

        // Header Table
        $tblEnquiries = new Application_Model_DbTable_Enquiries;

        // Status details
        $select = $tblEnquiries->select()
                ->from($tblEnquiries, 'STATUS, Count(STATUS) as COUNT')
                ->where('STATUS > 0')
                ->where('Year(ADD_TS) = (year(current_date)) ')
                ->group('STATUS')
                ->order(array('STATUS'));

        $chart1 = "";
        foreach ($tblEnquiries->fetchAll($select) as $result) {
            $chart1 .= "['" . getStatusDescription($result->STATUS) . "'," . $result->COUNT . "],";
        }
        $this->view->chart1 = substr($chart1, 0, -1);


        // Header Table
        $tblEnquiries = new Application_Model_DbTable_Enquiries;

        // Status details
        $select = $tblEnquiries->select()
                ->from($tblEnquiries, 'STATUS, Count(STATUS) as COUNT')
                ->where('STATUS > 0')
                ->group('STATUS')
                ->order(array('STATUS'));

        $chart2 = "";
        foreach ($tblEnquiries->fetchAll($select) as $result) {
            $chart2 .= "['" . getStatusDescription($result->STATUS) . "'," . $result->COUNT . "],";
        }
        $this->view->chart2 = substr($chart2, 0, -1);

        // Top 10 Courses
        $tblCourses = new Application_Model_DbTable_TrainingCourses;

        //main data selection
        $select = $tblCourses->select()
                ->from($tblCourses, 'SUBSTR(TRAINING_COURSES.COURSE_CODE,1,4) as CODE, Count(TRAINING_COURSES.COURSE_CODE) as COUNT')
        ;

        $select->setIntegrityCheck(false)
                ->joinLeft('ENQUIRIES', 'ENQUIRIES.ENQ_NO = TRAINING_COURSES.ENQUIRY_NUMBER', array())
                ->where('ENQUIRIES.STATUS = ?', 90)
                ->group('SUBSTR(TRAINING_COURSES.COURSE_CODE,1,4)')
                ->order('COUNT DESC')
                ->limit(10, 0)
        ;

        $chart3 = "";

        // ECHO "<p>SQL: $select </p>" ;

        foreach ($tblCourses->fetchAll($select) as $result) {
            $chart3 .= "['" . $result->CODE . "'," . $result->COUNT . "],";
        }

        $this->view->chart3 = substr($chart3, 0, -1);

        // Course Content
        $tblStudents = new Application_Model_DbTable_Students();
        $tblConfig = new Application_Model_DbTable_Configuration();

        //main data selection
        $select = $tblStudents->select()
                ->from($tblStudents, 'ASSESMENT_CONTENT, Count(ASSESMENT_CONTENT) as COUNT')
                ->where('ASSESMENT_CONTENT > ?', 0)
                ->where('ASSESMENT_CONTENT < ?', 60)
                ->group('ASSESMENT_CONTENT')
                ->order('COUNT DESC')
        ;

        $chart4 = "";

        foreach ($tblCourses->fetchAll($select) as $result) {
            $chart4 .= "['" . $tblConfig->getConfiguration('ASSESMENT', $result->ASSESMENT_CONTENT) . "'," . $result->COUNT . "],";
        }

        $this->view->chart4 = substr($chart4, 0, -1);

        // Instructor
        $tblStudents = new Application_Model_DbTable_Students();
        $tblConfig = new Application_Model_DbTable_Configuration();

        //main data selection
        $select = $tblStudents->select()
                ->from($tblStudents, 'ASSESMENT_INSTRUCTOR, Count(ASSESMENT_INSTRUCTOR) as COUNT')
                ->where('ASSESMENT_INSTRUCTOR > ?', 0)
                ->where('ASSESMENT_INSTRUCTOR < ?', 60)
                ->group('ASSESMENT_INSTRUCTOR')
                ->order('COUNT DESC')
        ;

        $chart5 = "";

        foreach ($tblCourses->fetchAll($select) as $result) {
            $chart5 .= "['" . $tblConfig->getConfiguration('ASSESMENT', $result->ASSESMENT_INSTRUCTOR) . "'," . $result->COUNT . "],";
        }

        $this->view->chart5 = substr($chart5, 0, -1);

        // Facilities
        $tblStudents = new Application_Model_DbTable_Students();
        $tblConfig = new Application_Model_DbTable_Configuration();

        //main data selection
        $select = $tblStudents->select()
                ->from($tblStudents, 'ASSESMENT_FACILITIES, Count(ASSESMENT_FACILITIES) as COUNT')
                ->where('ASSESMENT_FACILITIES > ?', 0)
                ->where('ASSESMENT_FACILITIES < ?', 60)
                ->group('ASSESMENT_FACILITIES')
                ->order('COUNT DESC')
        ;

        $chart6 = "";

        foreach ($tblCourses->fetchAll($select) as $result) {
            $chart6 .= "['" . $tblConfig->getConfiguration('ASSESMENT', $result->ASSESMENT_FACILITIES) . "'," . $result->COUNT . "],";
        }

        $this->view->chart6 = substr($chart6, 0, -1);

        // Location Chart
        $tblCourses = new Application_Model_DbTable_TrainingCourses();

        $select = $tblCourses->select()
                ->from($tblCourses, 'Count(VENUE) as COUNT')
                ->where('VENUE = ?', 'Loughton')
        ;

        $chart7 = "";
        foreach ($tblCourses->fetchAll($select) as $result) {
            $chart7 .= "['Loughton', " . $result->COUNT . "],";
        }
        
        //ECHO "<p>SQL: $select </p>";

        //$loughton = $tblCourses->fetchAll($select);
        //$loughton_count = $loughton->COUNT ;

        // Zend_Debug::dump($loughton_count);
        
        $select = $tblCourses->select()
                ->from($tblCourses, 'Count(VENUE) as COUNT')
                ->where('VENUE <> ?', 'Loughton')
        ;

        foreach ($tblCourses->fetchAll($select) as $result) {
            $chart7 .= "['Others', " . $result->COUNT . "],";
        }
        // $others = $tblCourses->fetchAll($select);
        // $other_count = $others->COUNT ;

        // Zend_Debug::dump($loughton_count);
        // Zend_Debug::dump($other_count);
        // echo "<p>Loughton = " . $loughton_count . "</p>";
        // echo "<p>Others = " . $other_count . "</p>";

        
        //foreach ($tblEnquiries->fetchAll($select) as $result) {
        //$chart7 .= "['Loughton'," . $loughton_count . "],";
        //$chart7 .= "['Others'," . $other_count . "],";
        // }
        $this->view->chart7 = substr($chart7, 0, -1);
        
        // echo "<p>$this->view->chart7</p>" ; 
        
        
        // Days & Courses per year
        $tblCourses = new Application_Model_DbTable_TrainingCourses();

        $select = $tblCourses->select()
                ->from($tblCourses, 'Year(START_DATE) as YEAR, sum(NO_OF_DAYS) as DAYS, count(course_code) as COURSES')
                ->group('YEAR(START_DATE)')
                ->order('YEAR(START_DATE) DESC')
        ;

        $chart8 = "['Year', 'Days', 'Courses'],";
        // $chart8 = "";
        
        foreach ($tblCourses->fetchAll($select) as $result) {
            $chart8 .= "['" . $result->YEAR . "'," . $result->DAYS . "," . $result->COURSES . "],";
        }
        
        $this->view->chart8 = substr($chart8, 0, -1);
        
        // ECHO "<p>SQL: $select </p>" ;
        // Zend_Debug::dump($this->view->chart8);
        
    }

}

