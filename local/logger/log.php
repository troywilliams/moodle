<?php

/**
 * @author David Vega M.
 * @package moodle uow logger
 *
 * Logger Plugin: Centralize place to view past 
 * process and admin reporting 
 *
 * This file displays all the log entries for a 
 * specific logger
 *
 * 2007-09-01   File created
 *
 * @uses $CFG
 * @uses lib/adminlib
 *
 */
    require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->dirroot.'/local/logger/logger.php');

    //  Check that the user is allow to perforn this task (i.e. admin)
    require_login();
    require_capability('moodle/site:config', context_system::instance());
    
    //  page vars
    $datetimeformat = 'D j, M y - h:i a';
    $id      = optional_param('id', 0, PARAM_INT);
    $counter = 0;
    $loglist = null;
    $where   = null;
    $table   = new html_table();

    //  The where conditions for the logger and if debug, show debug records
    $where = 'loggerid = '. $id;
    
    if (!get_config(null, 'debug')) {
        $where .= ' AND level > 399';
    }
    
    //  Get the log entries 
    //$loglist = get_records_select('uow_log_entries', $where, 'id ASC');
    $loglist = $DB->get_records_select('uow_log_entries', $where);
    
    // construct the table ready to display  
    if ($loglist) {
        $table->cellpadding = '3';
        $table->head = array('', 'Type', 'Description', );
        $table->align = array('center', 'left', 'left');
        $table->size = array('','90px', '');
        $table->data = array();
    
        foreach($loglist as $log) {
            $counter++;
            $type  = '<span style="color: #'. Logger::get_log_color($log->level) .';">'. Logger::get_log_code($log->level) .'</span>'; 
            $description = '<span style="color: #'. Logger::get_log_color($log->level) .';">'. $log->message .'</span>';    

            array_push($table->data, array($counter, $type, $description));
        }
    }
    else {
        $table->size = array('100%');
        $table->align = array('center');
        $table->data = array(array('There are no entries for this log'));
    }

     /***********  Display the page  ***************/
    admin_externalpage_setup('uowlogsconfig');
    echo $OUTPUT->header();
    echo $OUTPUT->heading('UoW Logs');

    echo html_writer::table($table);

    echo $OUTPUT->footer();

