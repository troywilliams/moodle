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


// now get cli options
list($options, $unrecognized) = cli_get_params(
    array(
        'help'              => false
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
"Command line change WMS topics course format to topics format

Please note you must execute this script with the same uid as apache!

Options:
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/cli/change_wmstopicsformat.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

$count = $DB->count_records('course', array('format' => 'topics_wms'));
if ($count) {
    $prompt = "Found {$count} courses with topis_wms format, change to topics format?";
    $input = cli_input($prompt, '', array('n', 'y'));
    if ($input == 'y') {
        $DB->set_field('course', 'format', 'topics', array('format' => 'topics_wms'));
        purge_all_caches();
        mtrace('Done.');
    }
} else {
    mtrace("No courses found with 'topics_wms course format");
}
exit;
