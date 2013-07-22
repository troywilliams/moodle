<?php

function  xmldb_local_sis_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013050100) {
          $table = new xmldb_table('grade_items');
          $field = new xmldb_field('compulsory', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'needsupdate');
           /// Conditionally launch drop field compulsory
          if ($dbman->field_exists($table, $field)) {
             $result = $dbman->drop_field($table, $field, $continue=true, $feedback=true);
          }
          upgrade_plugin_savepoint(true, 2013050100, 'local', 'sis');

    }
    return true;
}

