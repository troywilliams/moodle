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

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/coursearchiver/admin/forms.php');

admin_externalpage_setup('coursearchiver-settings');

$form = new coursearchiver_settings_form();
$data = $form->get_data();
echo $OUTPUT->header();
if ($data) {
    set_config('enabled', $data->enabled, 'local_coursearchiver');
    set_config('mbzstoredirectory', $data->mbzstoredirectory, 'local_coursearchiver');
    set_config('archivecategory', $data->archivecategory, 'local_coursearchiver');
    set_config('graceperiod', $data->graceperiod * 60 * 60 * 24, 'local_coursearchiver');
    set_config('processingtime', $data->processingtime, 'local_coursearchiver');
    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
}
$form->display();
echo $OUTPUT->footer();
die();
