<?php

/**
 * @author Pieter le Roux <pgp@waikato.ac.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package moodle multiauth
 *
 * Authentication Plugin: WaikCookie
 *
 * Authenticate with WaikCookie's
 *
 * 2007-06-07  File created.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

class auth_plugin_waikcookie extends auth_plugin_base {

    /**
     * Constructor with initialisation.
     */
    function auth_plugin_waikcookie() {
        $this->authtype = 'waikcookie';
        $this->errorlogtag = '[AUTH WAIKCOOKIE] ';
        $this->init_plugin($this->authtype);
    }
    /**
     * Init plugin config from database settings depending on the plugin auth type.
     */
    function init_plugin($authtype) {
        $this->pluginconfig = 'auth/'.$authtype;
        $this->config = get_config($this->pluginconfig);
        if (empty($this->config->cookiepage)) {
            $this->config->cookiepage = 'https://cookie.waikato.ac.nz/cgi-bin/WCLogin';
        }
        if (empty($this->config->returnpage)) {
            $this->config->returnpage = 'http://www.waikato.ac.nz';
        }
    }
    /**
     * Returns true if the username and password work or don't exist and false
     * if the user exists and the password is wrong.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        global $CFG, $DB;
        $pass = false;

        //  Check that the user has a WaikCookie and the check that it is valid
        if (isset($_COOKIE['WaikCookie']) && !empty($_COOKIE['WaikCookie'])) {
            $pass = true;

            if (!isset($username) || (strtoupper($username) == 'NOTCOOKIE') || (strtoupper($username) == 'BADCOOKIE')) {
                $pass = false;
            }
            else if (!$DB->record_exists('user', array('username' => $username, 'mnethostid'=> $CFG->mnet_localhost_id))) {
                $pass = false;
            }
        }

        return $pass;
    }
    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {
        // set to defaults if undefined
        if (!isset($config->cookiepage)) {
            $config->cookiepage = 'https://cookie.waikato.ac.nz/cgi-bin/WCLogin';
        }
        if (!isset($config->returnpage)) {
            $config->returnpage = 'http://www.waikato.ac.nz';
        }

        // save settings
        set_config('cookiepage', $config->cookiepage, $this->pluginconfig);
        set_config('returnpage', $config->returnpage, $this->pluginconfig);

        return true;
    }
    /**
     * Returns the URL for changing the user's password, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        if (empty($this->config->stdchangepassword)) {
            return new moodle_url($this->config->changepasswordurl);
        } else {
            return null;
        }
    }

    /**
     * Will get called before the login page is shown.
     *
     */
    function loginpage_hook() {
        global $CFG;
        global $errormsg;
   
        // Prevent username from being shown on login page after logout
        $CFG->nolastloggedin = true;

        $manual   = optional_param('manual', null, PARAM_TEXT);
        $username = optional_param('username', null, PARAM_TEXT);
        $password = optional_param('password', null, PARAM_TEXT);
        $location = 'Location: '. $this->config->cookiepage . '?encref='. base64_encode(str_replace('~', '%7e', $CFG->wwwroot . '/login/index.php'));

        if ($manual === 'true') {
            $_POST['username'] = $username;
            $_POST['password'] = $password;
            $errormsg = '  ';
        }
        else if (isset($_COOKIE['WaikCookie']) && !empty($_COOKIE['WaikCookie'])) {
            $wcookie = $_COOKIE['WaikCookie'];
            $wuser   = null;

            // Make sure is a waikcookie and not something else
            if (!ctype_xdigit($wcookie)) {
                $wuser = 'BADCOOKIE';
            }
            else {
                $wuser = exec("/var/www/cgi-bin/wcdecode $wcookie");
            }

            //  If the WaikCookie has errored (i.e. badcookie) redirect to login page
            if (strtoupper($wuser) == 'BADCOOKIE') {
                @header($location);
            }

            $_POST['username'] = $wuser;
            $_POST['password'] = 'foobar';
        }
        else if (!isset($username) && !isset($password)) {
            @header($location);
        }
    }

    function logoutpage_hook() {
        global $redirect;
        $redirect = "http://cookie.waikato.ac.nz/cgi-bin/WCLogout?referrer=". $this->config->returnpage;
    }

}// End of the class
// Note: Errors on PHP closing tag, Unsure why have seen post somewhere, where can't remember.