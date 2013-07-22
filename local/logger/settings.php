<?php

if ($hassiteconfig) { // speedup for non-admins, add all caps used on this page
    $ADMIN->add('localplugins', new admin_externalpage('uowlogsconfig', get_string('pluginname', 'local_logger'), "$CFG->wwwroot/local/logger/index.php"));
}
