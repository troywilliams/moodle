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


function xmldb_block_panopto_upgrade($oldversion, $block) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    
    if ($oldversion < 2013100100){
        $table = new xmldb_table('block_panopto_foldermap');
        
        $courseidfield = new xmldb_field('moodleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $courseidfield)) {
            $dbman->rename_field($table, $courseidfield, 'courseid');
        }
        $panoptofolderidfield = new xmldb_field('panopto_id', XMLDB_TYPE_CHAR, '36', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $panoptofolderidfield)) {
            $dbman->rename_field($table, $panoptofolderidfield, 'folderid');
        }
        $panoptolinkedfolderidfield = new xmldb_field('linkedfolderid', XMLDB_TYPE_CHAR, '36', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $panoptolinkedfolderidfield)) {
            $dbman->add_field($table, $panoptolinkedfolderidfield);
        }
        $syncuserlistfield = new xmldb_field('syncuserlist', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'panopto_id');
        if (!$dbman->field_exists($table, $syncuserlistfield )) {
            $dbman->add_field($table, $syncuserlistfield );
        }
        upgrade_block_savepoint(true, 2013100100, 'panopto');
    }

    if ($oldversion < 2013100101){
        if (isset($CFG->block_panopto_instancename)){
            set_config('block_panopto_instance_name', $CFG->block_panopto_instancename);
            unset_config('block_panopto_instancename');
        }
        if (isset($CFG->block_panopto_servername)){
            set_config('block_panopto_server_name', $CFG->block_panopto_servername);
            unset_config('block_panopto_servername');
        }
        if (isset($CFG->block_panopto_applicationkey)){
            set_config('block_panopto_application_key', $CFG->block_panopto_applicationkey);
            unset_config('block_panopto_applicationkey');
        }
        upgrade_block_savepoint(true, 2013100101, 'panopto');
    }
    return true;
}