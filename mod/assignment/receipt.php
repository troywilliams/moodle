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

/**
 * UOW assignment receipt implementation
 *
 * @package   mod-assignment
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
    require_once("../../config.php");
    require_once("lib.php");
 
    $id  = required_param('id', PARAM_INT);
    $submissionid  = required_param('submission', PARAM_INT);   // Assignment Submission Id
    
    
    if (! $cm = get_coursemodule_from_id('assignment', $id)) {
        print_error("Course Module ID was incorrect");
    }

    if (! $assignment = $DB->get_record("assignment", array("id"=>$cm->instance))) {
        print_error("assignment ID was incorrect");
    }

    if (! $course = $DB->get_record("course", array("id"=>$assignment->course))) {
        print_error("Course is misconfigured");
    }
    
    if (!$submission = $DB->get_record('assignment_submissions', array('id'=>$submissionid))) {
        print_error('Submission not found');
    }

    require_login($course, true, $cm);
    
    if ($submission->userid != $USER->id && !has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        error('Permission Denied');
    }

    $strassignments = get_string("modulenameplural", "assignment");

    $PAGE->set_url('/mod/assignment/receipt.php', array('id'=>$course->id, 'submission'=>$submissionid));
    $PAGE->navbar->add($strassignments);
    $PAGE->set_title($strassignments);
    $PAGE->set_heading($course->fullname);

    /// Load up the required assignment code
    require($CFG->dirroot.'/mod/assignment/type/'.$assignment->assignmenttype.'/assignment.class.php');
    $assignmentclass = 'assignment_'.$assignment->assignmenttype;
    $assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);

    $assignmentinstance->view_header();
    echo ''.get_string('assignmentsubmissionreceipt', 'assignment').'';
    echo $OUTPUT->box_start();
    echo $assignmentinstance->get_coversheet_html($submission);
    echo $OUTPUT->box_end();
    echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id'=>$course->id)));
    $assignmentinstance->view_footer();
    
    add_to_log($course->id, 'assignment', 'view receipt', 'receipt.php?id='.$cm->id.'&amp;submission='.$submission->id, $cm->instance, $cm->id);

   
?>