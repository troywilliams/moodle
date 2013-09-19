<?php
/**
 * Moodle - Modular Object-Oriented Dynamic Learning Environment
 *          http://moodle.org
 * Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * View and allocate users to groups in a single screen
 *
 * @author    Matt Clarkson mattc@catalyst.net.nz
 * @author    Chris Wharton chrisw@catalyst.net.nz
 * @version   0.0.3
 * @copyright Catalyst IT Ltd 2013 <http://catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package   groups
 */

global $DB;

require_once('../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/tablelib.php');

$courseid = required_param('courseid', PARAM_INT);
$roleid = optional_param('roleid', 0, PARAM_INT);
// Hack: get around array of arrays clean problem for $updatedgroupsusers
$updatedgroupsusers = isset($_POST['groups_users']) ? $_POST['groups_users'] : array();
$updatedgroupsusers = clean_param_array($updatedgroupsusers, PARAM_CLEAN, true);
$update = optional_param('update', 0, PARAM_INT);

$returnurl = $CFG->wwwroot.'/group/index.php?id='.$courseid;

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

$coursecontext = context_course::instance($course->id);

require_login($course);

$pageurl = new moodle_url('/group/assign_advanced.php', array('courseid'=>$courseid));

// set up page

$PAGE->set_url($pageurl);
$PAGE->set_context($coursecontext);

if (!has_capability('moodle/course:managegroups', $coursecontext)) {
    redirect('/course/view.php', array('id'=>$course->id)); // Not allowed to manage all groups
}

$strgroups = get_string('groups');
$strparticipants = get_string('participants');
$stroverview = get_string('overview', 'group');
$strgrouping = get_string('grouping', 'group');
$strgroup = get_string('group', 'group');
$strselectfromrole = get_string('selectfromrole', 'group');
$strnotingrouping = get_string('notingrouping', 'group');
$strfiltergroups = get_string('filtergroups', 'group');
$stradvancedadduserstogroups = get_string('advancedadduserstogroups', 'group');
$strtotals = get_string('totals', 'group');

/// Get applicable roles
$rolenames = array();
$avoidroles = array();

if ($roles = get_roles_used_in_context($coursecontext, true)) {
    $canviewroles    = get_roles_with_capability('moodle/course:viewparticipants', CAP_ALLOW, $coursecontext);
    $doanythingroles = get_roles_with_capability('moodle/user:delete', CAP_ALLOW, $PAGE->context);

    foreach ($roles as $role) {
        if (!isset($canviewroles[$role->id])) {   // Avoid this role (eg course creator)
            $avoidroles[] = $role->id;
            unset($roles[$role->id]);
            continue;
        }
        if (isset($doanythingroles[$role->id])) {   // Avoid this role (ie admin)
            $avoidroles[] = $role->id;
            unset($roles[$role->id]);
            continue;
        }
        $rolenames[$role->id] = strip_tags(role_get_name($role, $coursecontext));   // Used in menus etc later on
    }
}

// Get groups
if (!$groups = groups_get_all_groups($course->id)) {
    redirect($returnurl, "No groups defined for course");
    die;
}

// Get potental users
if (!$users = groups_get_potential_members($course->id, $roleid)) {
    print_error("No users enrolled in course");
}

// Get current group membership
$sql = "SELECT groupid, userid " .
       "FROM {groups_members} " .
       "WHERE groupid IN (".implode(',', array_keys($groups)).")";
$rs = $DB->get_recordset_sql($sql);

foreach ($rs as $row) {
	$groupsusers[$row->groupid][$row->userid] = true;
}

/// Process form submission
if (!empty($update)) {
    foreach ($users as $user) {
        foreach ($groups as $group) {
            // Insert, delete or ignore
            if (isset($updatedgroupsusers[$group->id][$user->id]) && !isset($groupsusers[$group->id][$user->id])) {
                // insert
                $groupmember = new stdClass;
                $groupmember->groupid = $group->id;
                $groupmember->userid = $user->id;

                $DB->insert_record('groups_members', $groupmember);

            } elseif (!isset($updatedgroupsusers[$group->id][$user->id]) && isset($groupsusers[$group->id][$user->id])) {
                // delete
                $DB->delete_records('groups_members', array('groupid' => $group->id, 'userid' => $user->id));
            }
            // anything else gets ignored.
        }
    }
    redirect($returnurl);
}

$strheading = get_string('editgroupsettings', 'group');

$PAGE->navbar->add($strparticipants, new moodle_url('/user/index.php', array('id'=>$courseid)));
$PAGE->navbar->add($strgroups, new moodle_url('/group/index.php', array('id'=>$courseid)));
$PAGE->navbar->add($strheading);
$PAGE->requires->js_init_call('M.core_group.updatetotals');

/// Print header
$PAGE->set_title($strgroups);
$PAGE->set_heading($course->fullname . ': '.$strgroups);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('adduserstogroup', 'group'), 3);

// define column headers
$grp_user_cnt = array();
$tablecolumns = array('name');
$tableheaders = array(get_string('fullname'));

foreach ($groups as $group) {
	$tablecolumns[] = 'group-'.$group->id;
    $tableheaders[] = $group->name;

    $grp_user_cnt[$group->id] = 0;
}
$tablecolumns[] = 'checksum';
$tableheaders[] = $strtotals;
$table = new flexible_table('group-allocation-'.$course->id);
$table->baseurl = $pageurl;

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'group-advanced-assign');
$table->set_attribute('class', 'generaltable generalbox');

$table->setup();

// Print form
foreach ($roles as $role) {
    $selected = $role->id == $roleid ? 'selected="selected"':'';
}

echo '<form id="advanced-assign-group-select-form" class="mform">';
echo '  <input type="hidden" value="'.$courseid.'" name="courseid"/>';
echo "<label for=\"groupselect\"> $strselectfromrole </label>";
echo '<select id="roleselect" name="roleid" onchange="this.parentNode.submit();">';
echo '    <option value="">'.get_string('all').'</option>';
echo "<option value=\"{$role->id}\" $selected>".format_string($role->name)."</option>\n";
echo '</select>';
echo '</form>';

echo '<form id="advanced-assign-form" class="mform" method="post" >';
echo '<fieldset id="autogroup" class="clearfix">';
echo '<legend class="ftoggler">'.$stradvancedadduserstogroups.'</legend>';

foreach ($users as $user) {
	$data = array();
    $data[] = fullname($user);
    $count = 0;
    foreach ($groups as $group) {
        if (isset($groupsusers[$group->id][$user->id])) {
            $checked = 'checked="checked"';
            $count++;
            $grp_user_cnt[$group->id]++;
        } else {
            $checked = '';
        }

        $data[] = "<input type=\"checkbox\" class=\"advadduserstogroups\" name=\"groups_users[{$group->id}][{$user->id}]\" $checked />";
    }
    $data[] = "<strong><span id=\"user_grp_cnt_{$user->id}\">$count</span></strong>";
    $table->add_data($data);
}

$data = array();
$data[] = '<strong>'.$strtotals.'</strong>';
foreach ($groups as $group) {
    $data[] = "<strong><span id=\"grp_user_cnt_{$group->id}\">{$grp_user_cnt[$group->id]}</span></strong>";
}
$table->add_data($data);
$table->finish_output();

// Print the dialog
echo '</fieldset>';
echo '  <input type="hidden" value="'.$courseid.'" name="courseid"/>';
echo '  <input type="hidden" value="1" name="update"/>';
echo '<div class="buttons">';
echo '  <input id="id_submitbutton" type="submit" value="Submit" name="submitbutton"/>';
echo '  <input id="id_cancel" type="submit" value="Cancel" name="cancel"/>';
echo '</div>';
echo '</form>';
echo $OUTPUT->footer();
