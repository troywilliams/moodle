<?php
defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot .'/local/logger/logger.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;
require_login();
$syscontext = context_system::instance();
require_capability('moodle/site:config', $syscontext);
$logger =& logger_get_logger('[ENROL IMS Enterprise]', 'Cronjob course automation');
$logger->info('calling sync enrolments function...');
$enrol = enrol_get_plugin('imsenterprise');
$enrol->sync_enrolments();
$logger->info('IMS Enterprise log: <br />'. str_replace("\n",'<br />', $enrol->log));
