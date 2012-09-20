<?php
defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('lib.php');
require_once($CFG->libdir  .'/gdlib.php');
require_once($CFG->dirroot .'/local/logger/logger.php');
// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;
require_login();
$syscontext = context_system::instance();
require_capability('moodle/site:config', $syscontext);
$starttime = time();
$timeelapsed = 0;
$logger =& logger_get_logger('[Official User Pix]', 'Cronjob user pix import');
$logger->info('Starting to load all user images...');
userpix_import::load_all_user_images();
$timeelapsed = time() - $starttime;
$logger->info('Process has completed. Time taken: '.$timeelapsed.' seconds.');
