<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_uowofficialuserpix_install() {
    global $CFG;
    // Move old userpiximport config out of main $CFG to local plugin space
    $plugin = 'local_uowofficialuserpix';
    if (isset($CFG->userpiximport_repodir)) {
        set_config('repodir', $CFG->userpiximport_repodir, $plugin);
    }
    if (isset($CFG->userpiximport_all)) {
        set_config('all', $CFG->userpiximport_all, $plugin);
    }
    unset_all_config_for_plugin('userpiximport');
    return true;
}