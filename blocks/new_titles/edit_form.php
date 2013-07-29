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

class block_new_titles_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB, $USER;

        // Fields for editing block contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_shownumentries', get_string('shownumentrieslabel', 'block_new_titles'), array('size' => 5));
        $mform->setType('config_shownumentries', PARAM_INT);
        $mform->addRule('config_shownumentries', null, 'numeric', null, 'client');
       
        $mform->setDefault('config_shownumentries', 5);
     

        $rssfeeds = $DB->get_records_sql_menu('
                SELECT id,
                       CASE WHEN preferredtitle = ? THEN ' . $DB->sql_compare_text('title', 64) .' ELSE preferredtitle END
                FROM {block_new_titles}
                ORDER BY CASE WHEN preferredtitle = ? THEN ' . $DB->sql_compare_text('title', 64) . ' ELSE preferredtitle END ',
                array('', $USER->id, ''));
        if ($rssfeeds) {
            $select = $mform->addElement('select', 'config_rssid', get_string('choosefeedlabel', 'block_new_titles'), $rssfeeds, array('size'=>'10'));
            $select->setMultiple(true);

        } else {
            $mform->addElement('static', 'config_rssid', get_string('choosefeedlabel', 'block_new_titles'),
                    get_string('nofeeds', 'block_new_titles'));
        }

        if (has_any_capability(array('block/new_titles:managefeeds', 'block/new_titles:selectfeeds'), $this->block->context)) {
            $mform->addElement('static', 'nofeedmessage', '',
                    '<a href="' . $CFG->wwwroot . '/blocks/new_titles/managefeeds.php?courseid=' . $this->page->course->id . '">' .
                    get_string('feedsaddedit', 'block_new_titles') . '</a>');
        }

        $mform->addElement('text', 'config_title', get_string('uploadlabel'));
        $mform->setType('config_title', PARAM_NOTAGS);
        $mform->setDefault('config_title', get_string('defaultblocktitle', 'block_new_titles'));

    }
}
