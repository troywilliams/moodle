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
 * The main group management user interface.
 * 
 * @author    Troy Williams
 * @author    Matt Clarkson mattc@catalyst.net.nz
 * @author    Chris Wharton chrisw@catalyst.net.nz
 * @version   0.1
 * @copyright Catalyst IT Ltd 2013 <http://catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   core_group
 */

require_once('../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/tablelib.php');

$courseid   = required_param('courseid', PARAM_INT);
$roleid     = optional_param('roleid', 0, PARAM_INT);
$update     = optional_param('update', 0, PARAM_INT);

$course     = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_login($course);
$context    = context_course::instance($courseid);
require_capability('moodle/course:managegroups', $context);

$pageurl    = new moodle_url('/group/advancedgroupassign.php', array('courseid' => $courseid));
$returnurl  = new moodle_url('/group/index.php', array('id' => $courseid));

// Set up page.
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
// Strings to be used later.
$straddremove       = get_string('adduserstogroup', 'group');
$strgroup           = get_string('group', 'group');
$strgroups          = get_string('groups');
$strparticipants    = get_string('participants');
$strselectfromrole  = get_string('selectfromrole', 'group');
$strtotals          = get_string('totals', 'group');

// Get potential users.
$users = groups_get_potential_members($course->id, $roleid);
if (!$users) {
    redirect($returnurl, get_string('nousersenrolled')); // No users enrolled in course.
}
// Get course groups.
$groups = groups_get_all_groups($course->id);
if (!$groups) {
    redirect($returnurl, get_string('nogroups', 'group')); // No groups defined for course.
}
// Get current group memberships.
list($insql, $inparams) = $DB->get_in_or_equal(array_keys($groups), SQL_PARAMS_NAMED);
$sql = "SELECT groupid, userid
          FROM {groups_members}
         WHERE groupid $insql";
$rs = $DB->get_recordset_sql($sql, $inparams);
$memberships = array();
foreach ($rs as $record) {
    $memberships[$record->groupid][$record->userid] = true;
}
$rs->close();

// Process submitted data.
$data = data_submitted();
if ($data) {
    require_sesskey(); // CSRF check.
    $data = (array) $data;
    $userupdates = array(); // Users to process.
    $membershipupdates = array();
    // Cycle data looking for matching keys.
    foreach ($data as $key => $value) {
        $pattern = '/user-(\d.*)/'; // Extract user identifier.
        preg_match($pattern, $key, $matches);
        if (!empty($matches)) {
            $userid = $matches[1];
            $userupdates[$userid] = $users[$userid];
        }
        $pattern = '/group-(\d.*)-user-(\d.*)/'; // Extract group, user identifiers.
        preg_match($pattern, $key, $matches);
        if (!empty($matches)) {
            $groupid = $matches[1];
            $userid = $matches[2];
            $membershipupdates[$groupid][$userid] = true;
        }
    }
    // Process users that where sent.
    foreach ($userupdates as $user) {
        foreach ($groups as $group) {
            // Have current membership but empty in updates.
            if (isset($memberships[$group->id][$user->id]) and !isset($membershipupdates[$group->id][$user->id])) {
                groups_remove_member($group->id, $user->id);
                // Don't have current membership but are in updates.
            } else if (!isset($memberships[$group->id][$user->id]) and isset($membershipupdates[$group->id][$user->id])) {
                groups_add_member($group->id, $user->id);
            }
        }
    }
    if (!empty($userupdates)) {
        redirect($pageurl);
    }
}

$table = new html_table();
$table->id = 'groups-users';
$header = array();
$cell = new html_table_cell(get_string('fullname'));
$cell->header = true;
$header[] = $cell;
foreach ($groups as $group) {
    $cell = new html_table_cell($group->name);
    $cell->header = true;
    $header[] = $cell;
}

$PAGE->navbar->add($strparticipants, new moodle_url('/user/index.php', array('id' => $courseid)));
$PAGE->navbar->add($strgroups, new moodle_url('/group/index.php', array('id' => $courseid)));
$PAGE->navbar->add($straddremove);

$PAGE->requires->js_init_call('M.core_group.updatetotals');
$PAGE->set_title($strgroups);
$PAGE->set_heading($course->fullname . ': '. $strgroups);

// Start table build.
$cell = new html_table_cell('');
$cell->header = true;
$header[] = $cell;
$row = new html_table_row($header);
$table->data[] = $row;
foreach ($users as $user) {
    $row = array();
    $key = 'user-' . $user->id;
    $attrs = array('type' => 'hidden', 'id' => $key, 'name' => $key);
    $row[] = fullname($user) . html_writer::empty_tag('input', $attrs);
    $ua = 0;
    foreach ($groups as $group) {
        $key = 'group-' . $group->id . '-user-' . $user->id;
        $attrs = array('type' => 'checkbox', 'id' => $key, 'name' => $key, 'class' => 'check12');
        if (isset($memberships[$group->id][$user->id])) {
            $attrs['checked'] = 'checked';
            $ua++;
        }
        $row[] = html_writer::empty_tag('input', $attrs);
    }
    $span = html_writer::span($ua, null, array('id' => 'user-' . $user->id . '-total'));
    $cell = new html_table_cell($span);
    $row[] = $cell;
    $table->data[] = $row;
}
$footer = array('');
// Cycle groups get membership count, used in footer row build.
foreach ($groups as $id => $group) {
    $total = isset($memberships[$id]) ? count($memberships[$id]) : 0;
    $footer[] = html_writer::span($total, null, array('id' => 'group-' . $id . '-total'));
}
$footer[] = $strtotals;
$table->data[] = $footer;

echo $OUTPUT->header();
echo $OUTPUT->heading($straddremove, 3);

// Role selector.
$roles = get_profile_roles($context);
$rolenames = role_fix_names(get_profile_roles($context), $context, ROLENAME_ALIAS, true);
echo html_writer::label($strselectfromrole, 'roleid');
echo $OUTPUT->single_select($pageurl, 'roleid', $rolenames, $roleid, array(0 => get_string('allparticipants')));

$formattrs = array('method' => 'post', 'action' => $pageurl);
echo html_writer::start_tag('form', $formattrs);
// Output the table.
echo html_writer::table($table);
$pageurl->param('sesskey', sesskey()); // Add hidden session key.
echo html_writer::input_hidden_params($pageurl);
echo html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'submit', 'value' => 'Save'));
echo html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'cancel', 'value' => 'Cancel'));
echo html_writer::end_tag('form');
echo $OUTPUT->footer();
