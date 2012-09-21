<?php

/**
 * Form for selecting course categories and course formats for processing by course-format.php
 *
 * This class extends moodleform overriding the definition() method only
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

require_once($CFG->libdir.'/formslib.php');

class course_format_form extends moodleform {

    function definition() {

        global $CFG, $COURSE;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', '');

        $categories = (array)$this->_customdata['categories'];
        $categories = array('' => get_string('select').'...')+$categories;
        $mform->addElement('select', 'category', 'Category', $categories);

        $mform->addElement('text', 'coursename', 'Course name must contain');

        $courseNumbers = array('1'=>'1', '5'=>'5', '10'=>'10', '100'=>'100', '500'=>'500');
        $mform->addElement('select', 'maxtochange', 'Max courses to change', $courseNumbers);
        $mform->setDefault('maxtochange', 5);

        $mform->addElement('selectyesno', 'processvisible', 'Process visible courses');
        $mform->setDefault('processvisible', '0');

        $mform->addElement('selectyesno', 'makevisible', 'Make courses visible');
        $mform->addHelpButton('makevisible', 'makevisible', 'local_courseformatchanger');
        $mform->setDefault('makevisible', '0');

        $mform->addElement('selectyesno', 'processviewed', 'Process viewed courses');
        $mform->setDefault('processviewed', '0');

        $viewthresholdVals = array('1'=>'1', '5'=>'5', '10'=>'10', '100'=>'100', '500'=>'500');
        $mform->addElement('select', 'viewthreshold', 'View skip threshold', $viewthresholdVals);
        $mform->addHelpButton('viewthreshold', 'viewthreshold', 'local_courseformatchanger');
        $mform->setDefault('viewthreshold', 5);

        $mform->addElement('text', 'coursesections', 'Number of course sections');
        $mform->setDefault('coursesections', 1);
        $mform->setType('coursesections', PARAM_INT);

        $courseformats = get_list_of_plugins('course/format');
        $formcourseformats = array();
        foreach ($courseformats as $courseformat) {
            $formcourseformats["$courseformat"] = get_string("pluginname","format_$courseformat");
            if($formcourseformats["$courseformat"]=="[[format$courseformat]]") {
                $formcourseformats["$courseformat"] = get_string("format$courseformat");
            }
        }
        $mform->addElement('select', 'format', get_string('format'), $formcourseformats);

        $mform->addElement('selectyesno', 'updatenow', 'Update Now');
        $mform->addHelpButton('updatenow', 'updatenow', 'local_courseformatchanger');
        $mform->setDefault('updatenow', '0');

        $debuglevels = (array)$this->_customdata['debuglevels'];
        $mform->addElement('select', 'debug', 'Debug level', $debuglevels);
        $mform->setDefault('debug', '3');

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);

        $this->add_action_buttons(true, 'Go');
    }
}
?>
