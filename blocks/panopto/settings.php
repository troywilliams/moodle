<?php

defined('MOODLE_INTERNAL') || die;

$site = get_site(); // default

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_panopto_instancename',
                                                get_string('instancename', 'block_panopto'),
                                                get_string('instancename_description', 'block_panopto'),
                                                $site->shortname, PARAM_RAW, 40));
    $settings->add(new admin_setting_configtext('block_panopto_servername',
                                                get_string('servername', 'block_panopto'),
                                                get_string('servername_description', 'block_panopto'),
                                                '', PARAM_RAW, 40));
    $settings->add(new admin_setting_configtext('block_panopto_applicationkey',
                                                get_string('applicationkey', 'block_panopto'),
                                                get_string('applicationkey_description', 'block_panopto'),
                                                '', PARAM_RAW, 40));

    //$params->return_url = $CFG->wwwroot.'/admin/settings.php?section=blocksettingpanopto';
    //$query_string = http_build_query($params);
    //$link ='<a href="'.$CFG->wwwroot.'/blocks/panopto/provision_course.php?'.$query_string.'">Add Moodle courses to Panopto CourseCast</a>';
    //$settings->add(new admin_setting_heading('block_panopto_managecourses', '', $link));

}

?>
