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

require_once(dirname(__FILE__) . '/../../config.php');
require_once("$CFG->dirroot/enrol/meta/locallib.php");

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/enrol/meta/search.php');

header('Content-type: application/json; charset=utf-8');

// Check access.
if (!isloggedin()) {
    print_error('mustbeloggedin');
}
if (!confirm_sesskey()) {
    print_error('invalidsesskey');
}
$id         = required_param('id', PARAM_INT);// course id
$searchtext = required_param('searchtext', PARAM_RAW);// Get the search parameter.

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

// $context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);

$enrol = enrol_get_plugin('meta');
$limit = $enrol->get_config('addmultiple_rowlimit', 0); // row limit

$filtered = array();

$result = enrol_meta_multiple_search($course->id, $searchtext, true, $limit);
if ($result->found > $result->limit) {
    if ($searchtext) {
        $a = new stdClass;
        $a->matches = $result->found;
        $a->search = $searchtext;
        $label = get_string('toomanycoursesmatchsearch', 'enrol_meta', $a);
     } else {
        $label = get_string('toomanycoursestoshow', 'enrol_meta', $result->found);
     }
} else {
    foreach ($result->courses as $c) {
        $c->fullname = shorten_text(format_string($c->fullname), 80, true);
        unset($c->ctxid);
        unset($c->ctxpath);
        unset($c->ctxdepth);
        unset($c->ctxinstance);
        $filtered[$c->id] = $c;
    }
    $label = get_string('coursesmatchingsearch', 'enrol_meta', $result->found);
}
echo json_encode(array('label'=>$label, 'matches'=>$filtered));

