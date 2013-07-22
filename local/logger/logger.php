<?php

/**
 * @author David Vega M.
 * @package moodle uow logger
 *
 * Logger Plugin: Centralize place to view past 
 * process and admin reporting 
 *
 * The logger class manage all log entries.  Those entries can 
 * be user define (by using the methods within this class) or 
 * php define.  There are 6 levels of log and 7 logging methods 
 * (one for each level and a generic method) error, warning, 
 * fine, info, config, debug.  Only error, warning and fine are 
 * display to the user interface if the logger object is initiate 
 * with the print flag set to true.
 *
 * The following is a guideline for calling the corresponding 
 * level methods:
 *
 * - error:   logs with level error should be input when the system 
 *            rise an error exception or when we know that the system 
 *            state can cause the system to halt unexpectedly
 *
 * - warning: an unexpected result has occur but it does not cause 
 *            the system to halt
 *
 * - fine:    logs the process progress
 *
 * - info:    logs the process progress but not need as user feed back
 *
 * - config,  used for debugging.  This entries are only recorded if 
 *   debug:   the uow_logger_debug config param (in the config table)
 *            is set above 0 or calling the allow_debug() function
 *
 * For all intent and proposes you will initiate a logger object using 
 * the factory method logger_get_logger.  This will guaranty that only 
 * one logger will be created per user process/request.  
 * 
 * E.g. if a user wish to upload a picture to his/her profile this 
 * could involve a picture upload form and, say, the upload_manager,
 * each with their individual logging.  The picture upload form is 
 * specific to this task but the upload_manager is not.  The upload_manager 
 * is used for any file upload in the system.  Nevertheless we want to 
 * capture loggings from the upload_manager as if they belong the picture 
 * upload form (as this is the task initiated by the user).  By calling 
 * the logger_get_logger function the upload_manager  can borrow an already 
 * initialize logger and attach its logs to this logger.  In the case that 
 * upload_manager is the top process and a logger does not already exists, 
 * then the logger_get_logger function will create a new logger that can be 
 * borrow by any sub-process.
 *
 * In some instance you may want to have an independent logger, in which case 
 * you can create your logger by normal object initialization (i.e. 
 * $loggerObj = new logger(name, type,print)) but this logger scope will be 
 * bound function scope.
 *
 *
 * 2007-09-01  File created.
 * 
 */

class logger {

    //  Level code/clour mapping    
    private static $LEVELS = array('ERROR'   => array(1000, 'CC0000'), 
                                   'WARNING' => array(800, 'FF6600'),
                                   'FINE'    => array(600, '0A5C0B'),
                                   'INFO'    => array(400, '0000CC'),
                                   'CONFIG'  => array(200, 'D7A803'),
                                   'DEBUG'   => array(0, '000000'),
                                   1000      => array('ERROR', 'CC0000'), 
                                   800       => array('WARNING', 'FF6600'),
                                   600       => array('FINE', '0A5C0B'),
                                   400       => array('INFO', '0000CC'),
                                   200       => array('CONFIG', 'D7A803'),
                                   0         => array('DEBUG', '000000'),
                                   );
    
    private static $CACHE = 30;
    private $inserts;
    private $inserStr;
    private $sqlStr;
    public $id;
    public $printlog;
    public $allowdebug;
    
    /**
     * Class constructor.  Note that very rarely you should construct a logger object, use the 
     * logger_get_logger factory function instead.
     * @param   string   $name  The name assosiated with the logger (normally the class or file name)
     * @param   string   $type  The process type (normally the function/method name)
     * @param   boolean  $print Whether the logger should output the log messages to the user.  Note
     *                          only logs with level higher than 599 will be output (error, warning, fine)
     */    
    public function logger($name, $type, $print=false) {
        global $CFG, $DB;
        
        //  Create a logger in the database
        $data = new stdClass();
        $data->id = '';
        $data->name = $name;
        $data->type = $type;
        $data->creationtime = time();    
        
        /*
        $this->inserts = 0;
        $this->insertStr = 'EXECUTE doLogInsert';
        */
        $this->id = $DB->insert_record('uow_logger', $data);
        $this->printlog = $print;
        $this->allowdebug = (get_config(null, 'uow_logger_debug') > 0);                     
    }
    
    /**
     * Allows the logger to record config and debug logs
     */
    public function allow_debug() {
        $this->allowdebug = true;
    }
    
    /**
     * Inserts a log message to the db with a specify level
     * @param   string   $msg     The message input in the log entry
     * @param   string   $level   The log level assosiated with the message
     * @return  string   $prntStr An html representation of the log entry
     */
    public function log($msg, $level='DEBUG') {
        global $DB;
        
        $level = self::get_log_code($level);
        
        if ($this->allowdebug || ($level > 399)) {
            
            
            $data = new stdClass();
            $data->id = '';
            $data->loggerid = $this->id;
            $data->level = $level;
            $data->message = $msg;
            $data->creationtime = time();
            
            $DB->insert_record('uow_log_entries', $data);
            
            /*
            $msg = addslashes($msg);
            
            $this->inserts++;
            $this->sqlStr .= $this->insertStr . "($this->id, '$level', '$msg');";
            
            if ($this->inserts >= self::$CACHE) {
                $this->do_inserts();
            }               
            */
            
            $prntStr = '<p style="color:#'. self::get_log_color($level) .'; text-align:center; padding: 5px;">'. $msg .'</p>';
            
            if ($this->printlog && ($level > 599)) {
                echo $prntStr; 
            }
            
            return $prntStr;
        }                   
    }
    
    /**
     * Insert all cache logs into the database
     * ******* This function is not currenly been used *******
     * ******* It is a very effective (4 or 5 times    *******
     * ******* faster) than individual inserts, but    *******
     * ******* currenly only available for postgresql  *******
     */ 
    public function do_inserts() {
        if (($this->inserts >= 0) && ($this->sqlStr != null)) {
            $this->inserts = 0;
            $this->sqlStr = 'PREPARE doLogInsert (bigint, smallint, text) AS INSERT INTO mdl_uow_log_entries (loggerid, level, message) VALUES ($1, $2, $3);' . $this->sqlStr . 'DEALLOCATE doLogInsert';
            execute_sql($this->sqlStr);
            $this->sqlStr = null;
        }    
    }
    
    /**
     * Inserts a log message to the db with level set to error (1000)
     * @param   string   $msg     The message input in the log entry
     * @return  string            An html representation of the log entry
     */
    public function error($msg) {
        return $this->log($msg, 'ERROR');
    }
    
    /**
     * Inserts a log message to the db with level set to warning (800)
     * @param   string   $msg     The message input in the log entry
     * @return  string            An html representation of the log entry
     */
    public function warning($msg) {
        return $this->log($msg, 'WARNING');
    }

    /**
     * Inserts a log message to the db with level set to fine (600)
     * @param   string   $msg     The message input in the log entry
     * @return  string            An html representation of the log entry
     */
    public function fine($msg) {
        return $this->log($msg, 'FINE');
    }

    /**
     * Inserts a log message to the db with level set to info (400)
     * @param   string   $msg     The message input in the log entry
     * @return  string            An html representation of the log entry
     */
    public function info($msg) {
        return $this->log($msg, 'INFO');
    }
    
    /**
     * Inserts a log message to the db with level set to config (200)
     * @param   string   $msg     The message input in the log entry
     * @return  string            An html representation of the log entry
     */
    public function config($msg) {
        return $this->log($msg, 'CONFIG');
    }  
    
    /**
     * Inserts a log message to the db with level set to debug (0)
     * @param   string   $msg     The message input in the log entry
     * @return  string            An html representation of the log entry
     */
    public function debug($msg) {
        return $this->log($msg);
    }
    
    /**
     * Retrieves the colour associated with a log level
     * @param   string   $level  A log level code
     * @return  string           A hex number representation of 
     *                           the colour assosiated with $level
     */
    public static function get_log_color($level) {
        return self::$LEVELS[strtoupper($level)][1];
    }
    
    /**
     * Retrieves the code associated with a log level
     * @param   int    $level  A log level
     * @return  string         A string representation 
     *                         assosiated with $level  
     */
    public static function get_log_code($level) {
        return self::$LEVELS[strtoupper($level)][0];
    }
}

/***********  Class wrapper ***********/
/**
 * Factory function (the preferred way to initialize the logger object)
 * @param   string   $name  The name assosiated with the logger (normally the class or file name)
 * @param   string   $type  The process type (normally the function/method name)
 * @param   boolean  $print Whether the logger should output the log messages to the user.  Note
 *                          only logs with level higher than 599 will be output (error, warning, fine)
 */
function &logger_get_logger($name, $type, $print=false) {
    if (!isset($GLOBALS['logger'])) {
        //register_shutdown_function('logger_insert_last_logs');
        set_error_handler('logger_error_handler');
        $logger = new logger($name, $type, $print);
        $GLOBALS['logger'] = &$logger;
    }
    return $GLOBALS['logger'];
}

/**
 * Clear existing logger
 */
function logger_clear_logger() {
    if (isset($GLOBALS['logger'])) {
        restore_error_handler();
        unset($GLOBALS['logger']);
    }
}

/**
 * Inserts the any remaning logs in the cache into the databade before exiting
 * ******* This function is not currenly been used *******
 */
function logger_insert_last_logs() {
    if (isset($GLOBALS['logger'])) {
        $GLOBALS['logger']->do_inserts();
    }    
}

/**
 * This function is to be use by PHP and not by you!!!
 * Handles php errors by mapping the php errors to logger log levels
 * and inserting a corresponding log entry
 */
function logger_error_handler($errno, $errmsg, $filename, $linenum, $vars) {

    $errortype = array (
                E_ERROR              => array('ERROR','Error'), 
                E_WARNING            => array('WARNING','Warning'),
                E_PARSE              => array('WARNING','Parsing Error'),
                E_NOTICE             => array('DEBUG','Notice'),
                E_CORE_ERROR         => array('ERROR','Core Error'),
                E_CORE_WARNING       => array('WARNING','Core Warning'),
                E_COMPILE_ERROR      => array('ERROR','Compile Error'),
                E_COMPILE_WARNING    => array('WARNING','Compile Warning'),
                E_USER_ERROR         => array('ERROR','User Error'),
                E_USER_WARNING       => array('WARNING','User Warning'),
                E_USER_NOTICE        => array('DEBUG','User Notice'),
                E_STRICT             => array('DEBUG','Runtime Notice'),
                E_DEPRECATED         => array('DEBUG','Runtime Notice'),
                E_USER_DEPRECATED    => array('DEBUG','Runtime Notice'),
                E_RECOVERABLE_ERROR  => array('ERROR','Catchable Fatal Error')
                );
    
    $msg = $errortype[$errno][1] ." - in $filename($linenum): $errmsg";
    
    $GLOBALS['logger']->log($msg, $errortype[$errno][0]);
}

?>
