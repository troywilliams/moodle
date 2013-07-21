<?php
define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

// Emulate normal session - we use admin account by default
cron_setup_user();

$uowloggerfile = $CFG->dirroot .'/local/logger/logger.php';
if (file_exists($uowloggerfile)) {
   require_once($uowloggerfile);
   $uowlogger = new logger('IMS Enterprise', 'Manual Sync');
}
$enrol = enrol_get_plugin('imsenterprise');
$imsfilelocation = $enrol->get_config('imsfilelocation');
if (!file_exists($imsfilelocation)) {
    $uowlogger->error('File not found: '.$imsfilelocation);
} else {
    $enrol->cron(false); // use custom disabled parameter to get cron going.
    $processlog = $enrol->get_config('logtolocation');
    if (file_exists($processlog)) {
        $contents = file_get_contents($processlog);
        $uowlogger->info(html_writer::tag('pre', $contents));
        unlink($processlog);
    } else {
        $uowlogger->error('You need to setup IMS Enterprise logtolocation parameter there Fruity!');
    }
}
mtrace('Fin!');
