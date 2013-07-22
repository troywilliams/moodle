<?php
/**
 * @author David Vega M.
 * @package moodle uow cron
 *
 * Cronjob Plugin: Add, edit and delete cron jobs 
 *
 * This form class is part of the moodle forms
 * API to handle requests to add and edit crons
 *
 * 2007-08-20  File created.
 * 
 * @uses $CFG
 * @uses lib/formslib 
 * 
 */
 
require_once($CFG->dirroot.'/lib/formslib.php');

class cronjob_edit_form extends moodleform {

    // Define the form elements
    function definition() {
        global $CFG;

        $mform =& $this->_form;
        $strrequired = get_string('required');
        
        $jobperiods = array('600'     => '10 Min',
                            '1800'    => '30 Min',
                            '3600'    => 'Hour',
                            '7200'    => '2 Hours',
                            '18000'   => '5 Hours',
                            '86400'   => 'Day',
                            '172800'  => '2nd Day',
                            '604800'  => 'Week',
                            '1209600' => '2nd Week',
                            '2419200' => '4 Weeks');

        $mform->addElement('header', 'cronjob', 'Cron Job');
        
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('text', 'name', 'Job name', 'size="20"');
        $mform->addRule('name', $strrequired, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);
        
        $mform->addElement('text', 'filepath', 'File path', 'size="80"');
        $mform->addRule('filepath', $strrequired, 'required', null, 'server');
        $mform->setType('filepath', PARAM_PATH);
        
        $mform->addElement('select', 'period', 'Run job every', $jobperiods);
        $mform->addRule('period', $strrequired, 'required', null, 'server');
        $mform->setType('period', PARAM_INT);
        
        $mform->addElement('date_time_selector', 'nextrun', 'Start date-time');
        $mform->addRule('nextrun', $strrequired, 'required', null, 'server');
        $mform->setType('nextrun', PARAM_INT);
        $mform->setDefault('nextrun', time() + 3600 * 24);
                
        $this->add_action_buttons();        
    }
    
    //  Validate the submitted data
    function validation($data, $files){
        global $CFG;
        $errors= array();
        
        if (empty($data['filepath'])) {
            $errors['filepath'] = 'You must enter a path for the cron file';    
        }        
        else if (!is_file($data['filepath'])) {
            if (!($data['filepath']{0} == DIRECTORY_SEPARATOR)) {
                $data['filepath'] = DIRECTORY_SEPARATOR . $data['filepath'];
            }
            $data['filepath'] = $CFG->dirroot . $data['filepath'];
            
            if (!is_file($data['filepath'])) {
                $errors['filepath'] = 'You must enter a valid path for the cron file';
            }
        }
        
        if (empty($data['nextrun'])) {
            $errors['nextrun'] = 'You must enter a Start date fro the cron job';
        }
        else if ($data['nextrun'] < time()) {
            $errors['nextrun'] = 'Starting date/time needs to be greater than today/now';
        }

        if (0 == count($errors)){
            return true;
        } 
        else {
            return $errors;
        }
    }
}  //  End of cronjob_edit_form Class
?>
