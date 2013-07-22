<?php
/**
 *  'web service function name' => array(                    
 *       'classname'   => 'class containing the external function',
 *       'methodname'  => 'external function name',
 *       'classpath'   => 'file containing the class/external function',
 *       'description' => 'human readable description of the web service function',
 *       'type'        => 'database rights of the web service function (read, write)',
 *       'capabilities'=> 'required user capabilities',
 *       'testclientpath' => 'local/sis/testclient.php',
 *   )
 */
$functions = array(
    'sis_get_course_activity_by_idnumber' => array(                    
        'classname'   => 'sis_external',                        
        'methodname'  => 'get_course_activity_by_idnumber',                               
        'classpath'   => 'local/sis/externallib.php',                        
        'description' => 'activity from a course by idnumber',        
        'type'        => 'read',
        'capabilities'=> 'coursereport/log:view',
        'testclientpath' => 'local/sis/testclient.php',
    ),
    'sis_get_bulk_course_activity' => array(                    
        'classname'   => 'sis_external',                        
        'methodname'  => 'get_bulk_course_activity',                               
        'classpath'   => 'local/sis/externallib.php',                        
        'description' => 'activity from all courses after certain date',        
        'type'        => 'read',
        'capabilities'=> 'coursereport/log:view',
        'testclientpath' => 'local/sis/testclient.php',
    ),
    'sis_get_course_assessments_by_idnumber' => array(                    
        'classname'   => 'sis_external',                        
        'methodname'  => 'get_course_assessments_by_idnumber',                               
        'classpath'   => 'local/sis/externallib.php',                        
        'description' => 'assessment structure for a course by idnumber',        
        'type'        => 'read',
        'capabilities'=> 'moodle/grade:manage, moodle/grade:export',
        'testclientpath' => 'local/sis/testclient.php',
    ),
    'sis_get_bulk_course_assessments' => array(                    
        'classname'   => 'sis_external',                        
        'methodname'  => 'get_bulk_course_assessments',                               
        'classpath'   => 'local/sis/externallib.php',                        
        'description' => 'assessment structure for all courses that have been modified after certain date',        
        'type'        => 'read',
        'capabilities'=> 'moodle/grade:manage, moodle/grade:export',
        'testclientpath' => 'local/sis/testclient.php',
    ),
    'sis_get_course_results_by_idnumber' => array(                    
        'classname'   => 'sis_external',                        
        'methodname'  => 'get_course_results_by_idnumber',                               
        'classpath'   => 'local/sis/externallib.php',                        
        'description' => 'results located in the gradebook for a course by idnumber',        
        'type'        => 'read',
        'capabilities'=> 'moodle/grade:viewall, moodle/grade:export',
        'testclientpath' => 'local/sis/testclient.php',
    ),
    'sis_get_bulk_course_results' => array(                    
        'classname'   => 'sis_external',                        
        'methodname'  => 'get_bulk_course_results',                               
        'classpath'   => 'local/sis/externallib.php',                        
        'description' => 'results located in the gradebook for all courses that have been modified after certain date',        
        'type'        => 'read',
        'capabilities'=> 'moodle/grade:viewall, moodle/grade:export',
        'testclientpath' => 'local/sis/testclient.php',
    ),
);
?>
