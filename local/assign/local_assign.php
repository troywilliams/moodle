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
 * Extends the assign class to include custom strings in the recipient email
 *
 * @package    local
 * @subpackage assign
 * @author     Russell England <russell.england@catalyst-eu.net>
 * @copyright  Catalyst IT Ltd 2013 <http://catalyst-eu.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Returns an object with the submission status for the recipient email
 *
 * @param context the course module context
 * @param object $coursemodule the current course module
 * @param object $course the current course
 * @param object $userto user object
 * @return object submission status details
 */
function local_assign_get_submission_status($context,
                                            $coursemodule,
                                            $course,
                                            $userfrom,
                                            $userto,
                                            $info) {
    // If its cron then userfrom is the grader and userto is the student.
    // If its an update by the student, then userfrom is the student and userto will be student and teacher.
    // Assume userto is the student, unless userto is a teacher then assume userfrom is the student.
    $user = null;
    $roles = get_user_roles($context, $userto->id, true);
    foreach ($roles as $role) {
        if ($role->shortname == 'student') {
            $user = $userto;
            break;
        }
    }
    if (empty($user) || count($roles) > 1) {
        // Userto is not a student.
        // Or is a student but has multiple roles (might be a student and a teacher).
        // So see if userfrom is a student.
        $roles = get_user_roles($context, $userfrom->id, true);
        foreach ($roles as $role) {
            if ($role->shortname == 'student') {
                $user = $userfrom;
                break;
            }
        }
    }
    $local_assign = new local_assign($context, $coursemodule, $course);
    return $local_assign->get_submission_status($user, $info);
}

/**
 * Extending the class so I can use $this
 */
class local_assign extends assign {

    /**
     * Returns an object with the submission status details to be used as placeholders in recipient email
     *
     * Mostly a merge of functions view_student_summary() and render_assign_submission_status()
     *
     * @param stdClass $user the user to return the object for
     * @return stdClass $info with submission status details
     */
    public function get_submission_status($user, $info) {
        // Set up default values.
        $info->submissionstatus = get_string('nosubmission', 'assign');
        $info->duedate = get_string('duedateno', 'assign');
        $info->timeremaining = get_string('duedateno', 'assign');
        $info->lastmodified = get_string('nosubmission', 'assign');
        $info->filessubmitted = get_string('nosubmission', 'assign');
        $info->studentname = fullname($user);
        $info->studentid = $user->id;

        if ($this->can_view_submission($user->id)) {
            $instance = $this->get_instance();
            $submission = $this->get_user_submission($user->id, false);

            $teamsubmission = null;
            $submissiongroup = null;
            $notsubmitted = array();
            if ($instance->teamsubmission) {
                $teamsubmission = $this->get_group_submission($user->id, 0, false);
                $submissiongroup = $this->get_submission_group($user->id);
                $groupid = 0;
                if ($submissiongroup) {
                    $groupid = $submissiongroup->id;
                }
                $notsubmitted = $this->get_submission_group_members_who_have_not_submitted($groupid, false);
            }

            $time = time();

            // Work out the submission status for individual or team.
            if (!$this->is_any_submission_plugin_enabled()) {
                $info->submissionstatus = get_string('noonlinesubmissions', 'assign');
            } else {
                $info->submissionstatus = get_string('nosubmission', 'assign');
            }
            if (!$instance->teamsubmission) {
                if ($submission) {
                    $info->submissionstatus = get_string('submissionstatus_' . $submission->status, 'assign');
                }
            } else {
                if ($teamsubmission) {
                    $submissionsummary = get_string('submissionstatus_' . $teamsubmission->status, 'assign');
                    if (!empty($notsubmitted)) {
                        $viewfullnames = has_capability('moodle/site:viewfullnames', $this->get_course_context());
                        $userslist = array();
                        foreach ($notsubmitted as $member) {
                            $userslist[] = fullname($member, $viewfullnames);
                        }
                        $userstr = implode(', ', $userslist);
                        $submissionsummary .= html_writer::empty_tag('br');
                        $submissionsummary .= get_string('userswhoneedtosubmit', 'assign', $userstr);
                    }
                    $info->submissionstatus = $submissionsummary;
                }
            }

            // Due date and time remaining.
            $duedate = $instance->duedate;
            if ($duedate > 0) {
                // Due date.
                if ($flags = $this->get_user_flags($user->id, false)) {
                    if ($flags->extensionduedate) {
                        $duedate = $flags->extensionduedate;
                    }
                }
                $info->duedate = userdate($duedate);

                // Time remaining.
                if ($duedate - $time <= 0) {
                    if (!$submission ||
                            $submission->status != ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                        if ($this->is_any_submission_plugin_enabled()) {
                            $info->timeremaining = get_string('overdue', 'assign',
                                    format_time($time - $duedate));
                        } else {
                            $info->timeremaining = get_string('duedatereached', 'assign');
                        }
                    } else {
                        if ($submission->timemodified > $duedate) {
                            $info->timeremaining = get_string('submittedlate', 'assign',
                                    format_time($submission->timemodified - $duedate));
                        } else {
                            $info->timeremaining = get_string('submittedearly', 'assign',
                                    format_time($submission->timemodified - $duedate));
                        }
                    }
                } else {
                    $info->timeremaining = format_time($duedate - $time);
                }
            }

            // Last modified and files submitted.
            $thissubmission = $teamsubmission ? $teamsubmission : $submission;
            if ($thissubmission) {
                $info->lastmodified = userdate($thissubmission->timemodified);

                $filessubmitted = array();
                foreach ($this->get_submission_plugins() as $plugin) {
                    $pluginshowsummary = !$plugin->is_empty($thissubmission) || !$plugin->allow_submissions();
                    if ($plugin->is_enabled() &&
                        $plugin->is_visible() &&
                        $plugin->has_user_summary() &&
                        $pluginshowsummary) {
                        $pluginfiles = $plugin->get_files($thissubmission, $user);
                        foreach ($pluginfiles as $filename => $file) {
                            $filessubmitted[] = $filename;
                        }
                    }
                }

                if (!empty($filessubmitted)) {
                    $info->filessubmitted = implode(', ', $filessubmitted);
                }
            }
        }

        // Format for html output.
        foreach ($info as $key => $value) {
            $info->$key = format_string($value);
        }

        $info->submissionreceipttext = get_string('submissionreceipttext', 'local_assign', $info);
        $info->submissionreceipthtml = get_string('submissionreceipthtml', 'local_assign', $info);

        return $info;
    }

}