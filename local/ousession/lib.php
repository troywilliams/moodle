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

/**
 * OU custom session library for memcache sessions.
 *
 * Moodle 2 doesn't let you do session handling the way we want!
 * 1. Extend session_stub set savehandler to memcache.
 * 2. Implement session_exists($sid) for memcache.
 * In config.php set 2 defines to set up our own session class:
 * define('SESSION_CUSTOM_FILE', '/local/ousession/lib.php') ;
 * define('SESSION_CUSTOM_CLASS', 'ou_memcache_session') ;`
 *
 * @package local
 * @subpackage ousession
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class ou_memcache_session extends session_stub {

    protected function init_session_storage() {
        global $CFG;
        ini_set('session.save_handler', 'memcache');
        ini_set('session.gc_maxlifetime', $CFG->sessiontimeout);
        ini_set('memcache.hash_strategy', 'consistent');
        /* Recommend below is set and adjusted in php.ini
        ini_set('session.save_path', 'tcp://<server>:11211');
        */
        $hostlist = ''; //default
        if (!isset($CFG->memcachehostlist)) {
            throw new coding_exception('$CFG->memcachehostlist must be defined in config.php');
        } else {
            $hostlist = $CFG->memcachehostlist;
        }
        ini_set('session.save_path', $hostlist);
    }

    public function session_exists($sid) {

        $sid = clean_param($sid, PARAM_FILE);

        // Create memcache object
        $memcache_obj = new Memcache;

        // Get the memcache hosts
        $hostlist = ini_get('session.save_path');
        $hostarray = explode(",", $hostlist);

        // Add all the memcache server to the memcache object to make sure we
        // are checking them all
        foreach ($hostarray as $host) {
            // rid the port number (probably 11211) if it is there
            // TODO: Below needs "fixing" for other ports
            $host = str_ireplace(":11211", "", $host);
            $memcache_obj->addServer($host);
        }

        // See if the session exists.
        if ($memcache_obj->get($sid)) {
            return true;
        } else {
            return false;
        }
    }
}
