<?php

function xmldb_block_new_titles_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $result = true;

    if ($oldversion < 2011050500) {
        /// Define table block_new_titles to be created
        $table = new XMLDBTable('block_new_titles');
        /// Adding fields to table
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('title', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('preferredtitle', XMLDB_TYPE_CHAR, '64', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, 'small', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('url', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
         /// Adding keys to table
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        /// Conditionally launch create table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        $from    = 'FROM {block_newtitles_feeds} ntf, {block_rss_client} rss
                    WHERE ntf.id_feed = rss.id ';
        $orderby = 'ORDER BY rss.title';
        
        $count  = $DB->count_records_sql('SELECT COUNT(rss.id) ' . $from);
        $rs     = $DB->get_recordset_sql('SELECT rss.* ' .
                                         $from . $orderby);
        $pbar   = new progress_bar('migratenewtitles', 500, true);
        $i      = 0;
        foreach ($rs as $feed) {
            $i++;
            upgrade_set_timeout(60); // set up timeout, may also abort execution
            $pbar->update($i, $count, "Migrating to new table - $i/$count");
 
            unset($feed->id);
            unset($feed->shared);

            $DB->insert_record('block_new_titles', $feed);

        }
        $rs->close();
        // drop old table
        $dbman->drop_table(new XMLDBTable('block_newtitles_feeds'));
        mtrace('block_newtitles_feeds dropped');
        // savepoint reached
        upgrade_block_savepoint(true, 2011050500, 'new_titles');
    }

}
