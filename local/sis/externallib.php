<?php
/**
 * External SIS API
 *
 * @package    webservice
 * @subpackage sis
 * @copyright  ~copyright
 * @license    ~license
 */
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/externallib.php");

class sis_external extends external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */ 
    public static function get_bulk_course_activity_parameters(){
        return new external_function_parameters(
            array(
                'datetime' => new external_value(PARAM_TEXT, 
                                                 'datetime sequence identifier used to denote the synchronization reference point',
                                                 VALUE_REQUIRED
                                                 )
            )
        );
    }
    /**
     *
     * @param string $datetime
     * @return array An array of arrays user activity items
     */
    public static function get_bulk_course_activity($datetime){
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/sis/lib.php');
        
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        $capabilities = array('coursereport/log:view'); 
        if (!has_all_capabilities($capabilities, $context)) {
            throw new moodle_exception('Capabilities that are required: '. implode(', ', $capabilities), 'Error');
        }
        $validated = self::validate_parameters(self::get_bulk_course_activity_parameters(), array('datetime'=>$datetime));   
        // Convert datetime string to epoch 
        $epochdatetime = epoch_from_ISO8601($validated['datetime']);
        return sis_get_bulk_course_activity($epochdatetime);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_bulk_course_activity_returns(){
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'student_id' => new external_value(PARAM_RAW, 'students offical id number'),
                    'username' => new external_value(PARAM_RAW, 'students system username'),
                    'activity_name' => new external_value(PARAM_RAW, 'module or component'),
                    'action_name' => new external_value(PARAM_RAW, 'action within module or component'),
		    'action_date' => new external_value(PARAM_RAW, 'date/time action was performed'),
		    'action_count' => new external_value(PARAM_RAW, 'action count', VALUE_OPTIONAL),
                    'paper_occurrence_code' => new external_value(PARAM_RAW, 'paper in which action occured'),
                )
            ), 'Array of arrays'
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */ 
    public static function get_course_activity_by_idnumber_parameters() {
        return new external_function_parameters(
            array(
                'idnumber' => new external_value(PARAM_TEXT, 
                                                 'retrieve activity for course by course identifier',
                                                 VALUE_REQUIRED
                                                 )
            )
        );
    }
    /**
     *
     * @param string $idnumber
     * @return array of array user activity items
     */
    public static function get_course_activity_by_idnumber($idnumber) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/sis/lib.php');
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        $capabilities = array('coursereport/log:view'); 
        if (!has_all_capabilities($capabilities, $context)) {
            throw new moodle_exception('Capabilities that are required: '. implode(', ', $capabilities), 'Error');
        }
        $validated = self::validate_parameters(self::get_course_activity_by_idnumber_parameters(), array('idnumber'=>$idnumber));
        return sis_get_course_activity_by_idnumber($validated['idnumber']);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_course_activity_by_idnumber_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'student_id' => new external_value(PARAM_RAW, 'students offical id number'),
                    'username' => new external_value(PARAM_RAW, 'students system username'),
                    'activity_name' => new external_value(PARAM_RAW, 'module or component'),
                    'action_name' => new external_value(PARAM_RAW, 'action within module or component'),
		    'action_date' => new external_value(PARAM_RAW, 'date/time action was performed'),
		    'action_count' => new external_value(PARAM_RAW, 'action count', VALUE_OPTIONAL),
                    'paper_occurrence_code' => new external_value(PARAM_RAW, 'paper in which action occured'),
                )
            ), 'Array of arrays'
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */  
    public static function get_bulk_course_assessments_parameters(){
        return new external_function_parameters(
            array(
                'datetime' => new external_value(PARAM_TEXT, 
                                                 'datetime sequence identifier used to denote the synchronization reference point',
                                                 VALUE_REQUIRED
                                                 )
            )
        );
    }
    /**
     *
     * @param string $datetime
     * @return array An array of arrays user activity items
     */
    public static function get_bulk_course_assessments($datetime){
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/sis/lib.php');
        
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        $capabilities = array('moodle/grade:manage', 'moodle/grade:export'); 
        if (!has_all_capabilities($capabilities, $context)) {
            throw new moodle_exception('Capabilities that are required: '. implode(', ', $capabilities), 'Error');
        }
        $validated = self::validate_parameters(self::get_bulk_course_assessments_parameters(), array('datetime'=>$datetime));
        // Convert datetime string to epoch 
        $epochdatetime = epoch_from_ISO8601($validated['datetime']);
        return sis_get_bulk_course_assessments($epochdatetime);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_bulk_course_assessments_returns(){
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'paper_occurrence_code' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'source_paper_occurrence_code' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'source_assessment_id' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'parent_source_assessment_id' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'assessment_code' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'assessment_name' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'assessment_type'=> new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'assessment_description'=> new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'asssement_status' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'compulsory' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'sort_order' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'weighting' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'maximum_mark' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'due_date' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'submit_location' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'display_result' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'collection_date' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'collection_location' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                )
            ), 'Description of returned structure'
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */  
    public static function get_course_assessments_by_idnumber_parameters() {
        return new external_function_parameters(
            array(
                'idnumber' => new external_value(PARAM_TEXT, 
                                                 'retrieve assessments for course by course identifier',
                                                 VALUE_REQUIRED
                                                 )
            )
        );
    }
    /**
     *
     * @param string $idnumber
     * @return array of array
     */
    public static function get_course_assessments_by_idnumber($idnumber) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/sis/lib.php');
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        $capabilities = array('moodle/grade:manage', 'moodle/grade:export'); 
        if (!has_all_capabilities($capabilities, $context)) {
            throw new moodle_exception('Capabilities that are required: '. implode(', ', $capabilities), 'Error');
        }
        $validated = self::validate_parameters(self::get_course_assessments_by_idnumber_parameters(), array('idnumber'=>$idnumber));
        return sis_get_course_assessments_by_idnumber($validated['idnumber']);
        
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_course_assessments_by_idnumber_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'paper_occurrence_code' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'source_paper_occurrence_code' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'source_assessment_id' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'parent_source_assessment_id' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'assessment_code' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'assessment_name' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'assessment_type'=> new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'assessment_description'=> new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'asssement_status' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'compulsory' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'sort_order' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'weighting' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'maximum_mark' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'due_date' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'submit_location' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'display_result' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'collection_date' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'collection_location' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                )
            ), 'Description of returned structure'
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */ 
    public static function get_bulk_course_results_parameters(){
        return new external_function_parameters(
            array(
                'datetime' => new external_value(PARAM_TEXT, 
                                                 'datetime sequence identifier used to denote the synchronization reference point',
                                                 VALUE_REQUIRED
                                                 )
            )
        );
    }
    /**
     *
     * @param string $datetime
     * @return array An array of arrays user activity items
     */
    public static function get_bulk_course_results($datetime){
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/sis/lib.php');
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        $capabilities = array('moodle/grade:viewall', 'moodle/grade:export'); 
        if (!has_all_capabilities($capabilities, $context)) {
            throw new moodle_exception('Capabilities that are required: '. implode(', ', $capabilities), 'Error');
        }
        $params = self::validate_parameters(self::get_bulk_course_results_parameters(), array('datetime'=>$datetime));
        // Convert datetime string to epoch 
        $epochdatetime = epoch_from_ISO8601($datetime);
        return sis_get_bulk_course_results($epochdatetime);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_bulk_course_results_returns(){
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'student_id' => new external_value(PARAM_RAW, 'University student identifier', VALUE_OPTIONAL),
                    'username' => new external_value(PARAM_RAW, 'University system username', VALUE_OPTIONAL),
                    'paper_occurrence_code' => new external_value(PARAM_RAW, 'Offical Paper Occurrence Code', VALUE_OPTIONAL),
                    'source_paper_occurrence_code' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'source_assessment_id' => new external_value(PARAM_RAW, 'Identifies the assessment item in the source system', VALUE_OPTIONAL),
                    'grade' => new external_value(PARAM_RAW, 'Number grade raw to 5dp', VALUE_OPTIONAL),
                    'mark' => new external_value(PARAM_RAW, 'Letter grade A-F', VALUE_OPTIONAL),
                    'percentage'=> new external_value(PARAM_RAW, 'Percentage grade', VALUE_OPTIONAL),
                    'contribution'=> new external_value(PARAM_RAW, 'Contribution fraction to total grade', VALUE_OPTIONAL),
                    'submitted_date' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'achieved_date' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'result_status' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                )
            ), 'Results for assessment items for students.'
        );
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */ 
    public static function get_course_results_by_idnumber_parameters() {
        return new external_function_parameters(
            array(
                'idnumber' => new external_value(PARAM_TEXT, 
                                                 'course identifier',
                                                 VALUE_REQUIRED
                                                 )
            )
        );
    }
    /**
     *
     * @param string $idnumber
     * @return array An array of arrays user activity items
     */
    public static function get_course_results_by_idnumber($idnumber) {
        global $CFG, $DB;
        require_once("$CFG->dirroot/local/sis/lib.php");
        // Ensure the current user is allowed to run this function
        $context = get_context_instance(CONTEXT_SYSTEM);
        self::validate_context($context);
        $capabilities = array('moodle/grade:viewall', 'moodle/grade:export'); 
        if (!has_all_capabilities($capabilities, $context)) {
            throw new moodle_exception('Capabilities that are required: '. implode(', ', $capabilities), 'Error');
        }
        $params = self::validate_parameters(self::get_course_results_by_idnumber_parameters(), array('idnumber'=>$idnumber));
        return sis_get_course_results_by_idnumber($idnumber);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_course_results_by_idnumber_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'student_id' => new external_value(PARAM_RAW, 'University student identifier', VALUE_OPTIONAL),
                    'username' => new external_value(PARAM_RAW, 'University system username', VALUE_OPTIONAL),
                    'paper_occurrence_code' => new external_value(PARAM_RAW, 'Offical Paper Occurrence Code', VALUE_OPTIONAL),
                    'source_paper_occurrence_code' => new external_value(PARAM_RAW, '@TODO', VALUE_OPTIONAL),
                    'source_assessment_id' => new external_value(PARAM_RAW, 'Identifies the assessment item in the source system', VALUE_OPTIONAL),
                    'grade' => new external_value(PARAM_RAW, 'Number grade raw to 5dp', VALUE_OPTIONAL),
                    'mark' => new external_value(PARAM_RAW, 'Letter grade A-F', VALUE_OPTIONAL),
                    'percentage'=> new external_value(PARAM_RAW, 'Percentage grade', VALUE_OPTIONAL),
                    'contribution'=> new external_value(PARAM_RAW, 'Contribution fraction to total grade', VALUE_OPTIONAL),
                    'submitted_date' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'achieved_date' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                    'result_status' => new external_value(PARAM_RAW, 'n/a', VALUE_OPTIONAL),
                )
            ), 'Results for assessment items for students.'
        );
    }
}

?>
