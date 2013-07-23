<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/local/uowuserimport/lib.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

require_login();
require_capability('moodle/site:config', context_system::instance());

$ui = new local_uowuserimport();

$uowloggerfile = $CFG->dirroot .'/local/logger/logger.php';
if (file_exists($uowloggerfile)) {
   require_once($uowloggerfile);
   $uowlogger = new logger('UOW User import', 'Run from local Cronjob');
   $ui->add_uow_logger($uowlogger);
}

$ui->run();