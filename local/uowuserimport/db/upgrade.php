<?php

function  xmldb_local_uowuserimport_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();
    $result = true;

    if ($oldversion < 2011062100) {
        $table = new xmldb_table('user');
        /// Changing precision of field phone1 on table user to (30), why because of this: ((675)32603) 32600061 x320
        $phone1 = new xmldb_field('phone1', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, 'msn');
        $dbman->change_field_precision($table, $phone1);
         /// Changing precision of field phone2 on table user to (30), why because of this: ((675)32603) 32600061 x320
        $phone2 = new xmldb_field('phone2', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null, 'phone1');
        $dbman->change_field_precision($table, $phone2);
    }

    return $result;
}
