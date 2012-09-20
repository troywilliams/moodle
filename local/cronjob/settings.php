<?php

if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page
    $ADMIN->add('localplugins', new admin_externalpage('uowcronjobconfig', 'UoW Cron Jobs', "$CFG->wwwroot/local/cronjob/edit.php"));
}