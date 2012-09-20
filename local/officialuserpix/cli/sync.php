<?php
define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/local/officialuserpix/lib.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

userpix_import::load_all_user_images();

mtrace('Done');
