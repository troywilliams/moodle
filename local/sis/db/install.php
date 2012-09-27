<?php

function xmldb_local_sis_install() {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('grade_items');
    $field = new xmldb_field('compulsory', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'needsupdate');
    /// Conditionally launch add field compulsory
    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field, $continue=true, $feedback=true);
    }
    return true;
}

?>