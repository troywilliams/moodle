<?php
    //  UOW: course format changer report
if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page
    $temp = new admin_externalpage('uowcourseformatchanger', 'UoW Course format changer', $CFG->wwwroot.'/local/courseformatchanger/course-format.php');
    $ADMIN->add('localplugins', $temp);
}
