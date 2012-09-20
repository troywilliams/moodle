<?php
define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/local/userimport/lib.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

user_import_users();

mtrace('Done');
