<?php
    
/**
 * @author David Vega M.
 * @package moodle uow logger
 *
 * Logger Plugin: Centralize place to view past 
 * process and admin reporting 
 *
 * The aim of the pluing is to facilitate the 
 * means for which the system output admin 
 * task messages as well as keep record of 
 * those task.
 *
 * This file displays all the existing loggers
 *
 * 2007-09-01  File created.
 * 
 * @uses $CFG
 * @uses lib/adminlib 
 * @uses uow-logger
 * 
 */
    
    require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->dirroot.'/local/logger/logger.php');
    
    //  Check that the user is allow to perforn this task (i.e. admin)        
    require_login();
    require_capability('moodle/site:config', context_system::instance());
        
    //  Page vars
    $dtformat = 'D j, M y - h:i a';
    $perpage  = 25;     //  how many logs per page
    $page     = optional_param('page', 0, PARAM_INT);   
    $table = new html_table();
    $logcount = null;
    $loglist  = null;
    $sql      = null;
    
    //  Cound the total amount of log(gers)
    $logcount = $DB->count_records('uow_logger');
        
    //  The SQL to get the loggers and status summary
    $sql = "SELECT l.id, l.name, l.type, l.creationtime, COUNT(DISTINCT le1.id) AS warning, COUNT(DISTINCT le2.id) AS error FROM {$CFG->prefix}uow_logger l LEFT JOIN {$CFG->prefix}uow_log_entries le1 ON l.id = le1.loggerid AND le1.level = 800 LEFT JOIN {$CFG->prefix}uow_log_entries le2 ON l.id = le2.loggerid AND le2.level = 1000 GROUP BY l.id, l.name, l.type, l.creationtime ORDER BY l.creationtime DESC";
    $loglist = $DB->get_records_sql($sql);

    // construct the table ready to display 
    if ($loglist) {   
        $table->head = array('Log Name', 'Type', 'Time', 'Status');
        $table->align = array('left', 'left', 'center', 'left');
        $table->data = array();
        
        foreach($loglist as $log) {
            $name  = "<a href=\"$CFG->wwwroot/local/logger/log.php?id=$log->id\">$log->name</a>";
            $status = '';    
                
            //  If the logger loged an error entry, the process failed
            if ($log->error) {
                $status = '<span style="color: #'. Logger::get_log_color('ERROR') .';">Process failed!</span>';
            }
            //  If it has any warning it finish ok but show warnings
            else if ($log->warning) {
                $status = '<span style="color: #'. Logger::get_log_color('WARNING') .';">Completed successfully but with warnings ('. $log->warning .')</span>';
            }
            //  If not errors nor warnings then all OK
            else {
                $status = '<span style="color: #'. Logger::get_log_color('FINE') .';">Completed successfully!</span>';
            }    
            array_push($table->data, array($name, $log->type, date($dtformat, $log->creationtime), $status));

        }
    }
    else {
        $table->size = array('100%');
        $table->align = array('center');
        $table->data = array(array('No logs recorded yet'));
    }
    
    
    /***********  Display the page  ***************/
    admin_externalpage_setup('uowlogsconfig');
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'local_logger'));

    echo html_writer::table($table);
    echo $OUTPUT->paging_bar($logcount, $page, $perpage, $CFG->wwwroot."/local/logger/index.php?");
    
    echo $OUTPUT->footer();

