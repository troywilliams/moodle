<?php

/**
 * @author David Vega M.
 * @package moodle uow cron
 *
 * Cronjob Plugin: Add, edit and delete cron jobs 
 *
 * Library functions for the UoW Cron Plugin
 *
 * 2007-08-20  File created.
 * 
 * @uses $CFG
 * @uses uow/logger 
 * 
 */

require_once($CFG->dirroot .'/local/logger/logger.php');

/**
 * Creates a new cron 
 * @param object $data - All the data needed to insert a new record to the cron table
 * @return boolean - True if the record was added successfully false otherwise
 */
function cronjob_add_instance($data) {
    global $DB;

    $data->filepath = cronjob_get_file($data->filepath);
    if ($DB->insert_record('uow_cronjob', $data)) {
        return true;         
    }
    return false;
} 

/**
 * Updates a cron record
 * @param object $data - All the data needed to update the cron record
 * @return boolean - True if the record was updated successfully false otherwise
 */
function cronjob_update_instance($data) {
    global $DB;

    $data->filepath = cronjob_get_file($data->filepath);
    if ($DB->update_record('uow_cronjob', $data)) {
        return true;
    }
    return false;
}

/**
 * Deletes a cron record from the database
 * @param int $id - The unique identifier for the cron record to be deleted
 * @return boolean - True if the record was deleted successfully false otherwise
 */
function cronjob_delete_instance($id) {
    global $DB;

    if ($DB->delete_records('uow_cronjob', array('id' => $id))) {
        return true;
    }
    return false;
} 

/**
 * Forces a cron job to run immediately
 * @param int $id - The unique identifier for the cron record to be run
 * @param object $cron - A cron record object to run
 * @param boolean $$manual - Whether the request to run the job is a direct 
 * request from the user (true) or is a unattended request (default false)
 */
function cronjob_run_job($id, $cron=null, $manual=false) {
    global $CFG, $DB, $OUTPUT;
    
    //  Get the cron record (if not pass already)    
    if (!$cron) {
        $cron = $DB->get_record('uow_cronjob', array('id' => $id));
    }    
    
    //  Check if the user has access (what about unattended requests ?????)
    //require_login();
    //$context = context_system::instance();
    //require_capability('moodle/site:config', $context);
    
    //  Logger details 
    $loggertype  = 'Cron Job';
    $loggerstart = 'Starting cron job: '. $cron->name .' schedule to run at '. date('d/m/Y H:i', $cron->nextrun);
    
    //  If a manual request, output log details for the user
    if ($manual) {
        $loggertype  = 'Cron Job - manual';
        $loggerstart = 'Starting manual request to run cron job: '. $cron->name;
        //print_header($loggertype, 'Cron Job - '. $cron->name, $cron->name, '', '', false, '&nbsp;', '&nbsp;');
        if (!CLI_SCRIPT) {
            admin_externalpage_setup('uowcronjobconfig');
            echo $OUTPUT->header();
            echo $OUTPUT->heading('UoW Cron Jobs');
        }
    }
    
    //  Start logger
    $logger =& logger_get_logger($cron->name, $loggertype, $manual);
    $logger->fine($loggerstart);
    
    //  Run the cron script file
    $starttime = microtime(true);
    
    if (!file_exists($cron->filepath)){
        $logger->error(get_string('filenotfound', 'error'));
    } else {
        require_once($cron->filepath);
    }
    
    $endtime = microtime(true);
    
    //  Close page if needed
    if ($manual) {
        if (!CLI_SCRIPT) {
            echo $OUTPUT->continue_button(new moodle_url("$CFG->wwwroot/local/cronjob/edit.php"));
            echo $OUTPUT->footer();
        }
    }
    
    //  Calculate the next time that the job is schedule to run    
    $cron->lastrun = time();
    
    while ($cron->nextrun < $cron->lastrun) { 
        $cron->nextrun = $cron->nextrun + $cron->period;
    }
    
    //  Just to keep php happy
    date_default_timezone_set('Pacific/Auckland');
    
    $logger->info('Job duration: '. round(($endtime - $starttime), 3) .' s<br />Job compleated at '. date('D j, M y - h:i a', $cron->lastrun) .' next time that this job is schedule to start is on '. date('D j, M y - h:i a', $cron->nextrun));
    
    //  Update the cron record with new next/last run dates/times
    $DB->update_record('uow_cronjob', $cron);
    
    return true;        
}

/**
 * Run all schedule jobs
 */
function cronjob_cron() {
    global $DB;
    $cronlist = null;
    
    if (!$cronlist = $DB->get_records('uow_cronjob', null, 'nextrun ASC')) {
        return;
    }
    
    //  Remove the default time limit in case that some jobs run for 
    //  very long time.  Also increase memory and tell apache that 
    //  the process can be recycle after it's done.
    @set_time_limit(0);
    @raise_memory_limit("192M");
    if (function_exists('apache_child_terminate')) {
        @apache_child_terminate();
    }
    
    foreach ($cronlist as $cron) {
        //  Only run if it is the right time to run
        if ($cron->nextrun <= time()) {
            logger_clear_logger();
            cronjob_run_job($cron->id, $cron);
        }    
    }

    //  Clear the logger to avoid other processes noise
    logger_clear_logger();
}

/**
 * Just a helper function to add $CFG->dirroot to the 
 * file path if necesary
 * @param string $filepath - The cron file path
 * @return string - $filepath with $CFG->dirroot prefix if necesary 
 */
function cronjob_get_file($filepath) {
    global $CFG;
    
    if (!is_file($filepath)) {
           $filepath = $CFG->dirroot . DIRECTORY_SEPARATOR . ltrim($filepath, DIRECTORY_SEPARATOR);
    }
    
    return $filepath;
}
?>
