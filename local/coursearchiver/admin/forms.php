<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once($CFG->libdir . '/formslib.php');

class coursearchiver_settings_form extends moodleform {
    
    public function definition() {
        global $CFG;
        
        $mform = & $this->_form;
        
        $enabledoptions = array(0 => get_string('disabled', 'local_coursearchiver'), 1 => get_string('enabled', 'local_coursearchiver'));
        $enabledselect = $mform->addElement('select', 'enabled', get_string('active'), $enabledoptions);
        $enabledselect->setSelected(0);
        $mform->addHelpButton('enabled', 'enabled', 'local_coursearchiver');
        
        $mform->addElement('text', 'mbzstoredirectory', get_string('saveto'), array('size'=>'100'));
        $mform->setDefault('mbzstoredirectory', $CFG->dataroot.'/coursearchiver');
        //$mform->addRule('mbzstoredirectory', get_string('required'), 'required', null, 'client');
        
        $displaylist = array();
        $parentlist = array();
        make_categories_list($displaylist, $parentlist, 'moodle/category:manage');
        $displaylist = array(0 => get_string('select') . '...') + $displaylist;
        $mform->addElement('select', 'archivecategory', get_string('archivecategory', 'local_coursearchiver'), $displaylist);
        $mform->addHelpButton('archivecategory', 'archivecategory', 'local_coursearchiver');
        
        $graceperiodoptions = array(0 => get_string('none'),
                                    7 => get_string('numdays', '', 7),
                                    30 => get_string('numdays', '', 30),
                                    60 => get_string('numdays', '', 60),
                                    90 => get_string('numdays', '', 90));

        $graceperiodselect = $mform->addElement('select', 'graceperiod', get_string('graceperiod', 'local_coursearchiver'), $graceperiodoptions);
        $mform->addHelpButton('graceperiod', 'graceperiod', 'local_coursearchiver');
        
        $mform->addElement('duration', 'processingtime',
                get_string('cronprocessingtime', 'local_coursearchiver'));
        $mform->setDefault('processingtime', 60);

        
        
        // load existing config if exists
        $config = (array) get_config('local_coursearchiver');
        if ($config) {
            $this->set_data($config);
        }
        
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        if (!is_dir($data['mbzstoredirectory'])) {
            $errors['mbzstoredirectory'] = get_string('mbzstoredirectory_error', 'local_coursearchiver');
        }
        if (empty($data['archivecategory'])) {
            $errors['archivecategory'] = get_string('archivecategory_error', 'local_coursearchiver');
        }
        return $errors;
    }
}