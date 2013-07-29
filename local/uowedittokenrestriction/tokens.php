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

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/uowedittokenrestriction/forms.php');
require_once($CFG->libdir . '/externallib.php');

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$tokenid = optional_param('tokenid', '', PARAM_SAFEDIR);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

admin_externalpage_setup('edittokenrestriction');


require_capability('moodle/site:config', context_system::instance());

$tokenlisturl = new moodle_url('index.php');

require_once($CFG->dirroot . "/webservice/lib.php");
$webservicemanager = new webservice();

switch ($action) {

    case 'create':
        $mform = new web_service_token_form(null, array('action' => 'create'));
        $data = $mform->get_data();
        if ($mform->is_cancelled()) {
            redirect($tokenlisturl);
        } else if ($data and confirm_sesskey()) {
            ignore_user_abort(true);

            //check the the user is allowed for the service
            $selectedservice = $webservicemanager->get_external_service_by_id($data->service);
            if ($selectedservice->restrictedusers) {
                $restricteduser = $webservicemanager->get_ws_authorised_user($data->service, $data->user);
                if (empty($restricteduser)) {
                    $allowuserurl = new moodle_url('/' . $CFG->admin . '/webservice/service_users.php',
                            array('id' => $selectedservice->id));
                    $allowuserlink = html_writer::tag('a', $selectedservice->name , array('href' => $allowuserurl));
                    $errormsg = $OUTPUT->notification(get_string('usernotallowed', 'webservice', $allowuserlink));
                }
            }

            //check if the user is deleted. unconfirmed, suspended or guest
            $user = $DB->get_record('user', array('id' => $data->user));
            if ($user->id == $CFG->siteguest or $user->deleted or !$user->confirmed or $user->suspended) {
                throw new moodle_exception('forbiddenwsuser', 'webservice');
            }

            //process the creation
            if (empty($errormsg)) {
                //TODO improvement: either move this function from externallib.php to webservice/lib.php
                // either move most of webservicelib.php functions into externallib.php
                // (create externalmanager class) MDL-23523
                external_generate_token(EXTERNAL_TOKEN_PERMANENT, $data->service,
                        $data->user, context_system::instance(),
                        $data->validuntil, $data->iprestriction);
                redirect($tokenlisturl);
            }
        }

        //OUTPUT: create token form
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('createtoken', 'webservice'));
        if (!empty($errormsg)) {
            echo $errormsg;
        }
        $mform->display();
        echo $OUTPUT->footer();
        die;
        break;
    case 'edit':
        $mform = new web_service_token_form(null, array('action' => 'edit'));
        $data = $mform->get_data();
        if ($mform->is_cancelled()) {
            redirect($tokenlisturl);
        } else if ($data and confirm_sesskey()) {
            ignore_user_abort(true);
            unset($data->action);
            unset($data->submitbutton);
            // Update
            if (!$DB->update_record('external_tokens', $data)) {
                print_error('failed to update token');
            }
            redirect($tokenlisturl);
        }
        // Load form
        if (empty($tokenid)) {
            print_error('missing tokenid');
            break;
        }
        $token = $DB->get_record('external_tokens', array('id'=>$tokenid));
        if (!$token) {
            print_error('no token found with tokenid: '.$tokenid);
            break;
        }
        $user = $DB->get_record('user', array('id'=>$token->userid));
        if (!$user) {
            print_error('no user found for tokenid: '.$tokenid);
            break;
        }
        $servicename = $DB->get_field('external_services', 'name', array('id'=>$token->externalserviceid));
        if (!$servicename) {
            print_error('no servicename found');
            break;
        }
        $editdata = new stdClass;
        $editdata->id = $token->id;
        $editdata->token = $token->token;
        $editdata->user = $user->id;
        $editdata->userfullname = fullname($user);
        $editdata->servicename = $servicename;
        $editdata->iprestriction = $token->iprestriction;
        $editdata->validuntil = $token->validuntil;
        $mform->set_data((array)$editdata);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('edittokenrestriction', 'local_uowedittokenrestriction'));
        if (!empty($errormsg)) {
            echo $errormsg;
        }
        $mform->display();
        echo $OUTPUT->footer();
        die;
        break;
        
    case 'delete':
        $token = $webservicemanager->get_created_by_user_ws_token($USER->id, $tokenid);

        //Delete the token
        if ($confirm and confirm_sesskey()) {
            $webservicemanager->delete_user_ws_token($token->id);
            redirect($tokenlisturl);
        }

        ////OUTPUT: display delete token confirmation box
        echo $OUTPUT->header();
        $renderer = $PAGE->get_renderer('core', 'webservice');
        echo $renderer->admin_delete_token_confirmation($token);
        echo $OUTPUT->footer();
        die;
        break;

    default:
        //wrong url access
        redirect($tokenlisturl);
        break;
}
