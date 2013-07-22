<?php
define('CLI_SCRIPT', true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');         // cli only functions
require_once($CFG->dirroot . '/local/coursearchiver/lib.php');

$CFG->debug = DEBUG_DEVELOPER;

$config = course_archiver::get_config();

$countsql = <<<'EOCS'
SELECT COUNT(1)
  FROM {course} c
 WHERE c.shortname ~* '^\\w{4,}-(07|08|09|10)\\w{1,}\\s{1,}'
EOCS;

$count = $DB->count_records_sql($countsql);

$sql = <<<'EOS'
SELECT c.id, c.shortname 
  FROM {course} c 
 WHERE c.shortname ~* '^\\w{4,}-(07|08|09|10)\\w{1,}\\s{1,}'
EOS;

if ($count) {
    $prompt = 'Found '.$count.' records, would you like to proceed? type y (means yes) or n (means no)';
    $input = cli_input($prompt, '', array('n', 'y'));
    if ($input == 'n') {
        exit();
    }
    $rs = $DB->get_recordset_sql($sql);
    foreach ($rs as $course) {
        // Do whatever you want with this record
        $course->category = $config->archivecategory;
        $DB->update_record('course', $course);
        mtrace('Moved '.$course->shortname.' to archiver category');
    }
    $rs->close();
} else {
    mtrace('No matches');
}
fix_course_sortorder();
mtrace('Done');
