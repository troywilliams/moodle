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

require_once $CFG->libdir . '/formslib.php';

class web_service_token_form extends moodleform {

    function definition() {
        global $USER, $DB, $CFG;

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('static', 'token', get_string('token', 'webservice'));
        if ($data['action'] == 'edit') {
            $mform->addElement('static', 'userfullname', get_string('user'));
            $mform->addElement('hidden', 'user');
            $mform->setType('user', PARAM_INT);
        } else {
            
            if (empty($data->nouserselection)) {

                //check if the number of user is reasonable to be displayed in a select box
                $usertotal = $DB->count_records('user',
                        array('deleted' => 0, 'suspended' => 0, 'confirmed' => 1));

                if ($usertotal < 500) {
                    list($sort, $params) = users_order_by_sql('u');
                    //user searchable selector - get all users (admin and guest included)
                    //user must be confirmed, not deleted, not suspended, not guest
                    $sql = "SELECT u.id, u.firstname, u.lastname
                                FROM {user} u
                                WHERE u.deleted = 0 AND u.confirmed = 1 AND u.suspended = 0 AND u.id != :siteguestid
                                ORDER BY $sort";
                    $params['siteguestid'] = $CFG->siteguest;
                    $users = $DB->get_records_sql($sql, $params);
                    $options = array();
                    foreach ($users as $userid => $user) {
                        $options[$userid] = fullname($user);
                    }
                    $mform->addElement('searchableselector', 'user', get_string('user'), $options);
                    $mform->setType('user', PARAM_INT);
                } else {
                    //simple text box for username or user id (if two username exists, a form error is displayed)
                    $mform->addElement('text', 'user', get_string('usernameorid', 'webservice'));
                    $mform->setType('user', PARAM_RAW_TRIMMED);
                }
                $mform->addRule('user', get_string('required'), 'required', null, 'client');
            }
        }
        if ($data['action'] == 'edit'){
            $mform->addElement('static', 'servicename', get_string('servicename', 'webservice'));
        } else {
            //service selector
            $services = $DB->get_records('external_services');
            $options = array();
            $systemcontext = context_system::instance();
            foreach ($services as $serviceid => $service) {
                //check that the user has the required capability
                //(only for generation by the profile page)
                if (empty($data->nouserselection)
                        || empty($service->requiredcapability)
                        || has_capability($service->requiredcapability, $systemcontext, $USER->id)) {
                    $options[$serviceid] = $service->name;
                }
            }
            $mform->addElement('select', 'service', get_string('service', 'webservice'), $options);
            $mform->addRule('service', get_string('required'), 'required', null, 'client');
            $mform->setType('service', PARAM_INT);
        }
        $mform->addElement('text', 'iprestriction', get_string('iprestriction', 'webservice'));
        $mform->setType('iprestriction', PARAM_RAW_TRIMMED);

        $mform->addElement('date_selector', 'validuntil',
                get_string('validuntil', 'webservice'), array('optional' => true));
        $mform->setType('validuntil', PARAM_INT);
 
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $this->add_action_buttons(true);

        $this->set_data($data);
    }

    function get_data() {
        global $DB;
        $data = parent::get_data();

        if (!empty($data) && !is_numeric($data->user)) {
            //retrieve username
            $user = $DB->get_record('user', array('username' => $data->user), 'id');
            $data->user = $user->id;
        }
        return $data;
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        if (is_numeric($data['user'])) {
            $searchtype = 'id';
        } else {
            $searchtype = 'username';
            //check the username is valid
            if (clean_param($data['user'], PARAM_USERNAME) != $data['user']) {
                $errors['user'] = get_string('invalidusername');
            }
        }

        if (!isset($errors['user'])) {
            $users = $DB->get_records('user', array($searchtype => $data['user']), '', 'id');

            //check that the user exists in the database
            if (count($users) == 0) {
                $errors['user'] = get_string('usernameoridnousererror', 'webservice');
            } else if (count($users) > 1) { //can only be a username search as id are unique
                $errors['user'] = get_string('usernameoridoccurenceerror', 'webservice');
            }
        }

        return $errors;
    }

}