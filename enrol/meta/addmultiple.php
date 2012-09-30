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

require('../../config.php');
require_once("$CFG->dirroot/enrol/meta/addmultiple_form.php");
require_once("$CFG->dirroot/enrol/meta/locallib.php");

$id = required_param('id', PARAM_INT); // course id
$searchtext = optional_param('links_searchtext', '', PARAM_RAW);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);

$pageurl = new moodle_url('/enrol/meta/addmultiple.php');
$pageurl->param('id', $course->id);

$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');

navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));

require_login($course);
require_capability('moodle/course:enrolconfig', $context);

if (optional_param('links_clearbutton', 0, PARAM_RAW) && confirm_sesskey()) {
    redirect($pageurl);
}
$enrol = enrol_get_plugin('meta');
$limit = $enrol->get_config('addmultiple_rowlimit', 0);

$result = enrol_meta_multiple_search($course->id, $searchtext, true, $limit);
if ($result->found > $result->limit) {
    if ($searchtext) {
        $a = new stdClass;
        $a->matches = $result->found;
        $a->search = $searchtext;
        $courses = array(get_string('toomanycoursesmatchsearch', 'enrol_meta', $a) => array(''),
                         get_string('pleasesearchmore') => array(''));
     } else {
        $courses = array(get_string('toomanycoursestoshow', 'enrol_meta', $result->found) => array(''),
                         get_string('pleaseusesearch') => array(''));
     }
} else {
    $filtered = array();
    foreach ($result->courses as $c) {
        $filtered[$c->id] = format_string($c->fullname) . ' ['.$c->shortname.']';
    }
    $courses = array(get_string('coursesmatchingsearch', 'enrol_meta', $result->found) => $filtered);
}
if (!$enrol->get_newinstance_link($course->id)) {
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id, '')));
}

$mform = new enrol_meta_addmultiple_form($pageurl->out(false), array('course'=>$course,'courses'=>$courses));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
} else if ($data = $mform->get_data()) {
    if (!empty($data->links)) {
        foreach ($data->links as $link) {
            if (!empty($link)) { // because of formlib selectgroups
                $eid = $enrol->add_instance($course, array('customint1'=>$link));
            }
        }
        enrol_meta_sync($course->id);
    }
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
}
$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_meta'));
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();

