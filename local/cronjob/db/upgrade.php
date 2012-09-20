<?php

function  xmldb_local_cronjob_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();
    $result = true;

    //===== 1.9.0 upgrade line ======//
    if ($oldversion < 2007090100) {
        $table = new xmldb_table('uow_cronjob');
        if ($dbman->table_exists($table)) { // Lets check if table exists.
            $result = $DB->get_manager()->install_from_xmldb_file($CFG->dirroot.'/local/cronjob/db/install.xml');
        }
    }

    return $result;
}
