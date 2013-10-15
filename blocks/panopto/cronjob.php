<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/panopto/block_panopto.php');

// Ensure errors are well explained
// $CFG->debug = DEBUG_NORMAL;

require_login();
require_capability('moodle/site:config', context_system::instance());

$block = new block_panopto();

$uowloggerfile = $CFG->dirroot .'/local/logger/logger.php';
if (file_exists($uowloggerfile)) {
   require_once($uowloggerfile);
   $uowlogger = new logger('Panopto', 'Users to folder sync');
   $uowlogger->info('Processing...');
   $block->sync_users();
   $uowlogger->info('Done.');
}
