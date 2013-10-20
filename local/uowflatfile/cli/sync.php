<?php
define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

// Emulate normal session - we use admin account by default
cron_setup_user();

require_login();
require_capability('moodle/site:config', context_system::instance());

$loggerfile = $CFG->dirroot .'/local/logger/logger.php';
if (file_exists($loggerfile)) {
   require_once($loggerfile);
   $flatfilelogger = new logger('Flatfile', 'Run from local Cronjob');
}

$enrol = enrol_get_plugin('flatfile');
$filelocation = $enrol->get_config('location');
if (!file_exists($filelocation)) {
    $flatfilelogger->error('File not found: '. $filelocation);
} else {
    $trace = new progress_trace_buffer(new text_progress_trace(), true);
    $enrol->sync($trace);
    $log = $trace->get_buffer();
    $flatfilelogger->info(html_writer::tag('pre', $log));
}
