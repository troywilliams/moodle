<?php
define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/panopto/block_panopto.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'courseid'   => false,
        'fullforce'  => false,
        'help'       => false
    ),
    array(
        'h' => 'help'
    )
);
$courseid = $options['courseid'];
$fullforce = $options['fullforce'];
if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
if ($options['help']) {
    $help =
"Command line: Sync user list
Sync users from Moodle courses to Panopto folders

Options:
--courseid      Process just one course by id

-h, --help      Print out this help

Example:
\$sudo -u apache /usr/bin/php blocks/panopto/cli/syncusers.php
"; //TODO: localize - to be translated later when everything is finished
    echo $help;
    die;
}

$block = new block_panopto();

if ($courseid) {
    $prompt = 'Sync users for courseid#'.$courseid.'? type y (means yes) or n (means no)';
    $input = cli_input($prompt, '', array('n', 'y'));
    if ($input == 'n') {
        exit(1);
    }
    $block->sync_users($courseid, $fullforce);
} else {
    $prompt = 'Full user sync users? type y (means yes) or n (means no)';
    $input = cli_input($prompt, '', array('n', 'y'));
    if ($input == 'n') {
        exit(1);
    }
    $block->sync_users();
}
mtrace('Done!');
