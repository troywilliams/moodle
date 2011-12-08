<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_meta_addmultiple_form extends moodleform {
    protected $course;
    private static $jsmodule = array(
                'name' => 'course_selector',
                'fullpath' => '/enrol/meta/module.js',
                'requires'  => array('node', 'event-custom', 'datasource', 'json'));

    function definition() {
        global $CFG, $DB, $PAGE;

        $mform  = $this->_form;
        $course = $this->_customdata['course'];
        $availablecourses = $this->_customdata['availablecourses'];
        $this->course = $course;


        $mform->addElement('header','general', get_string('pluginname', 'enrol_meta'));
        $mform->addElement('select', 'links', get_string('linkcourses', 'enrol_meta'), $availablecourses, array('size'=>10, 'multiple'=>true));
        $mform->addRule('links', get_string('required'), 'required', null, 'client');
        
        $searchgroup = array();
        $searchgroup[] = &$mform->createElement('text', 'links_searchtext');
        $searchgroup[] = &$mform->createElement('submit', 'links_searchbutton', get_string('search'));
        $mform->registerNoSubmitButton('links_searchbutton');
        $searchgroup[] = &$mform->createElement('submit', 'links_clearbutton', get_string('clear'));
        $mform->registerNoSubmitButton('links_clearbutton');
        $mform->addGroup($searchgroup, 'searchgroup', get_string('search') , array(' '), false);

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('add'));

        $this->set_data(array('id'=>$course->id));
        $PAGE->requires->js_init_call('M.core_enrol.init_course_selector', array('links', $course->id), true, self::$jsmodule);

    }

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        // TODO: this is duplicated here because it may be necessary one we implement ajax course selection element

        $errors = parent::validation($data, $files);
        if (!isset($data['links'])){
            $errors['links'] = get_string('required');
        }
        // TODO: context and capability checks maybe
        return $errors;
    }
}

?>
