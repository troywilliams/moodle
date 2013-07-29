<?php
if ($hassiteconfig) {
    $temp = new admin_externalpage('edittokenrestrictions', get_string('edittokenrestrictions', 'local_uowedittokenrestriction'), "$CFG->wwwroot/local/uowedittokenrestriction/index.php", 'moodle/site:config');
    $ADMIN->add('webservicesettings', $temp);
    $temp = new admin_externalpage('edittokenrestriction', get_string('edittokenrestriction', 'local_uowedittokenrestriction'), "$CFG->wwwroot/local/uowedittokenrestriction/tokens.php", 'moodle/site:config', true);
    $ADMIN->add('webservicesettings', $temp);
}