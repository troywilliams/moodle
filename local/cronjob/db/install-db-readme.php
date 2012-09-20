<?php
/**
 * @author David Vega M.
 * @package moodle uow cronjobs
 *
 * Cronjobs: Run schedule "cronjobs" 
 *
 * This page load the configued XMLDB file into the a database table
 * and create the requied tables for the cronjobs functionality.
 *
 * To use it:
 *   1. Uncomment the file
 *   2. Login to moodle as administrator
 *   3. Navigate to $CFG->dirroot/cronjob/db/install-db-readme.php in your browser
 *   4. Comment the file again
 *   5. Done!
 *
 * 2008-04-16  File created.
 */

//////////////  Uncomment from the line below to...  //////////////

/*
require_once('config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once($CFG->libdir.'/adminlib.php');  // Contains various admin-only functions
require_once($CFG->libdir.'/ddllib.php'); // Install/upgrade related db functions


require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$xmldbfile = $CFG->dirroot.'/cronjob/db/install.xml';

$status = false;

if (file_exists($xmldbfile)) {
    $db->debug = true;
    @set_time_limit(0);  // To allow slow databases to complete the long SQL
    $status = install_from_xmldb_file($xmldbfile); //load the table
    $db->debug = false;
    
    if ($status) {
        notify('The XMLDB file was loaded successfully to the database!');
    }
    else {
        error('The XMLDB file could NOT be set up successfully!');
    }
}
else {
    error('No XMLDB file defined');
}
*/
//////////////  ...to the line above  //////////////
?>
