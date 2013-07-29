<?php
defined('MOODLE_INTERNAL') || die;
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_new_titles_rssuserid', get_string('rssuserid', 'block_new_titles'),
                   get_string('userid', 'block_new_titles'), 0, PARAM_INT));
    
    $settings->add(new admin_setting_configselect('block_new_titles_cacheduration', get_string('cacheduration', 'block_new_titles'),
                   get_string('cacheduration_description', 'block_new_titles'), 3600, array(1800=>'half an hour',3600=>'1 hour',10800=>'3 hours',43200=>'12 hours',86400=>'1 day')));
    
    $link ='<a href="'.$CFG->wwwroot.'/blocks/new_titles/managefeeds.php">'.get_string('feedsaddedit', 'block_new_titles').'</a>';
    $settings->add(new admin_setting_heading('block_new_titles_addheading', '', $link));
}

