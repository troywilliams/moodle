<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


define('CLI_SCRIPT', true);
require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');         // cli only functions
@error_reporting(1023);
@ini_set('display_errors', '1');
$CFG->debug = 38911;
$CFG->debugdisplay = true;
/// no limit
set_time_limit(0);
/// increase memory limit
raise_memory_limit(MEMORY_EXTRA);

// now get cli options
list($options, $unrecognized) = cli_get_params(
    array(
        'help'=>false
    ),
    array(
        'h' => 'help'
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"
This script allows you to re-send files for a particalur course module to Turnitin that
have a error status code.

Please note you must execute this script with the same uid as apache!

Options:
--cm
-h, --help            Print out this help

Example:
\$sudo -u apache /usr/bin/php plagiarism/turnitin/cli/send_files_to_turnitin.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

$prompt = 'Enter a course module identifier';
$cmid = cli_input($prompt);
if (! is_numeric($cmid)){
    exit("Must be an integer!\n");
}
$params = array('cmid'=>$cmid);
$modulename = $DB->get_field_sql("SELECT md.name
                                    FROM {modules} md
                                    JOIN {course_modules} cm ON cm.module = md.id
                                   WHERE cm.id = :cmid", $params);
if (!$modulename) {
    exit("Missing! Is the cmid correct?\n");
}

$params['modulename'] = $modulename;
$sql = "SELECT c.shortname AS coursename, cm.id, m.name, md.name AS modname
              FROM {course_modules} cm
                   JOIN {course} c ON c.id = cm.course
                   JOIN {modules} md ON md.id = cm.module
                   JOIN {".$modulename."} m ON m.id = cm.instance
             WHERE cm.id = :cmid AND md.name = :modulename";
$record = $DB->get_record_sql($sql, $params);
if (!$record) {
    exit("Missing! Is the cmid correct?\n");
}

mtrace(' * '.$record->name.' in '.$record->coursename);
$prompt = 'Is this correct course module, would you like to proceed? type y (means yes) or n (means no)';
$input = cli_input($prompt, '', array('n', 'y'));
if ($input == 'n') {
    exit();
}

$tiisuccessstates = array(
'success',
'1',
'10',
'11',
'20',
'21',
'30',
'31',
'40',
'41',
'42',
'43',
'50',
'51',
'60',
'61',
'70',
'71',
'72',
'73',
'74',
'75'
);
list($tiisql, $tiiparams) = $DB->get_in_or_equal($tiisuccessstates, SQL_PARAMS_NAMED, 'tiiss', false);
$sql = "SELECT DISTINCT tf.statuscode
          FROM {plagiarism_turnitin_files} tf
         WHERE tf.cm = :cmid
           AND tf.statuscode $tiisql
      ORDER BY tf.statuscode DESC";
$params = $params + $tiiparams;
$availablecodes = $DB->get_records_sql($sql, $params);

if (!$availablecodes) {
    exit("No errored files\n");
}

$prompt = 'Errored state codes in this course module:  '.implode(', ',array_keys($availablecodes))."\n";
$prompt .= 'Enter code';
$statuscode = cli_input($prompt, '', array_keys($availablecodes));
$params['statuscode'] = $statuscode;

    $prompt = 'Ready to send files. Would you like to proceed? type y (means yes) or n (means no)';
    $input = cli_input($prompt, '', array('n', 'y'));
    if ($input == 'n') {
        exit(1);
    } 
    if ($input == 'y') {
        require_once($CFG->libdir.'/plagiarismlib.php');
        require_once($CFG->dirroot.'/plagiarism/turnitin/lib.php');
        require_once("$CFG->libdir/filelib.php"); //HACK to include filelib so that when event cron is run then file_storage class is available
        require_once("$CFG->dirroot/mod/assignment/lib.php"); //HACK to include filelib so that when event cron is run then file_storage class is available
        
        $plagiarismplugin = new plagiarism_plugin_turnitin();
        $plagiarismsettings = $plagiarismplugin->get_settings();
        
        $sql = "SELECT tf.*
                FROM {plagiarism_turnitin_files} tf, {course_modules} cm
                WHERE tf.cm = cm.id 
                AND tf.statuscode = :statuscode
                AND tf.cm = :cmid";
        $items = $DB->get_records_sql($sql, $params);
        foreach ($items as $item) {
            $fs = get_file_storage();
            $file = $fs->get_file_by_hash($item->identifier);
            if ($file) {
                //$pid = plagiarism_update_record($item->cm, $item->userid, $file->get_pathnamehash(), $item->attempt+1);
                $plagiarism_file = $DB->get_record('plagiarism_turnitin_files',
                        array('cm' => $item->cm, 'userid' => $item->userid, 'identifier' => $file->get_pathnamehash()));
                
                $pid = $plagiarism_file->id;
                if (!empty($pid)) {
                    mtrace('sending file '.$file->get_filename().' '.$item->userid);
                    turnitin_send_file($pid, $plagiarismsettings, $file);
                }
            } else {
                debugging('file resubmit attempted but file not found id: '.$item->id, DEBUG_DEVELOPER);
                $DB->delete_records('plagiarism_turnitin_files', array('id'=>$item->id));
            }
         }
       
              
    }
mtrace("Done!");
exit(0); // 0 means success
