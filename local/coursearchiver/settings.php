<?php

/// Add external admin pages as admin settings suck. 
if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page
    $ADMIN->add('localplugins', new admin_category('local_coursearchiver', get_string('pluginname', 'local_coursearchiver')));
    
    $ADMIN->add('local_coursearchiver', new admin_externalpage('coursearchiver-settings', get_string('settings', 'local_coursearchiver'), 
                $CFG->wwwroot.'/local/coursearchiver/admin/settings.php', 'moodle/backup:backupcourse', false));
    $ADMIN->add('local_coursearchiver', new admin_externalpage('coursearchiver-queue', get_string('queue', 'local_coursearchiver'), 
                $CFG->wwwroot.'/local/coursearchiver/queue.php', 'moodle/backup:backupcourse', false));
}
