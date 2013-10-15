<?php
/* Copyright Panopto 2009 - 2013 / With contributions from Spenser Jones (sjones@ambrose.edu)
 *
 * This file is part of the Panopto plugin for Moodle.
 *
 * The Panopto plugin for Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The Panopto plugin for Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the Panopto plugin for Moodle.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once($CFG->dirroot.'/blocks/panopto/lib/panopto_data.php');

class block_panopto_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $DB, $COURSE, $PAGE;

        // Construct the Panopto data proxy object
        $panoptodata = new panopto_data(null);
        if (!empty($panoptodata->servername) && !empty($panoptodata->instancename) && !empty($panoptodata->applicationkey)) {
            $selectlist = array();
            $selecteditem = false;
            // Construct the Panopto data proxy object
            $panoptodata = new panopto_data(null);
            $record = $DB->get_record('block_panopto_foldermap', array('courseid'=>$COURSE->id));
            // match up selected item for this course
            if ($record) {
                $panoptodata = new panopto_data($record->courseid);
                $courseinfo = $panoptodata->get_course();
                if ($courseinfo->Access != 'Error') {
                    $selecteditem = $courseinfo->PublicID;
                }
            }
            // Get available Panopto folders
            $panoptofolders = $panoptodata->get_courses();
            if (!empty($panoptofolders)) {
                foreach($panoptofolders as $panoptofolder) {
                     if ($panoptofolder->Access == 'Viewer') {
                         continue; // skip courses with viewer access
                     }
                     $displayname = s($panoptofolder->DisplayName);
                     $selectlist[$panoptofolder->Access][$panoptofolder->PublicID] = shorten_text($displayname, 80);
                }
            }

             // Fields for editing block contents.
            $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
            // No match so need to allow for provisioning the folder on Panopto
            if (empty($record->folderid)) {
                $html = '';
                $html .= '<p>No existing folder found linked to this Paper on the Panopto server<p>';
                $createfolderurl = new moodle_url('/blocks/panopto/createfolder.php', array('courseid'=>$COURSE->id));//, 'returnurl'=>urlencode($PAGE->url.'&sesskey='.sesskey())
                $html .= html_writer::link($createfolderurl, html_writer::tag('h2', 'Create new folder'));
                $html .= '<p>or, select an existing folder:<p>';
                $mform->addElement('html', $html);
            }
            // The list
            if ($selecteditem) {
                $elselect  = &$mform->addElement('selectgroups', 'config_linkedfolderid', 'Currently using folder:', $selectlist, array('disabled'), false);
                $elselect->setSelected($selecteditem);
            } else {
                $elselect  = &$mform->addElement('selectgroups', 'config_linkedfolderid', 'Currently using folder:', $selectlist, array('disabled'), true);
            }
            $mform->addElement('html', '<input type="button" name="'.get_string('edit').'" value="'.get_string('edit').'" onclick="document.getElementById(\'id_config_linkedfolderid\').disabled = false" /><br/><br/>');
            $mform->addElement('hidden', 'config_courseid', $COURSE->id);
            $mform->setType('config_courseid', PARAM_INT);
        } else {
          $notice = '<div class="error">Cannot configure block instance: Global configuration incomplete.<br />Please contact your system administrator.</div>';
          $mform->addElement('html', $notice);
        }
    }
}
