<?php
define('CLI_SCRIPT', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot.'/local/coursearchiver/lib.php');
$CFG->debug = DEBUG_DEVELOPER;

course_archiver::run(1800);
