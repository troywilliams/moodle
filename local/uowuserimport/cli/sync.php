<?php
define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/local/uowuserimport/lib.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

$ui = new local_uowuserimport();

$uowloggerfile = $CFG->dirroot .'/local/logger/logger.php';
if (file_exists($uowloggerfile)) {
   require_once($uowloggerfile);
   $uowlogger = new logger('UOW User import', 'Manual Sync');
   $ui->add_uow_logger($uowlogger);
}

$ui->run();
