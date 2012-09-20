<?php

if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page
    $ADMIN->add('localplugins', new admin_externalpage('uowlogsconfig', 'UoW Logs', "$CFG->wwwroot/local/logger/index.php"));
}
