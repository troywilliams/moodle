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

defined('MOODLE_INTERNAL') || die();

function panopto_event_user_enrolled($eventdata) {
    global $DB;

    $record = $DB->get_record('block_panopto_foldermap', array('courseid'=>$eventdata->courseid));
    if ($record) {
        $guid = $record->folderid;
        if ($record->linkedfolderid) {
            $guid = $record->linkedfolderid;
        }
        $DB->set_field('block_panopto_foldermap', 'syncuserlist', 1, array('folderid'=>$guid));
    }
    return true;
}

function panopto_event_user_unenrolled($eventdata) {
    global $DB;

    $record = $DB->get_record('block_panopto_foldermap', array('courseid'=>$eventdata->courseid));
    if ($record) {
        $guid = $record->folderid;
        if ($record->linkedfolderid) {
            $guid = $record->linkedfolderid;
        }
        $DB->set_field('block_panopto_foldermap', 'syncuserlist', 1, array('folderid'=>$guid));
    }
    return true;
}