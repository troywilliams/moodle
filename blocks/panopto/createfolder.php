<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once("lib/panopto_data.php");

$courseid = required_param('courseid', PARAM_INT);
$syscontext = context_system::instance();

require_login($syscontext, false);

$PAGE->set_context($syscontext);
$pageurl = new moodle_url('/blocks/panopto/createfolder.php', array('courseid'=>$courseid));
$PAGE->set_url($pageurl);
$response = false;
$panoptodata = new panopto_data($courseid);
$provisioninginfo = $panoptodata->get_provisioning_info();
$response = $panoptodata->provision_course($provisioninginfo);
$returnurl = new moodle_url('/course/view.php', array('id'=>$courseid));
if (!$response) {
   redirect($returnurl, 'Error: could not create folder on Panopto server!', 1);
} else {
   redirect($returnurl, 'Success, folder and created and linked');
}
exit;
