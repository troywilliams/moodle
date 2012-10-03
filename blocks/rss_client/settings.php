<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_rss_client_num_entries', get_string('numentries', 'block_rss_client'),
                       get_string('clientnumentries', 'block_rss_client'), 5, PARAM_INT));

    $settings->add(new admin_setting_configtext('block_rss_client_timeout', get_string('timeout2', 'block_rss_client'),
                       get_string('timeout', 'block_rss_client'), 30, PARAM_INT));

    $settings->add(new admin_setting_configselect('block_rss_client_cacheduration', get_string('cacheduration', 'block_rss_client'),
                       get_string('cacheduration_description', 'block_rss_client'), 3600,
                       array(0=>'None', 1800=>'half an hour',3600=>'1 hour',10800=>'3 hours',43200=>'12 hours',86400=>'1 day')));

    $link ='<a href="'.$CFG->wwwroot.'/blocks/rss_client/managefeeds.php">'.get_string('feedsaddedit', 'block_rss_client').'</a>';
    $settings->add(new admin_setting_heading('block_rss_addheading', '', $link));
}