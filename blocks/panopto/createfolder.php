<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once("lib/panopto_data.php");

$courseid = required_param('courseid', PARAM_INT);
$confirm  = optional_param('confirm', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid));
if (! $course) {
    print_error('coursemisconf');
}

$context = context_course::instance($courseid);

require_login($courseid, false);

require_capability('block/panopto:provision_course', $context);

$pageurl = new moodle_url('/blocks/panopto/createfolder.php', array('courseid' => $course->id));

$PAGE->set_url($pageurl);

if (!empty($confirm) && confirm_sesskey()) {
    $panoptodata = new panopto_data($courseid);
    $provisioninginfo = $panoptodata->get_provisioning_data();
    $response = $panoptodata->provision_folder($provisioninginfo);
    $returnurl = new moodle_url('/course/view.php', array('id'=>$courseid));
    if (!$response) {
       redirect($returnurl, 'Error: could not create folder on Panopto server!', 1);
    } else {
       redirect($returnurl, 'Success, folder and created and linked');
    }
}
echo $OUTPUT->header();
$pageurl->param('confirm', $course->id);
$returnurl = new moodle_url('/course/view.php', array('id' => $course->id));
echo $OUTPUT->confirm('Create Panopto folder for '.$course->shortname,
                       $pageurl, $returnurl);
echo $OUTPUT->footer();
exit;
