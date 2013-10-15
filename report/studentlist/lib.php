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

/**
 * Library
 *
 * @package report_studentlist
 * @copyright 2013 The University of Waikato
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_studentlist_extend_navigation_course($navigation, $course, $context) {
    global $CFG;
    require_once($CFG->dirroot . '/report/studentlist/locallib.php');

    if (has_capability('report/studentlist:view', $context) and !report_studentlist_issite()) {
        $url = new moodle_url('/report/studentlist/index.php', array('id'=>$course->id));
        $sort = optional_param('sort', 'lastnameaz', PARAM_ALPHANUM);
        $url->param('sort', $sort);
        $navigation->add(new lang_string('pluginname', 'report_studentlist'),
                $url, navigation_node::TYPE_SETTING,
                null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function report_studentlist_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        'report-studentlist-*'       => get_string('page-report-studentlist-x',  'report_studentlist'),
    );
    return $array;
}
