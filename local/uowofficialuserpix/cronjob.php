<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/local/uowofficialuserpix/lib.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

require_login();
require_capability('moodle/site:config', context_system::instance());

$uowloggerfile = $CFG->dirroot .'/local/logger/logger.php';
if (file_exists($uowloggerfile)) {
   require_once($uowloggerfile);
}
userpix_import::load_all_user_images($uowlogger);
