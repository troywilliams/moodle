<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/csvlib.class.php');

/**
 * Reads csv enrolment file and creates or updates users.
 */
class local_uowuserimport {
    /** default fields/values **/
    public    $defaultfields = array();
    /** Columns required in file **/
    protected $requiredcolumns = array('username',
                                       'password',
                                       'firstname',
                                       'lastname',
                                       'email',
                                       'idnumber',
                                       'phone1',
                                       'phone2');
    /** Counters **/
    private static $usersnew = 0;
    private static $usersupdated = 0;
    private static $usersskipped = 0;
    
    /** Log statuses **/
    const LOG_DEBUG   = 'DEBUG ';
    const LOG_INFO    = 'INFO';
    const LOG_WARNING = 'WARNING';
    const LOG_ERROR   = 'ERROR';
    const LOG_NONE    = 'NONE';
    
    /**
     * Load plugin config variables
     */
    public function __construct() {
        $this->config = get_config('local_uowuserimport');
    }
    /**
     * Main work horse will parse file, create new users or update existing users
     * based on config.
     * 
     * @global type $CFG
     * @global type $DB
     * @return boolean false on error
     */
    public function run () {
        global $CFG, $DB;
        
        $starttime = microtime();
        
        self::log("Server Time: ".date('r', time()));
        
        $config = $this->config;
        $defaultfields = $this->get_default_fields();
        if (!is_file($config->filelocation) or !is_readable($config->filelocation)) {
            self::error("Could not open $config->filelocation");
            return false;
        }
        
        $iid = csv_import_reader::get_new_iid('local_uowuserimport');
        $cir = new csv_import_reader($iid, 'local_uowuserimport');
        
        $contents = file_get_contents($config->filelocation);
        if ($contents === false) {
            self::error('Cannot read contents of file');
            return false;
        }
        $delimiter = csv_import_reader::get_delimiter('comma');
        $readcount = $cir->load_csv_content($contents, $config->encoding, $delimiter);
        if (!$readcount) {
            self::error($cir->get_error());
            return false;
        }
        $requiredcolumns = $this->requiredcolumns;
        $filecolumns = $cir->get_columns();
        foreach ($requiredcolumns as $requiredcolumn) {
            if (!in_array($requiredcolumn, $filecolumns)) {
                self::error('Required column '. $requiredcolumn . ' missing');
                return false;
            }
        }
        // init csv import helper
        $cir->init();
        $linenum = 1; //column header is first line
        while ($line = $cir->next()) {
            $linenum++;
            
            $user = new stdClass();
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->confirmed = 1;
            $user->timemodified = time();
            foreach ($line as $keynum => $value) {
                if (!isset($filecolumns[$keynum])) {
                    // this should not happen
                    continue;
                }
                $key = $filecolumns[$keynum];
                $user->$key = $value;
                $processfunction = 'local_uowuserimport_process_'.$key;
                if (function_exists($processfunction)) {
                    $user->$key = $processfunction($value);
                }
            }
        
            // formatted info string
            $info = sprintf("[%-8s] %-20s %-20s %s", $user->username, 
                                                     $user->firstname, 
                                                     $user->lastname, 
                                                     $user->email);  
       
            $existinguser = $DB->get_record('user', array('username'=>$user->username, 'mnethostid'=>$user->mnethostid));
            if ($existinguser) {
                if (!$config->allowupdate) {
                    self::$usersskipped++;
                    self::info('skipped '. $info);
                    continue;
                }
                // get rid of password
                unset($user->password);

                $user->id = $existinguser->id;
                // update user - update_record ignores any extra properties
                $user->id = $DB->update_record('user', $user);

                self::$usersupdated++;
 
                self::info('updated '. $info);

                events_trigger('user_updated', $existinguser);

            } else {

                foreach ($defaultfields as $key => $value) {
                    $user->$key = $value;
                }
                // create user - insert_record ignores any extra properties
                $user->id = $DB->insert_record('user', $user);
                // make sure user context exists
                context_user::instance($user->id);
                
                self::$usersnew++;
                
                self::info('created ' . $info);

                events_trigger('user_created', $user);
            }

        }
        $cir->close();
        $cir->cleanup(true);
        
        if (!isset($this->lasterror)) {
            @unlink($config->filelocation);
        }
        
        $difftime = microtime_diff($starttime, microtime());
        self::log("{$difftime} seconds, new: " . self::$usersnew .
                                  " updated: " . self::$usersupdated . 
                                  " skipped: " . self::$usersskipped);

    }
    /**
     * Add a uowlogger to send log events too.
     * 
     * @param logger $uowlogger
     */
    public function add_uow_logger(logger $uowlogger) {
        $this->uowlogger = $uowlogger;
    }
    /**
     * Pulls default userfields/values for new user out of
     * plugin config.
     * 
     * @return array
     */
    public function get_default_fields() {
        
        $defaultfields = array();
        foreach (get_object_vars($this->config) as $key => $value) {
            if (strpos($key, 'defaultfield_') !== false) {
                $name = str_replace('defaultfield_', '', $key);
                $defaultfields[$name] = $value;
            }
        }
        return $defaultfields;
    }
    /**
     * Outputs to screen, will pass to uowlogger if attached.
     * 
     * @param type $message
     * @param type $level
     * @return type
     */
    private function log ($message, $level = self::LOG_NONE) {
        mtrace($message);
        if (isset($this->uowlogger)) {
            switch ($level) {
                case self::LOG_INFO:
                    $this->uowlogger->info($message);
                    break;
                case self::LOG_WARNING:
                    $this->uowlogger->warning($message);
                    break;
                case self::LOG_ERROR:
                    $this->lasterror = $message;
                    $this->uowlogger->error($message);
                    break;
            }
        }
        return;
    }
    /**
     * Quick info logger
     * @param type $message
     */
    private function info($message) {
        self::log($message, self::LOG_INFO);
    }
    /**
     * Quick error logger
     * @param type $message
     */
    private function error($message) {
        self::log($message, self::LOG_ERROR);
    }
}

/** Process functions to clean fields **/
function local_uowuserimport_process_username($value) {
    return clean_param($value, PARAM_USERNAME);
}
function local_uowuserimport_process_phone1($value) {
    return preg_replace('/[,.]/i', '', $value);
}
function local_uowuserimport_process_phone2($value) {
    return preg_replace('/[,.]/i', '', $value);
}
function local_uowuserimport_process_password($value) {
    return hash_internal_user_password($value, true);
}
