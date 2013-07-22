<?php

if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page
    $ADMIN->add('localplugins', new admin_externalpage('uowcronjobconfig', get_string('pluginname', 'local_cronjob'), "$CFG->wwwroot/local/cronjob/edit.php"));
}