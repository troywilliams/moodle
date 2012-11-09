<?php

define('CLI_SCRIPT', true);
require(dirname(dirname(dirname(__FILE__))).'/config.php');

require_once($CFG->dirroot.'/lib/clilib.php'); // cli only functions
//require_once($CFG->dirroot.'/lib/cronlib.php');
//require_once($CFG->dirroot.'/lib/eventslib.php');
require_once($CFG->dirroot.'/lib/filelib.php');

// suppress
$CFG->debug = DEBUG_NONE;
$CFG->debugdisplay = false;
// increase time limit
set_time_limit(0);
/// increase memory limit
raise_memory_limit(MEMORY_EXTRA);
/// Start output log
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
"TODO

Options:
-h, --help            Print out this help

Example:
\$sudo -u apache /usr/bin/php admin/cli/post22upgrade.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

mtrace('** Checking for old uow_cronjob paths not pointing to cronjob.php **');
$cronjobs = $DB->get_records('uow_cronjob');
if (!$cronjobs) {
    mtrace('Terrible failure! No cronjob records');
    die;
} else {
    foreach ($cronjobs as $cronjob) {
        if (stripos($cronjob->filepath, 'cronjob.php') === false) {
            mtrace('Found...'.$cronjob->name);
            $dir = dirname($cronjob->filepath);
            $cronjob->filepath = $dir.'/cronjob.php';
            $DB->update_record('uow_cronjob', $cronjob);
            mtrace('... updated!');
        }
    }
}
mtrace('** Checking for old aspell_data cache files **');
$dir = $CFG->dataroot;
$listing = scandir($dir);
if (!$listing) {
    mtrace('Terrible failure!');
    die;
} else {
    foreach($listing as $filename) {
        $path = $dir.'/'.$filename;
        if (stripos($filename, 'aspell_data_') !== false and (is_file($path))) {
            $prompt = 'Found old aspell cache file:'.$path.'? type y (means yes) or n (means no) q (means quit) ';
            $input = cli_input($prompt, '', array('n','y','q'));
            switch ($input) {
                case 'q':
                    die;
                case 'n':
                    mtrace('...  skipped '.$path);
                    break;
                case 'y':
                    @unlink($path);
                    mtrace('... removed '.$path);
            }        
        }
    }
}
mtrace('** Detect old legacy _user_ directory **');
$dir = $CFG->dataroot.'/_user_';
if (!is_dir($dir)) {
    mtrace('... does not exist!');
} else {
    $prompt = 'Found '.$dir.'? type y (means yes) or n (means no) q (means quit) ';
    $input = cli_input($prompt, '', array('n','y','q'));
    switch ($input) {
        case 'q':
            die;
        case 'n':
            mtrace('...  skipped ...');
            break;
        case 'y':
            fulldelete($dir);
            mtrace('... removed');
    }
}
mtrace('** Detect old legacy imports directory **');
$dir = $CFG->dataroot.'/imports';
if (!is_dir($dir)) {
    mtrace('... does not exist!');
} else {
    $prompt = 'Found '.$dir.'? type y (means yes) or n (means no) q (means quit) ';
    $input = cli_input($prompt, '', array('n','y','q'));
    switch ($input) {
        case 'q':
            die;
        case 'n':
            mtrace('...  skipped ...');
            break;
        case 'y':
            fulldelete($dir);
            mtrace('... removed');
    }
}
mtrace('** Detect oldlang directory **');
$dir = $CFG->dataroot.'/oldlang';
if (!is_dir($dir)) {
    mtrace('... does not exist!');
} else {
    $prompt = 'Found '.$dir.'? type y (means yes) or n (means no) q (means quit) ';
    $input = cli_input($prompt, '', array('n','y','q'));
    switch ($input) {
        case 'q':
            die;
        case 'n':
            mtrace('...  skipped ...');
            break;
        case 'y':
            fulldelete($dir);
            mtrace('... removed');
    }        
}
mtrace('** Detect old 1.9 Blog cache directory **');
$dir = $CFG->dataroot.'/blog';
if (!is_dir($dir)) {
    mtrace('... does not exist!');
} else {
    $prompt = 'Found '.$dir.'? type y (means yes) or n (means no) q (means quit) ';
    $input = cli_input($prompt, '', array('n','y','q'));
    switch ($input) {
        case 'q':
            die;
        case 'n':
            mtrace('...  skipped ...');
            break;
        case 'y':
            fulldelete($dir);
            mtrace('... removed');
    }        
}
mtrace('** Detect old 1.9 RSS cache directories **');
$dir = $CFG->dataroot.'/rss';
if (!is_dir($dir)) {
    mtrace('... does not exist!');
} else {
    $prompt = 'Found '.$dir.'? type y (means yes) or n (means no) q (means quit) ';
    $input = cli_input($prompt, '', array('n','y','q'));
    switch ($input) {
        case 'q':
            die;
        case 'n':
            mtrace('...  skipped ...');
            break;
        case 'y':
            fulldelete($dir);
            mtrace('... removed');
    }        
}
mtrace('** Detect old 1.9 Filter cache directories **');
$dir = $CFG->dataroot.'/filter';
if (!is_dir($dir)) {
    mtrace('... does not exist!');
} else {
    $prompt = 'Found '.$dir.'? type y (means yes) or n (means no) q (means quit) ';
    $input = cli_input($prompt, '', array('n','y','q'));
    switch ($input) {
        case 'q':
            die;
        case 'n':
            mtrace('...  skipped ...');
            break;
        case 'y':
            fulldelete($dir);
            mtrace('... removed');
    }        
}
mtrace('** Purge old local cache directory **');
$dir = $CFG->dataroot.'/cache';
if (!is_dir($dir)) {
    mtrace('Terrible failure! '.$dir.' does not exist!');
    die;
} else {
    $listing = scandir($dir);
    foreach ($listing as $filename) {
        if ($filename == '.' || $filename == '..') {
            continue;
        }
        $path = $dir.'/'.$filename;
        $prompt = 'Found '.$path.'? type y (means yes) or n (means no) q (means quit) ';
        $input = cli_input($prompt, '', array('n','y','q'));
        switch ($input) {
            case 'q':
                die;
            case 'n':
                mtrace('...  skipped ...');
                break;
            case 'y':
                fulldelete($path);
                mtrace('... removed');
        }
    }
}
mtrace('** Purge old local temp directory **');
$dir = $CFG->dataroot.'/temp';
if (!is_dir($dir)) {
    mtrace('Terrible failure! '.$dir.' does not exist!');
    die;
} else {
    $listing = scandir($dir);
    foreach ($listing as $filename) {
        if ($filename == '.' || $filename == '..') {
            continue;
        }
        $path = $dir.'/'.$filename;
        $prompt = 'Found '.$path.'? type y (means yes) or n (means no) q (means quit) ';
        $input = cli_input($prompt, '', array('n','y','q'));
        switch ($input) {
            case 'q':
                die;
            case 'n':
                mtrace('...  skipped ...');
                break;
            case 'y':
                fulldelete($path);
                mtrace('... removed');
        }
    }
}

mtrace('** Remove misc files **');
$file = array();
$files[] = $CFG->dataroot.'/lcapi_cookie.out';
$files[] = $CFG->dataroot.'/lcapi_tstamp.out';
$files[] = $CFG->dataroot.'/debug.out';
$files[] = $CFG->dataroot.'/panopto.txt';
foreach ($files as $file) {
    if (file_exists($file)) {
       $prompt = 'Found '.$file.'? type y (means yes) or n (means no) q (means quit) ';
       $input = cli_input($prompt, '', array('n','y','q'));
        switch ($input) {
            case 'q':
                die;
            case 'n':
                mtrace('...  skipped ...');
                break;
            case 'y':
                unlink($file);
                mtrace('... removed');
        } 
    }
}

mtrace('** clean up Dialogue problem **');
$checkcourses = array(2172, 7593, 7310, 7340, 5541);
foreach ($checkcourses as $courseid) {
    $shortname = $DB->get_field('course', 'shortname', array('id'=>$courseid));
    mtrace('checking '.$shortname.' dialogues...');
    // get course users
    $sql = "SELECT DISTINCT (ue.userid) 
                      FROM {user_enrolments} ue 
                     WHERE ue.enrolid 
                        IN (SELECT e.id 
                              FROM {enrol} e 
                             WHERE e.courseid = ?);";
    $users = $DB->get_records_sql($sql, array($courseid));
    $users = array_keys($users);
    // get course dialogues
    $dialogues = $DB->get_records('dialogue', array('course'=>$courseid));
    foreach ($dialogues as $dialogue) {
        $count = $DB->count_records('dialogue_conversations', array('dialogueid'=>$dialogue->id));
        if ($count > 2000 && !$dialogue->multipleconversations) {
            mtrace($count.' conversations found in dialogue#'.$dialogue->id.' '.$dialogue->name);
            $prompt = 'Delete? type y (means yes) or n (means no) q (means quit) ';
            $input = cli_input($prompt, '', array('n','y','q'));
            switch ($input) {
                case 'q':
                    die;
                case 'n':
                    //mtrace('...  skipped ...');
                    die;
                case 'y':
                    break;
            } 
            list($insql, $inparams) = $DB->get_in_or_equal($users);
            $params = $inparams;
            $sql = "SELECT dc.id
                      FROM {dialogue_conversations} dc
                     WHERE dc.dialogueid = $dialogue->id
                       AND dc.recipientid $insql
                  ORDER BY dc.id";
            $conversations = $DB->get_records_sql($sql, $params, 0, count($users));
            
            $excludedids = array_keys($conversations);
            list($excludesql, $excludeparams) = $DB->get_in_or_equal($excludedids, SQL_PARAMS_QM, 'ex', false);
            // Delete entries
            $select = "dialogueid = $dialogue->id
                       AND conversationid $excludesql";
            $DB->delete_records_select('dialogue_entries', $select, $excludeparams);
            // Delete conversations
            $select = "dialogueid = $dialogue->id
                       AND id $excludesql";
            $DB->delete_records_select('dialogue_conversations', $select, $excludeparams);
            
            // Delete read
            $sql = "DELETE 
                      FROM {dialogue_read} 
                     WHERE id IN (SELECT DISTINCT(dr.id) 
                                  FROM {dialogue_read} dr
                                  LEFT JOIN {dialogue_conversations} dc
                                  ON dr.conversationid = dc.id
                                  WHERE dc.id IS NULL)";
            mtrace('cleaning up read receipts');
            $DB->execute($sql);
            mtrace('purged!');
        }
    }
}

$sql = "SELECT COUNT(1)
        FROM {dialogue_read}
        WHERE id IN (SELECT DISTINCT(dr.id)
                     FROM {dialogue_read} dr
                     LEFT JOIN {dialogue_conversations} dc
                     ON dr.conversationid = dc.id
                     WHERE dc.id IS NULL)";
$count = $DB->count_records_sql($sql);

if ($count > 0) {
mtrace($count.' orphaned read receipts limit of 500000');
// Delete read
$sql = "DELETE
        FROM {dialogue_read}
        WHERE id IN (SELECT DISTINCT(dr.id)
                     FROM {dialogue_read} dr
                     LEFT JOIN {dialogue_conversations} dc
                     ON dr.conversationid = dc.id
                     WHERE dc.id IS NULL LIMIT 500000)";
mtrace('cleaning up read receipts');
$DB->execute($sql);
}
// Delete read
mtrace(' ** Completed **');

?>
