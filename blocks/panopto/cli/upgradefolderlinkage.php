<?php
define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->dirroot.'/blocks/panopto/lib/panopto_data.php');

$version = $DB->get_field('block', 'version', array('name' => 'panopto'));
if ($version != 2013100101) {
    mtrace('Incorrect version, cannot run script');
    exit;
}
        
list($options, $unrecognized) = cli_get_params(
    array(
        'help'       => false
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
"Command line: Upgrade folder linkage for cousrses to Panopto folders

-h, --help      Print out this help

Example:
\$sudo -u apache /usr/bin/php blocks/panopto/cli/upgradefolderlinkage.php
"; //TODO: localize - to be translated later when everything is finished
    echo $help;
    die;
}


require_once($CFG->dirroot . '/blocks/panopto/lib/panopto_data.php');

cron_setup_user(); // need to emulate admin user

$instancename = isset($CFG->block_panopto_instance_name) ?
                      $CFG->block_panopto_instance_name :
                      false;

if (!$instancename) {
    mtrace('No instance setup');
    exit;
}
mtrace('** Caution: backups been done? **');
$count = $DB->count_records('block_panopto_foldermap');
$prompt = 'Found '.$count.' records, would you like to proceed? type y (means yes) or n (means no)';
$input = cli_input($prompt, '', array('n', 'y'));
if ($input != 'y') {
   exit();
}

mtrace(' moving folderid to linkedfolderid');

$records = $DB->get_records('block_panopto_foldermap');
foreach ($records as $record) {

    if ($record->folderid and empty($record->linkedfolderid)) {
        // move folderid to linked folderid
        $DB->set_field('block_panopto_foldermap', 'linkedfolderid', $record->folderid,
                           array('courseid'=>$record->courseid));

        // clear folderid
        $DB->set_field('block_panopto_foldermap', 'folderid', '',
                           array('courseid'=>$record->courseid));

    }
}

mtrace(' checking linkedfolderid to see if primary folderid');
foreach ($records as $record) {
    $panopto = new panopto_data($record->courseid);
    $panoptofolder = $panopto->get_course();
    if ($panoptofolder->ExternalCourseID) {
        $courseid = str_replace($instancename.':', '', $panoptofolder->ExternalCourseID);
        // is primary
        if ($courseid == $record->courseid) {
            mtrace("courseid $record->courseid is primary, setting folderid");

            $DB->set_field('block_panopto_foldermap', 'folderid', $panoptofolder->PublicID,
                           array('courseid'=>$record->courseid));

        }
    }

}

mtrace('All done, move along...');
exit;
