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

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/enrol/meta/search.php');

// In developer debug mode, when there is a debug=1 in the URL send as plain text
// for easier debugging.
if (debugging('', DEBUG_DEVELOPER) && optional_param('debug', false, PARAM_BOOL)) {
    header('Content-type: text/plain; charset=UTF-8');
    $debugmode = true;
} else {
    header('Content-type: application/json; charset=utf-8');
    $debugmode = false;
}

// Check access.
if (!isloggedin()) {;
    print_error('mustbeloggedin');
}
if (!confirm_sesskey()) {
    print_error('invalidsesskey');
}

$id         = required_param('id', PARAM_INT);// course id
$searchtext = required_param('searchtext', PARAM_RAW);// Get the search parameter.

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);
// row limit unlimited if not set in config
$rowlimit = empty($CFG->enrol_meta_addmultiple_rowlimit) ?  0 : (int) $CFG->enrol_meta_addmultiple_rowlimit;
$existing = $DB->get_records('enrol', array('enrol'=>'meta', 'courseid'=>$id), '', 'customint1, id');
if (!empty($searchtext)) {
    $searchparam = '%' . $searchtext . '%';
    $select = $DB->sql_like('shortname', '?', false, false);
    $rs = $DB->get_recordset_select('course', $select, array($searchparam), 'shortname ASC', 'id, shortname, fullname, visible', 0, $rowlimit);
} else {
    $rs = $DB->get_recordset('course', null, 'shortname ASC', 'id, shortname, fullname, visible', 0, $rowlimit);
}
foreach ($rs as $c) {
    if ($c->id == SITEID or $c->id == $course->id or isset($existing[$c->id])) {
        continue;
    }
    $coursecontext = get_context_instance(CONTEXT_COURSE, $c->id);
    if (!$c->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
        continue;
    }
    if (!has_capability('enrol/meta:selectaslinked', $coursecontext)) {
        continue;
    }
    $c->fullname = shorten_text(format_string($c->fullname), 80, true);
    $results[$c->id] = $c;
}
$rs->close();
echo json_encode(array('results'=>$results));

