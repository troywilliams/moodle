<?php
    
/**
 * @author David Vega M.
 * @package moodle uow logger
 *
 * Logger Plugin: Centralize place to view past 
 * process and admin reporting 
 *
 * This file is intended to be use as uow-cronjob 
 * to clean stale logs
 *
 * 2008-04-30   File created
 *
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/local/logger/logger.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$logger =& logger_get_logger('Logger', 'Clean logs');

$olderthan = 90;
$deletetime = time() - ($olderthan * 24 * 60 * 60);

//  SQL to get delete info
$sql  = "SELECT MAX(lg.id) AS maxid, COUNT(DISTINCT lg.id) as loggers, COUNT(DISTINCT le.id) AS entries ";
$sql .= "FROM {uow_logger} lg INNER JOIN {uow_log_entries} le ON lg.id = le.loggerid ";
$sql .= "WHERE lg.creationtime < $deletetime";

$deleteinfo = array_pop($DB->get_records_sql($sql));

$logger->fine("Deleting logs older than $olderthan days, i.e anything before ". date('D j, M y g:i:s a', $deletetime));

if ($deleteinfo->maxid != '') {
    //  Delete loggers
    $sql = "id <= $deleteinfo->maxid";
    $deletedloggers = $DB->delete_records_select('uow_logger', $sql);

    //  Delete log entries
    $sql = "loggerid <= $deleteinfo->maxid";
    $deletedentries = $DB->delete_records_select('uow_log_entries', $sql);

    $logger->fine("Logs cleaned, $deleteinfo->loggers loggers and $deleteinfo->entries log entries have been deleted");
}
else {
    $logger->fine('No logs to delete');
}

?>
