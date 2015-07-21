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

/**
 * IMS Enterprise CLI tool.
 *
 * Notes:
 *   - it is required to use the web server account when executing PHP CLI scripts
 *   - you need to change the "www-data" to match the apache user account
 *   - use "su" if "sudo" not available
 *
 * @package    enrol_imsenterprise
 * @copyright  2015 Troy Williams
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array('help' => false,
          'force' => false),
    array('h' => 'help')
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Process IMS Enterprise enrolments, update expiration and send notifications.

Options:
-h, --help  Print out this help
-force      Force processing by clearing prev_path CFG
Example:
\$ sudo -u www-data /usr/bin/php enrol/imsenterprise/cli/sync.php
";

    echo $help;
    die;
}

if (!enrol_is_enabled('imsenterprise')) {
    cli_error('enrol_imsenterprise plugin is disabled, synchronisation stopped', 2);
}

$force = $options['force'];
// Force processing.
if ($force) {
    set_config('prev_path', '', 'enrol_imsenterprise');
    mtrace('OK : cleared prev_path cfg to force processing');
}

/** @var $plugin enrol_imsenterprise_plugin */
$plugin = enrol_get_plugin('imsenterprise');

$plugin->cron(); // Does not return anything.

exit();
