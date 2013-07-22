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
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once('lib.php');

$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 50, PARAM_INT);

admin_externalpage_setup('coursearchiver-queue');

echo $OUTPUT->header();

$pluginconfig = course_archiver::get_config();

$table = new flexible_table('coursearchiver-queue'.uniqid());
$table->define_baseurl('queue.php');
$columns = array('courseid', 'courseshortname', 'process', 'status');
$table->define_columns($columns);
$headers = array('#', get_string('coursename', 'local_coursearchiver'), get_string('process', 'local_coursearchiver'), get_string('status', 'local_coursearchiver'));
$table->define_headers($headers);
$table->set_attribute('class', 'generaltable boxwidthwide');
$table->column_style_all('text-align', 'left');
$table->pageable(true);
$total = $DB->count_records('course_archiver');
$table->pagesize($perpage, $total);

$table->setup();

// Datetime variables
$datetimenow = new DateTime("now");
$systemtimezone = date_default_timezone_get();
// SQL for records get
$sql = "SELECT ca.*, c.shortname
          FROM {course_archiver} ca
          JOIN {course} c 
            ON ca.courseid = c.id";

$records = $DB->get_records_sql($sql, null, $table->get_page_start(), $table->get_page_size());
foreach ($records as $record) {
    $row = array();
    $row[] = $record->id;
    $row[] = $record->shortname;
    $processwhen = get_string('now');
    if ($pluginconfig->graceperiod) {
        $process = DateTime::createFromFormat('U', ($record->timequeued + $pluginconfig->graceperiod), new DateTimeZone($systemtimezone));
        $interval = $datetimenow->diff($process);
        if ($interval->format('%R') == '+') {
            $processwhen = $interval->format('%R%a days');
        }
    }
    $row[] = $processwhen;
    $row[] = ($record->status == 0) ? get_string('status_ok', 'local_coursearchiver') : get_string('status_error', 'local_coursearchiver');
    $table->add_data($row);
}


echo $OUTPUT->heading(get_string('queue','local_coursearchiver'));

echo $table->finish_html();

echo $OUTPUT->footer();