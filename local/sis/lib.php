<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/lib.php');
//////////////////////////
/// MAIN SIS FUNCTIONS ///
//////////////////////////
/**
 * Get moodle activity logs for a particular course object.
 * 
 * Note: Doesn't handle meta linked enrolments
 * 
 * @global object $DB
 * @param object $course
 * @param integer $epochdatetime
 * @return array of arrays
 */
function sis_get_course_activity($course, $epochdatetime=0) {
    global $DB;
    $site = get_site();
    $activity = array();

    if (!is_object($course)) {
        throw new invalid_parameter_exception('must be a course object');
    }
    if ($course->id == $site->id) {
        return $activity; // we don't use site activity
    }
    if (!isset($course->idnumber)) {
        throw new invalid_parameter_exception('missing course idnumber');
    }
    if (!is_integer($epochdatetime)) { // check epoch
        throw new coding_exception('Incorrect parameter type!'); 
    }
    // fields required to build rceord
    $fields = 'l.id, u.id AS userid, u.username, u.idnumber AS studentidnumber,
               c.id AS courseid, c.shortname, c.idnumber as courseidnumber,
               l.module, l.action, l.time';
    // join course and user tables for required fields, idnumber for students has length of 7
    // MUST: be ordered by course idnumber for cache.
    $sql = "SELECT $fields FROM {log} l
            LEFT JOIN {course} c
                ON l.course = c.id
            LEFT JOIN {user} u 
                ON l.userid = u.id 
            WHERE " . $DB->sql_length('u.idnumber') . " = 7 
            AND c.idnumber = ?    
            AND l.time >= ?
            ORDER BY c.idnumber ASC, u.idnumber ASC, l.time ASC";
    // fetch the set
    $rs = $DB->get_recordset_sql($sql, array($course->idnumber, $epochdatetime));
    if ($rs->valid()) {
        foreach ($rs as $record) { 
            $activityitem = new stdClass();
            $activityitem->student_id = $record->studentidnumber;
            $activityitem->username = $record->username;
            $activityitem->activity_name = $record->module;
            $activityitem->action_name = $record->action;
            $activityitem->action_date = ISO8601_from_epoch($record->time);
            $activityitem->action_count = 1;
            $activityitem->paper_occurrence_code = $record->courseidnumber;
            $activity[] = (array) $activityitem; //cast to array
            unset($activityitem);
        }
    }
    $rs->close();
    return $activity;
}
/**
 * Get moodle activity logs for a particular course idnnumber.
 * 
 * Note: Doesn't handle meta linked enrolments
 * 
 * @global object $DB
 * @param string $idnumber
 * @return array of arrays
 */
function sis_get_course_activity_by_idnumber($idnumber) {
    global $DB; 
    
    $idnumber = trim($idnumber);
    if (!is_string($idnumber)) {
        throw new invalid_parameter_exception('string required');
    }    
    try {
        $course = $DB->get_record('course', array('idnumber'=>$idnumber), 'id, shortname, fullname, idnumber, visible', MUST_EXIST);
    } catch (dml_missing_record_exception $e) {
        // create new moodle exception SoapFault will send out
       throw new moodle_exception($e->getMessage());
    }
    return sis_get_course_activity($course);
}
/**
 *
 * @global object $DB
 * @param string $epochdatetime
 * @return array of arrays
 */
function sis_get_bulk_course_activity($epochdatetime) {
    global $DB;
    $activity = array();
    
    // check epoch
    if (!is_integer($epochdatetime)) {
        throw new coding_exception('Incorrect parameter type!');
    }
    
    $sql = 'SELECT c.id, c.idnumber, shortname, c.fullname 
            FROM mdl_course c 
            WHERE c.id IN (SELECT DISTINCT (l.course) FROM mdl_log l 
                           WHERE l.time >= ?) 
            AND c.idnumber IS NOT NULL';
    
    // fetch the set
    $courses = $DB->get_recordset_sql($sql, array($epochdatetime));
    if ($courses->valid()) {
        foreach ($courses as $course) {
            // skip parents of meta linked papers;
            if ($DB->record_exists('enrol', array('enrol'=>'meta','courseid'=>$course->id))){
                continue;
            }
            $courseactivity = sis_get_course_activity($course, $epochdatetime);
            $activity = array_merge($activity, $courseactivity);  
            unset($courseactivity); // free mem
        }
    }
    $courses->close();
    return $activity;
}
/**
 * Get the assessment structure for particular course. This is built from gradebook grade_item and
 * grade_category information. This is all information from a Moodle course site. Is flatten tree 
 * structure with child items referencing parent items top down based on sort order.
 * 
 * @global object $DB
 * @param object $course
 * @return array of arrays 
 */
function sis_get_course_assessments($course) {
    global $DB; 
    $assessments = array();

    if (!is_object($course)) {
        throw new invalid_parameter_exception('must be a course object');
    }
    if (!isset($course->idnumber)) {
        throw new invalid_parameter_exception('missing course idnumber');
    }
    // first make sure we have proper final grades - this MUST be done
    grade_regrade_final_grades($course->id);
    // get all grade items as grade objects. will build own flat structure. TODO investigate grade_tree class
    $gradeitems = grade_item::fetch_all(array('courseid'=>$course->id));
    $sortedgradeitems = array();
    foreach($gradeitems as $key => $gradeitem) {
        $sortedgradeitems[$key] = $gradeitem->sortorder;
    }
    asort($sortedgradeitems);
    foreach ($sortedgradeitems as $key => $gradeitem) {
        $sortedgradeitems[$key] = $gradeitems[$key];
    }
    unset($gradeitems);
    if ($sortedgradeitems) {
        foreach ($sortedgradeitems as $gradeitem) {
            try { // Catch any possible errors.
                if ($gradeitem->is_course_item()) { // top of tree
                    $coursetotalid = $gradeitem->id;
                    $gradeitem->parentgradeitemid = null;
                    $gradeitem->itemname = 'Course total'; // TODO string lookup
                }
                if ($gradeitem->is_category_item()) {
                    $categoryitem = $gradeitem->get_item_category();
                    $parentcategoryitem = $categoryitem->get_parent_category();
                    if ($parentcategoryitem == null) { // MDL-27395 check for borked grade structure
                        throw new moodle_exception('borked grade structure, probably course and category with same iteminstance');
                    }
                    $parentcategorygradeitem = $parentcategoryitem->get_grade_item();
                    $gradeitem->parentgradeitemid = $parentcategorygradeitem->id;
                    $gradeitem->itemname = $categoryitem->fullname;
                }
                if (!$gradeitem->is_course_item() && !$gradeitem->is_category_item()){
                    $parentcategoryitem = $gradeitem->get_parent_category();
                    $gradeitem->parentgradeitemid = $parentcategoryitem->get_grade_item()->id;
                }
            } catch (Exception $e) {
                error_log($e->errorcode); // TODO umm..
                continue;
            }
            // create assessment object
            $assessmentitem = new stdClass();
            $assessmentitem->source_assessment_id = $gradeitem->id;
            $assessmentitem->parent_source_assessment_id = $gradeitem->parentgradeitemid;
            $assessmentitem->paper_occurrence_code = $course->idnumber; 
            $assessmentitem->source_paper_occurrence_code = !empty($course->idnumber) ? $course->idnumber : $course->shortname;
            $assessmentitem->assessment_name = $gradeitem->itemname;
            if ($gradeitem->itemmodule) {
                $assessmentitem->assessment_type = $gradeitem->itemtype.'/'.$gradeitem->itemmodule;
            } else {
                $assessmentitem->assessment_type = $gradeitem->itemtype;
            }
            $assessmentitem->sort_order = $gradeitem->sortorder;
            $assessmentitem->weighting = $gradeitem->aggregationcoef;
            $assessmentitem->maximum_mark = $gradeitem->grademax;
        
            $assessments[] = (array) $assessmentitem; //need to cast as array
            unset($assessmentitem);
        }    
    }
    return $assessments;
}
/**
 * Get all assessments for a particular course idnumber.
 * 
 * If course has meta link enrolments, assessments will be fetched
 * from primary course and any courses where the primary course
 * has been used as a enrolment meta link.
 * 
 * @global object $DB
 * @param string $idnumber
 * @return array of arrays
 */
function sis_get_course_assessments_by_idnumber($idnumber) {
    global $DB; 
    
    $idnumber = trim($idnumber);
    if (!is_string($idnumber)) {
        throw new invalid_parameter_exception('string required');
    }
    
    try {
        $course = $DB->get_record('course', array('idnumber'=>$idnumber), 'id, shortname, fullname, idnumber, visible', MUST_EXIST);
    } catch (dml_missing_record_exception $e) {
        // create new moodle exception SoapFault will send out
       throw new moodle_exception($e->getMessage());
    }
    
    $course->assessments = sis_get_course_assessments($course);
   
    $sql = "SELECT c.id, c.idnumber, c.shortname, c.fullname 
            FROM mdl_course c
            LEFT JOIN mdl_enrol e
            ON c.id = e.courseid
            WHERE e.enrol = 'meta'
            AND e.customint1 = ?";
    
    $enrolmetalinkedcourses = $DB->get_records_sql($sql, array($course->id));
    
    $assessments = array();
    
    if ($enrolmetalinkedcourses) {
        foreach ($enrolmetalinkedcourses as $enrolmetalinkedcourse) {
            $enrolmetalinkedcourse->assessments = sis_get_course_assessments($enrolmetalinkedcourse);
            foreach ($enrolmetalinkedcourse->assessments as &$assessment) {
                $assessment['paper_occurrence_code'] = $course->idnumber;
                $assessments[] = $assessment;
            }
        }
    }
    return array_merge($course->assessments, $assessments);
}
/**
 * Get all course assessments for courses that have been modified
 * after a datetime.
 * 
 * If course has meta link enrolments, assessments will be fetched
 * from primary course and any courses where the primary course
 * has been used as a enrolment meta link.
 * 
 * @global object $DB
 * @param integer $epochdatetime
 * @return array of arrays
 */
function sis_get_bulk_course_assessments($epochdatetime) {
    global $DB;
    
    $assessments = array();
    // check epoch
    if (!is_integer($epochdatetime)) {
        throw new coding_exception('Incorrect parameter type!');
    }
    
    $fields = 'c.id, c.idnumber, c.shortname, c.fullname';
    
    $sql = "SELECT $fields 
            FROM {course} c
            WHERE c.id IN (SELECT DISTINCT gi.courseid 
                           FROM {grade_items} gi 
                           WHERE gi.timemodified > ?)
            ORDER BY c.id";
    
    $courses = $DB->get_recordset_sql($sql, array($epochdatetime));
    
    if ($courses->valid()){
        foreach ($courses as $course) {
            /// Dirty ol meta link hack 4 Melo
            if ($DB->record_exists('enrol', array('enrol'=>'meta','courseid'=>$course->id))){
                
                $sql = "SELECT c.id, c.idnumber, c.shortname, c.fullname 
                        FROM mdl_course c
                        LEFT JOIN mdl_enrol e
                        ON c.id = e.customint1
                        WHERE e.enrol = 'meta'
                        AND e.courseid = ?";
    
                $enrolmetalinkedcourses = $DB->get_records_sql($sql, array($course->id));

                foreach ($enrolmetalinkedcourses as $enrolmetalinkedcourse) {
                    $enrolmetalinkedcourseassessments = sis_get_course_assessments_by_idnumber($enrolmetalinkedcourse->idnumber);
                    $assessments = array_merge($assessments, $enrolmetalinkedcourseassessments);
                }

            } else {
                $courseassessments = sis_get_course_assessments($course);
                $assessments = array_merge($assessments, $courseassessments);
            }
        }
    }
    $courses->close();
    return $assessments;  
}
/**
 * Get all student results for a particular course object.
 * 
 * @global object $CFG
 * @global object $DB
 * @param object $course
 * @return array of arrays 
 */
function sis_get_course_results($course) {
    global $CFG, $DB; 

    if (!is_object($course)) {
        throw new invalid_parameter_exception('incorrect parameter');
    }
    
    $results = array(); // container for returned results

    $sql = "SELECT gg.id, gg.itemid, gg.finalgrade, u.id AS userid, u.username, u.firstname, u.lastname, u.idnumber
                FROM {grade_grades} gg
            JOIN {grade_items} gi
                ON gg.itemid = gi.id
            JOIN {user} u 
                ON gg.userid = u.id 
            JOIN {course} c 
                ON gi.courseid = c.id
            WHERE gi.courseid = :courseid 
            ORDER BY u.username, gi.sortorder";
    
    $params = array('courseid'=>$course->id);
    $grades = $DB->get_records_sql($sql, $params);
    foreach ($grades as $grade) {
        $result = sis_build_grade_result($grade->itemid, $grade->finalgrade);
        $result->userid = $grade->userid;
        $result->username = $grade->username;
        $result->student_id = $grade->idnumber;
        $result->source_assessment_id = $grade->itemid;
        $result->paper_occurrence_code = $course->idnumber;
        $result->source_paper_occurrence_code = !empty($course->idnumber) ? $course->idnumber : $course->shortname;
        $results[] = (array) $result; // !Important must be cast as array
    }
    return $results;
}
/**
 * Get all student results for a particular course idnumber.
 * 
 * If course has meta link enrolments, results will be fetched
 * from primary course and any courses where the primary course
 * has been used as a enrolment meta link.
 * 
 * @global type $CFG
 * @global type $DB
 * @param type $idnumber
 * @return type 
 */
function sis_get_course_results_by_idnumber($idnumber) {
    global $CFG, $DB; 
    
    $idnumber = trim($idnumber);
    if (!is_string($idnumber)) {
        throw new invalid_parameter_exception('incorrect parameter');
    }    
    
    try {
        $course = $DB->get_record('course', array('idnumber'=>$idnumber), 'id, shortname, fullname, idnumber, visible', MUST_EXIST);
    } catch (dml_missing_record_exception $e) {
        // create new moodle exception SoapFault will send out
       throw new moodle_exception($e->getMessage());
    }
    
    $course->results = sis_get_course_results($course);

    $enrolmetalinks = $DB->get_records('enrol', array('enrol'=>'meta', 'customint1'=>$course->id), 'id, courseid, customint1');
    
    $results = array();
    if ($enrolmetalinks) {
        foreach($enrolmetalinks as $enrolmetalink) {
            $enrolmetalinkcourse = $DB->get_record('course', array('id'=>$enrolmetalink->courseid), 'id, shortname, fullname, idnumber, visible', MUST_EXIST);
            $enrolmetalinkcourseresults = sis_get_course_results($enrolmetalinkcourse);
            $userids = array_keys($DB->get_records('user_enrolments', array('enrolid'=>$enrolmetalink->id), 'userid', 'userid'));
            foreach ($enrolmetalinkcourseresults as &$enrolmetalinkcourseresult) {
                if (in_array($enrolmetalinkcourseresult['userid'], $userids)) {
                    $enrolmetalinkcourseresult['paper_occurrence_code'] = $course->idnumber;
                    $results[] = $enrolmetalinkcourseresult;
                }
            }
            unset($enrolmetalinkcourse);
            unset($enrolmetalinkcourseresults);
            unset($userids);
        }
    }
    return array_merge($course->results, $results);
}
/**
 * Get all course results for courses that have been modified
 * after a datetime.
 * 
 * If course has meta link enrolments, results will be fetched
 * from primary course and any courses where the primary course
 * has been used as a enrolment meta link.
 * 
 * @global object $DB
 * @param integer $epochdatetime
 * @return array of arrays 
 */
function sis_get_bulk_course_results($epochdatetime) {
    global $DB;
    
    $results = array();
    // check epoch
    if (!is_integer($epochdatetime)) {
        throw new coding_exception('Incorrect parameter type!');
    }
    //TODO context check, is this needed $context = get_context_instance(CONTEXT_SYSTEM); 
    $sql = 'SELECT c.id, c.shortname, c.fullname, c.idnumber 
            FROM {course} c 
            WHERE c.id IN (SELECT DISTINCT gi.courseid AS id
                            FROM {grade_items} gi
                            WHERE gi.id IN (SELECT DISTINCT gg.itemid 
                                            FROM {grade_grades} gg 
                                            WHERE gg.timemodified > ?)
                            ORDER BY gi.courseid ASC)';
    
    $courses = $DB->get_recordset_sql($sql, array($epochdatetime));
    if ($courses->valid()) {
        foreach ($courses as $course) {
             /// Dirty ol meta link hack 4 Melo
            if ($DB->record_exists('enrol', array('enrol'=>'meta','courseid'=>$course->id))){
                
                $sql = "SELECT c.id, c.idnumber, c.shortname, c.fullname 
                        FROM mdl_course c
                        LEFT JOIN mdl_enrol e
                        ON c.id = e.customint1
                        WHERE e.enrol = 'meta'
                        AND e.courseid = ?";
    
                $enrolmetalinkedcourses = $DB->get_records_sql($sql, array($course->id));

                foreach ($enrolmetalinkedcourses as $enrolmetalinkedcourse) {
                    $enrolmetalinkedcourseresults = sis_get_course_results_by_idnumber($enrolmetalinkedcourse->idnumber);
                    $results = array_merge($results, $enrolmetalinkedcourseresults);
                    unset($enrolmetalinkedcourseresults);
                }

            } else {
                $courseresults = sis_get_course_results($course);
                $results = array_merge($results, $courseresults);  
                unset($courseresults);
            }
        }
    }
    $courses->close();
    return $results;
}
/**
 * Helper function to build a grade result object.
 * 
 * @param int $gradeitemid
 * @param float $finalgrade
 * @return stdClass $grade
 */
function sis_build_grade_result($gradeitemid, $finalgrade) {
    $grade = new stdClass;
    $gradeitem = grade_item::fetch(array('id'=>$gradeitemid));
    //$grade->itemname = $gradeitem->itemname;
    //$grade->itemtype = $gradeitem->itemtype;
    $grade->grade = $finalgrade;
    //$grade->letter = grade_format_gradevalue_letter($finalgrade, $gradeitem);
    $grade->mark = grade_format_gradevalue_letter($finalgrade, $gradeitem);
    $grade->percentage = grade_format_gradevalue_percentage($finalgrade, $gradeitem, 0, true);
    $gradeitem->grademax = floatval($gradeitem->grademax);
    if ($gradeitem->get_coefstring() == 'aggregationcoefweight' and !$gradeitem->is_course_item()) {
        if (!empty($gradeitem->aggregationcoef) and !empty($gradeitem->grademax)) {
            $grade->contribution = ($gradeitem->aggregationcoef / $gradeitem->grademax) * $finalgrade;
        }
    }
    return $grade;
}
/////////////////////////
/// UTILITY FUNCTIONS ///
/////////////////////////
/**
 * Helper function to convert ISO8601 text formatted
 * datetime to epoch datetime.
 * 
 * @param type $iso8601
 * @return type 
 */
function epoch_from_ISO8601($iso8601){
    $systemtimezone = date_default_timezone_get();
    $datetime = DateTime::createFromFormat(DateTime::ISO8601, $iso8601, new DateTimeZone($systemtimezone));
    if (empty($datetime)) {
        throw new invalid_parameter_exception('IS0-8601 date/time required');
    }
    return $datetime->getTimestamp();
}
/**
 * Helper function to convert epoch datetime to a 
 * ISO8601 formatted datetime.
 * 
 * @param type $epoch
 * @return type 
 */
function ISO8601_from_epoch($epoch) {
    $systemtimezone = date_default_timezone_get();
    $datetime = DateTime::createFromFormat('U', $epoch, new DateTimeZone($systemtimezone));
    return $datetime->format(DateTime::ISO8601);
}
?>
