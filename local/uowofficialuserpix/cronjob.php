<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

require_login();
require_capability('moodle/site:config', context_system::instance());

$uowloggerfile = $CFG->dirroot .'/local/logger/logger.php';
if (file_exists($uowloggerfile)) {
   require_once($uowloggerfile);
   $uowlogger = new logger('UOW Official User Pix', 'Run from local Cronjob');
}
$starttime = time();
$timeelapsed = 0;
if ($uowlogger) $uowlogger->info('Starting to load all user images...');
userpix_import::load_all_user_images();
$timeelapsed = time() - $starttime;
if ($uowlogger) $uowlogger->info('Process has completed. Time taken: '.$timeelapsed.' seconds.');
