<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_uowuserimport_install() {
    global $CFG, $DB;
    $plugin = 'local_uowuserimport';
   
    $defaults   = array('defaultfield_auth',
                        'defaultfield_city',
                        'defaultfield_country',
                        'defaultfield_timezone',
                        'defaultfield_lang',
                        'defaultfield_maildisplay',
                        'defaultfield_emailstop',
                        'defaultfield_mailformat',
                        'defaultfield_autosubscribe',
                        'defaultfield_trackforums',
                        'defaultfield_htmleditor');

    // Move old userimport config out of main $CFG to local plugin space
    $oldconfig = array();
    $rs = $DB->get_records_select('config', "name LIKE 'userimport_%'");
    foreach ($rs as $record) {
        $name = str_replace('userimport_', '', $record->name);
        $oldconfig[$name] = $record->value;
    }
    if ($oldconfig) {
        if (isset($oldconfig['filelocation'])) {
            set_config('filelocation', $oldconfig['filelocation'], $plugin);
        }
        set_config('encoding', 'UTF-8', $plugin);
        set_config('allowupdate', 1, $plugin);
        foreach ($defaults as $default) {
            $name = str_replace('defaultfield_', '', $default);
            if (isset($oldconfig[$name])) {
                set_config($default, $oldconfig[$name], $plugin);
            }
        }
        
    }
  
    unset_all_config_for_plugin('userimport');
    return true;
}


