<?php

    require_once('../../config.php');
    require_once('lib.php');
    require_once('verifyfile-form.php');
    
    $id = required_param('id', PARAM_INT);  // Course Module ID

    if (! $cm = get_coursemodule_from_id('assignment', $id)) {
        print_error("Course Module ID was incorrect");
    }

    if (! $assignment = $DB->get_record("assignment", array('id'=>$cm->instance))) {
        print_error("assignment ID was incorrect");
    }

    if (! $course = $DB->get_record("course", array('id'=>$assignment->course))) {
        print_error("Course is misconfigured");
    }

    require_login($course, true, $cm);
    
    require_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id));

    $strassignments = get_string("modulenameplural", "assignment");

    $PAGE->set_url('/mod/assignment/verifyfile.php', array('id'=>$course->id));
    $PAGE->navbar->add($strassignments);
    $PAGE->set_title($strassignments);
    $PAGE->set_heading($course->fullname);


    /// Load up the required assignment code
    require($CFG->dirroot.'/mod/assignment/type/'.$assignment->assignmenttype.'/assignment.class.php');
    $assignmentclass = 'assignment_'.$assignment->assignmenttype;
    $assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);

    $assignmentinstance->view_header();
    
    $mform = new assignment_verifyfile_form(null, array('id' => $id));
    
    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot.'/mod/assignment/view.php?id='.$cm->id);
    } elseif ($data = $mform->get_data(false)) {
        $parts = explode('-',$data->receipt);
        $date = '';
        foreach ($parts as $part) {
            $date = $part[4].$date;
        }
        $fs = get_file_storage();
        $usercontext = get_context_instance(CONTEXT_USER, $USER->id);
        $draftitemid = file_get_submitted_draft_itemid('attachment');
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id');
        foreach ($files as $file) {
            if ($file->get_filename() == '.'){
                continue;
            }
            if ($data->receipt === $assignmentinstance->get_coversheet_file_receipt($file->get_contenthash(), strtotime($date))) {
                $message = get_string('validationsuccess', 'assignment');
            } else {

                $message = get_string('validationfailed', 'assignment');
            }
            $buttoncontinue = new single_button(new moodle_url('/mod/assignment/verifyfile.php', array('id'=>$cm->id)), get_string('yes'));
            $buttoncancel   = new single_button(new moodle_url('/mod/assignment/view.php', array('id'=>$cm->id)), get_string('no'));
            echo $OUTPUT->confirm($message, $buttoncontinue, $buttoncancel);
        }
    } else {
        $mform->display();
    }
    $assignmentinstance->view_footer();

?>