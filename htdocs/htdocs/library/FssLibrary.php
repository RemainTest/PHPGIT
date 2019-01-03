<?php

/**
 * FormaServe General Utilities Library
 * 
 * @author      FormaServe Systems Ltd
 * @copyright   Copyright (c) 1990-2012 FormaServe Systems Ltd 
 * @project     f_Training
 * @category    Utilities
 * @package     f_Training
 * @subpackage  Utilities
 * @version     1.0.0
 * @since       File available since 1.0.0
 * @link        http://www.formaserve.co.uk 
 * 
 */

/**
 * Initialise
 *
 * @return string current user
 */
function fss_Init() {

    //$writer = new Zend_Log_Writer_Stream("/logs/application.log");
    //$logger = new Zend_Log($writer);

    Global $current_user;
    $current_user = $_SERVER['REMOTE_USER'];

    return $current_user;
}

/**
 * Get Current IBM i User Profile From The Session
 * 
 * @return string Current IBM i User
 */
function getCurrentUser() {

    return $_SERVER['REMOTE_USER'];
}

/**
 * Extract date from a DB2 timestamp field
 *
 * @param string $in_TimeStamp DB2 timestamp to extract
 * @return string|NULL date in format DD/MM/YYYY, returns NULL if input timestamp not found
 */
function getDateFromTimestamp($in_TimeStamp) {

    $out = NULL;

    If ($in_TimeStamp <> '') {
        $out = substr($in_TimeStamp, 8, 2) . "/" . substr($in_TimeStamp, 5, 2) . "/" . substr($in_TimeStamp, 0, 4);
    }

    return $out;
}

/**
 * Extract Hour from a DB2 timestamp field
 *
 * @param string $in_TimeStamp timestamp to extract
 * @return int time in format HH
 */
function getHourFromTimestamp($in_TimeStamp) {

    $out = '';

    If ($in_TimeStamp <> '') {
        $out = substr($in_TimeStamp, 11, 2);
    }

    return $out;
}

/**
 * Extract Time from a DB2 timestamp field
 *
 * @param string $in_TimeStamp timestamp to extract
 * @return string time in format HH.MM
 */
function getTimeFromTimestamp($in_TimeStamp) {

    $out = '';

    If ($in_TimeStamp <> '') {
        $out = substr($in_TimeStamp, 11, 5);
    }

    return $out;
}

/**
 * Convert DMY to ISO Date
 * 
 * #138 Cater for no input date specified - now returns NULL 26/06/2012
 *
 * @param string $in_Date date in format DD/MM/YY (Delimiters can be omitted)
 * @return string|NULL ISO Date - Returns NULL if no date input
 */
function convertDMYToISODate($in_Date) {

    $out_Date = NULL;

    if (trim($in_Date <> '')) {

        $pos = strpos($in_Date, '/');

        if ($pos === false) {
            // no delimiters in date
            $out_Date = substr($in_Date, 4, 4) . "-" . substr($in_Date, 2, 2) . "-" . substr($in_Date, 0, 2);
        } else {
            // delimiters found in date
            $date = explode('/', $in_Date);
            $out_Date = $date[2] . "-" . $date[1] . "-" . $date[0];
        }
    }

    return $out_Date;
}

/**
 * Convert DMY to ISO Date
 *
 * @param string $in_date date in format DD/MM/YY
 * @return string|NULL date in ISO format, returns NULL if input date not found
 */
function convertDMYToISO($in_Date) {

    $out_Date = NULL;

    if ($in_Date) {
        $date = explode('/', $in_Date);
        $out_Date = $date[2] . "-" . $date[1] . "-" . $date[0];
    }

    return $out_Date;
}

/**
 * Convert ISO to DMY Date
 *
 * @param string $in_date date in format DD/MM/YY (Delimiters can be omitted)
 * @return string|NULL date in ISO format, returns NULL if input date not found
 */
function convertISODatetoDMY($in_date) {

    $out_date = NULL;

    if ($in_date) {
        $date = explode('-', $in_date);
        $out_date = $date[2] . "/" . $date[1] . "/" . $date[0];
    }

    return $out_date;
}

/**
 * Convert email timestamp (RFC 2822) to DB timestamp
 *
 * @param string $in_date email timestamp
 * @return string ISO Date
 */
function convertEmailToTimestamp($in_date) {

    $out_Date = date("Y-m-d-H.i.s", strtotime($in_date)) . ".000000";
    return $out_Date;
}

/**
 * Find the first and last day of the month from the given date
 * Input date should be in yyyy-mm-dd format
 * @param string $anydate any date  
 * @return array 1st & last day of month
 */
function findFirstAndLastDay($anyDate) {

    // Separate year, month and date
    list($yr, $mn, $dt) = split('-', $anyDate);

    // Create time stamp of the first day from the give date
    $timeStamp = mktime(0, 0, 0, $mn, 1, $yr);

    // Get first day of the given month
    $firstDay = date('D', $timeStamp);

    // Find the last date of the month and separating it
    list($y, $m, $t) = split('-', date('Y-m-t', $timeStamp));

    // Create time stamp of the last date of the give month
    $lastDayTimeStamp = mktime(0, 0, 0, $m, $t, $y);

    // Find last day of the month
    $lastDay = date('D', $lastDayTimeStamp);

    // Return the result in an array format
    $arrDay = array("$firstDay", "$lastDay");

    return $arrDay;
}

/**
 * Log Messages
 * 
 * @param string $message Message to log
 * @return 
 */
function logW($message) {

    global $writer;
    global $logger;

    $writer = new Zend_Log_Writer_Stream("log" . date("Ymd") . ".log");
    $logger = new Zend_Log($writer);
    $logger->addPriority('FSS', 8);
    $logger->log($message, ZEND_LOG::INFO);

    return;
}

/**
 * Get the number of days between a date and the current date
 * 
 * @param string $ts timestamp
 * @return int No of Days
 */
function getDays($ts) {

    $start = substr($ts, 0, 10);
    $end = date("Y-m-d");

    $datetime1 = new DateTime($start);
    $datetime2 = new DateTime($end);
    $interval = $datetime1->diff($datetime2);

    $days = $interval->days;

    return $days;
}

/**
 * Get timestamp from a date & a time field
 * 
 * @param string $date any date  
 * @param string $time any time
 * @return string ISO timestamp
 */
function getTimestamp($date, $time) {

    $day = substr($date, 0, 2);
    $month = substr($date, 3, 2);
    $year = substr($date, 6, 4);

    $hour = substr($time, 0, 2);
    $min = substr($time, 3, 2);

    $ts = $year . '-' . $month . '-' . $day . '-' . $hour . '.' . $min . '.00.000000';

    return $ts;
}

/**
 * Get Course Title From Code
 *
 * @param string $code Course Code
 * @return string|NULL Course Title, returns NULL if not found
 */
function getCourseTitle($code = NULL) {

    $text = Null;

    if ($code) {

        $tblClasses = new Application_Model_DbTable_Classes();

        $row = $tblClasses->fetchRow(Array(
            "CODE = ?" => $code
        ));

        if ($row) {
            $text = $row ["TITLE"];
        }
    }

    return $text;
}

/**
 * Generate a HTML select string for drop down boxes
 *
 * @param string $name name of the select string
 * @param array $options array of select options
 * @param string $current any current value
 * @param string $blank Y/N field - Y if a blank entry is also required
 * @param string $change Y/N field - Y if the 'onchange' name is required
 * @param int $width input style width in px
 * @return string HTML select string
 */
function generateSelect($name, $options = array(), $current, $blank = 'N', $change = 'N', $width = 0) {

    if ($width > 0) {
        $select = "<select style='width:" . $width . "px;' name='";
    } else {
        $select = "<select name='";
    }

    if ($change == 'N') {
        $html = $select . $name . "' id='" . $name . "' >";
    } else {
        $html = $select . $name . "' id='" . $name . "' onchange='" . $change . "' >";
    }

    // do we need a blank entry in ere??
    if ($blank == 'Y') {
        $html .= "<option></option>";
    }

    foreach ($options as $option => $value) {

        if (trim($value) == trim($current))
            $html .= "<option value='" . $value . "' selected>" . $value . "</option>";
        else
            $html .= "<option value='" . $value . "'>" . $value . "</option>";
    }

    $html .= '</select>';

    return $html;
}

/**
 * Generate a HTML select string for drop down boxes
 *
 * @param string $name name of the select string
 * @param array $options array of select options
 * @param string $current any current value
 * @param string $blank Y/N field - Y if a blank entry is also required
 * @param string $change Y/N field - Y if the 'onchange' name is required
 * @param int $width input style width in px
 * @return string HTML select string
 */
function generateSelect2($name, $val = array(), $txt = array(), $current, $blank = 'N', $change = 'N', $width = 0) {

    if ($width > 0) {
        $select = "<select style='width:" . $width . "px;' name='";
    } else {
        $select = "<select name='";
    }

    if ($change == 'N') {
        $html = $select . $name . "' id='" . $name . "' >";
    } else {
        $html = $select . $name . "' id='" . $name . "' onchange='" . $change . "' >";
    }

    // do we need a blank entry in ere??
    if ($blank == 'Y') {
        $html .= "<option></option>";
    }

    $i = 0;
    foreach ($txt as $txt => $value) {
        if (trim($value) == trim($current))
            $html .= "<option value='" . $val[$i] . "' selected>" . $value . "</option>";
        else
            $html .= "<option value='" . $val[$i] . "'>" . $value . "</option>";
        $i++;
    }

    $html .= '</select>';

    return $html;
}

/**
 * Get the date & time from a timestamp for display purposes
 *
 * @param string $ts DB2 Timestamp format
 * @return string containing date & time
 */
function getDateTimeFromTimestamp($ts) {

    $datetime = getDateFromTimestamp($ts) . " " . getTimeFromTimestamp($ts);

    return $datetime;
}

/**
 * Get Status Code Descriptions
 *
 * @param string $code Status Code
 * @return string Status Description, returns blanks if not found
 */
function getStatusDescription($code = 0) {

    $text = '';
    $parameters = 'STATUS';

    // Configuration Table
    $parms = new Application_Model_DbTable_Configuration ();

    $row = $parms->fetchRow(Array(
        "PARAMETERS = ?" => $parameters,
        "PARM_VALUE = ?" => $code
    ));

    if ($row) {
        $text = $row ["PARM_TEXT"];
    }

    return $text;
}
/**
 * Get Status Code From Description
 *
 * @param string $des Status Description
 * @return string Status Code, returns blanks if not found
 */
function getStatusCode($des = '') {

    $text = '';
    $parameters = 'STATUS';

    // Configuration Table
    $parms = new Application_Model_DbTable_Configuration ();

    $row = $parms->fetchRow(Array(
        "PARAMETERS = ?" => $parameters,
        "PARM_TEXT = ?" => $des
    ));

    if ($row) {
        $text = $row ["PARM_VALUE"];
    }

    return $text;
}
/**
 * Get a configuration item text
 * 
 * @param string $parameters The main parameter group
 * @param string $parm_value The parameter value
 * @returns string|NULL Parameter Text value, or NULL if not found
 */
function getConfiguration($parameters, $parm_value) {

    $text = NULL;

    // Configuration Table
    $parms = new Application_Model_DbTable_Configuration ();

    $row = $parms->fetchRow(Array(
        "PARAMETERS = ?" => $parameters,
        "PARM_VALUE = ?" => $parm_value
    ));

    if ($row) {
        $text = $row ["PARM_TEXT"];
    }

    return $text;
}

/**
 * Get User ID from full name
 *
 * @param string $name Full User Name
 * @return string|NULL IBM i User ID, returns null if not found
 */
function getUserID($name = null) {

    $userID = null;

    If ($name <> NULL) {

        // User Table
        $tblUsers = new Application_Model_DbTable_UserAccounts();

        $row = $tblUsers->fetchRow(Array(
            "UPPER(FULL_NAME) = ?" => strtoupper($name)
        ));

        if ($row) {
            $userID = $row ["USER_ID"];
        }
    }

    return $userID;
}

/**
 * Check a date is valid
 *
 * @param $in_date string Date to be validated in ISO format
 * @return boolean if a date is valid
 */
function is_date($in_date) {

    $date = str_replace("-", "/", $in_date);
    $d = explode("/", $date);
    $valid = TRUE;

    if (!checkdate($d[1], $d[0], $d[2])) {
        $valid = FALSE;
    }

    return $valid;
}

/**
 * Get the current month name in full
 *
 * @return string Month name
 */
function getCurrentMonthName() {

    $jd_day = cal_to_jd(CAL_GREGORIAN, date("m"), date("d"), date("Y"));

    Return jdmonthname($jd_day, 1);
}

/**
 * Dump the DB2 SQL Statement
 * 
 * @return array|null 
 */
function dumpSQL() {
    $db = Zend_Db_Table::getDefaultAdapter();
    $dbProfiler = $db->getProfiler();
    $dbQuery = $dbProfiler->getLastQueryProfile();
    $dbSQL = $dbQuery->getQuery();

    print_r($dbSQL);

    return;
}

/**
 * Check if we have a numeric before we update the Database
 * 
 * @param mixed $string String to check
 * @return boolean true if var is a number or a numeric string, false otherwise
 *  
 */
function validate_numeric($variable) {

    return is_numeric($variable);
}

/**
 * Check if we only have digits before updating the Database
 * @param mixed $element
 * @return boolean true if var is only a digit number, false otherwise
 */
function is_digits($element) {

    return !preg_match("/[^0-9]/", $element);
}

/**
 * Get the full date/time for a DB2 timestamp field 
 * 
 * @param mixed $ts DB2 Timestamp to convert
 * @return string Date/Time in Format DD MMM YYYY HH.MM eg 04 Mar 1956 10.12
 */
function getFullDateTime($ts) {

    $date = substr($ts, 0, 10);
    $time = getTimeFromTimestamp($ts);

    $full_date = strftime("%d %b %Y", strtotime($date)) . ' ' . $time;

    Return $full_date;
}

/**
 * Get the full date for a timestamp ie 02 Mar 2012
 * 
 * @param mixed $ts DB2 Timestamp to convert
 * @return string Date/Time in Format DD MMM YYYY eg 04 Mar 1956
 */
function getFullDate($ts = NULL) {

    $outDate = NULL;

    If ($ts) {
        $date = substr($ts, 0, 10);

        $outDate = strftime("%d %b %Y", strtotime($date));
    }

    Return $outDate;
}

/**
 * Convert a full date to ISO format ie 02 Mar 2012 converts to 2012-03-02
 * 
 * @param mixed $date date to convert
 * @return string ISO Date
 */
function convertFullDateToISO($inDate = NULL) {

    $outDate = NULL;

    If ($inDate <> null) {

        $date = DateTime::createFromFormat('j M Y', $inDate);

        if (!is_object($date)) {
            throw new Exception("DateTime::createFromFormat Error for date: $inDate");
        } Else {
            $outDate = $date->format('Y-m-d');
        }
    }

    Return $outDate;
}

/**
 * Get current ISO timestamp from DB2
 *
 * @return string DB2 ISO timestamp
 */
function getCurrentTimestamp() {

    $db = Zend_Db_Table::getDefaultAdapter();

    $ts = $db->fetchOne('SELECT current timestamp FROM sysibm/sysdummy1');

    return $ts;
}

/**
 * Get Instructor Name
 * 
 * @param string $name Instructor Initials
 *
 * @return string|NULL Full instructor name, returns NULL if not found
 */
function getInstructor($name) {

    $text = NULL;

    $parameters = 'INSTRUCTORS';

    // Configuration Table
    $parms = new Application_Model_DbTable_Configuration ();

    $row = $parms->fetchRow(Array(
        "PARAMETERS = ?" => $parameters,
        "PARM_VALUE = ?" => $name
    ));

    if ($row) {
        $text = $row ["PARM_TEXT"];
    }

    return $text;
}

/**
 * Is a Student On A Course?
 * 
 * @param int $enq_no Enquiry Number
 * @param int $code Course Code
 * @param int $student Student ID
 * @return Bool Yes if on course, No if not
 */
function isOnCourse($enq_no, $code, $student) {

    $flag = FALSE;

    $tblCourseStudents = New Application_Model_DbTable_CourseStudents();
    
    $flag = $tblCourseStudents->isOnCourse($enq_no, $code, $student) ;
    
    Return $flag ;
}
/**
 * Get File Attachments Path
 *
 * @return string Attach Path, returns 'Attach Path Not Found' if not found
 */
function getAttachPath() {

    $text = 'Attach Path Not Found';

    $parameters = 'SYSTEM';
    $value = 'ATTACH_PATH';

// Configuration Table
    $parms = new Application_Model_DbTable_Configuration ();

    $row = $parms->fetchRow(Array(
        "PARAMETERS = ?" => $parameters,
        "PARM_VALUE = ?" => $value
    ));

    if ($row) {
        $text = $row ["PARM_TEXT"];
    }

    return $text;
}
