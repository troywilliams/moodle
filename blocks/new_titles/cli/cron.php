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
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->libdir.'/cronlib.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Execute New library titles cron action.

Options:
-h, --help            Print out this help

Example:
\$sudo -u apache /usr/bin/php blocks/new_titles/cli/cron.php
";

    echo $help;
    die;
}
/// increase time limit
    set_time_limit(0);
    $starttime = microtime();
/// increase memory limit
    raise_memory_limit(MEMORY_EXTRA);
/// emulate normal session
    cron_setup_user();
/// Start output log
    $timenow  = time();
    mtrace("Server Time: ".date('r',$timenow)."\n");
    // we will need the base class.
    require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
    require_once($CFG->dirroot.'/blocks/new_titles/block_new_titles.php');
    $block = new block_new_titles();
    mtrace('Processing cron function for new_titles....','');
    $block->cron(true);
    @set_time_limit(0);
    mtrace('done.');
    $difftime = microtime_diff($starttime, microtime());
    mtrace("Execution took ".$difftime." seconds");
