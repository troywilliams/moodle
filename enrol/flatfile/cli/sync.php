<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

if (!enrol_is_enabled('flatfile')) {
    error_log('[ENROL flatfile] Plugin not enabled!');
    die;
}

$enrol = enrol_get_plugin('flatfile');
$enrol->sync_enrolments();
mtrace('Done');
