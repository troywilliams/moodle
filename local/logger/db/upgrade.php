<?php

function  xmldb_local_logger_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();
    $result = true;

    //===== 1.9.0 upgrade line ======//
    if ($oldversion < 2007090100) {
        if (!$dbman->table_exists(new xmldb_table('uow_logger')) and 
            !$dbman->table_exists(new xmldb_table('uow_log_entries'))) { // Lets check if tables exists.

            $result = $DB->get_manager()->install_from_xmldb_file($CFG->dirroot.'/local/logger/db/install.xml');
        }
    }

    return $result;
}

