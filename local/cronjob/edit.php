<?php
/**
 * @author David Vega M.
 * @package moodle uow cron
 *
 * Cronjob Plugin: Add, edit and delete cron jobs 
 *
 * This plugin allows the administration of regular 
 * unsupervised tasks (or cron jobs).
 *
 *
 * 2007-08-20  File created.
 * 
 * @uses $CFG
 * @uses lib/adminlib 
 * 
 */

    require_once('../../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->dirroot.'/local/cronjob/lib.php');
    require_once($CFG->dirroot.'/local/cronjob/edit_form.php');
    // Hack alert, this does some work setting up $PAGE
    admin_externalpage_setup('uowcronjobconfig');
    
    //  Check that the user is allow to perforn this task (i.e. admin)    
    require_login();
    $context = context_system::instance();
    require_capability('moodle/site:config', $context);
        
    //  From vars
    $dtformat = 'D j, M Y - h:i a';
    $id       = optional_param('id', 0, PARAM_INT);
    $deleteid = optional_param('delete', 0, PARAM_INT);
    $runnowid = optional_param('runnow', 0, PARAM_INT);
    $cronlist = null;
    $cronObj  = null;
    $editform = null;
    $table = new html_table();
    
    $PAGE->set_context($context);
    $PAGE->set_url(new moodle_url($CFG->dirroot.'/local/cronjob/edit.php', array('id' => $id,
                                                                                 'deleteid' => $deleteid,
                                                                             'runnowid' => $runnowid)));
    
    //  Delete the cron
    if ($deleteid && confirm_sesskey()) {
        if (!cronjob_delete_instance($deleteid)) {
            print_error('Error occurred while deleting the cron job record');
        }
    }
    //  Run the cron now
    else if ($runnowid && confirm_sesskey()) {
        if (!cronjob_run_job($runnowid, null, true)) {
            print_error('Error occurred while trying to run the job');
        }
        else {
            exit;
        }
    }    
    //  Else load the cron (if id is passed)
    else if ($id && confirm_sesskey()) { // editing cron
        if (!$cronObj = $DB->get_record('uow_cronjob', array('id' => $id))) {
            error('Cron job ID was incorrect');
        }
    } 
    
    //  Create a form object
    $editform = new cronjob_edit_form('edit.php');
    
    //  The three acctions of a form (cancel, add/save, edit)            
    if ($editform->is_cancelled()){
        redirect($CFG->wwwroot);
    } 
    else if ($data = $editform->get_data()) {
         if (!empty($cronObj)) {
             if (!cronjob_update_instance($data)) {
                 print_error('Cron job could NOT be updated');
             }
         }
         else {
             if (!cronjob_add_instance($data)) {
                 print_error('Cron job could NOT be created');
             }
         }
         redirect($CFG->wwwroot.'/local/cronjob/edit.php');
    }
    else if(!empty($cronObj)) {
        $editform->set_data($cronObj);    
    }
    
    //  Get all cron jobs for displaying
    if ($cronlist = $DB->get_records('uow_cronjob')) {
    
        // construct the flexible table ready to display    
        $table->head  = array('Job', 'File', 'Next', 'Last', '', '', '');
        $table->align = array('left', 'left', 'center', 'center','center', 'center','center');
        $table->width = '90%';
        $table->data  = array();
        
        foreach($cronlist as $cron) {
            $nextrun  = '';
            $lastrun  = '';
            $filepath = str_replace($CFG->dirroot, '', $cron->filepath);
            $delete   = "<a href=\"$CFG->wwwroot/local/cronjob/edit.php?delete=$cron->id&amp;sesskey=$USER->sesskey\" onclick=\"return confirm('Are you sure you want to delete this cron job?');\">delete</a>";
            $edit     = "<a href=\"$CFG->wwwroot/local/cronjob/edit.php?id=$cron->id&amp;sesskey=$USER->sesskey\">edit</a>";
            $runnow   = "<a href=\"$CFG->wwwroot/local/cronjob/edit.php?runnow=$cron->id&amp;sesskey=$USER->sesskey\">run now</a>";
            
            if ($cron->lastrun) {
                $lastrun = date($dtformat, $cron->lastrun);
            }
            
            if ($cron->nextrun) {
                $nextrun = date($dtformat, $cron->nextrun);
            }
            
            array_push($table->data, array($cron->name, $filepath, $nextrun, $lastrun, $delete, $edit, $runnow));
        }
    }
    else {
        $table->size  = array('100%');
        $table->align = array('center');
        $table->data  = array(array('No jobs added yet'));
    }

    /***********  Display the page  ***************/
    
    echo $OUTPUT->header();
    echo $OUTPUT->heading('UoW Cron Jobs');

    //  show the list of cron jobs (if any)
    echo html_writer::table($table);

     //  show the form
    $editform->display();

    echo $OUTPUT->footer();

