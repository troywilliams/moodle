<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////
require_once($CFG->dirroot.'/course/lib.php');

class course_archiver {

    const PROCESS_LIMIT = 1000;

    const MODE_ARCHIVE = 51;

    /** automated archiver are active and ready to run */
    const STATE_READY = 0;
    /** automated archiver are disabled and will not be run */
    const STATE_DISABLED = 1;
    /** automated archiver are all ready running! */
    const STATE_RUNNING = 2; 
    /** success of a stage operation */
    const STATUS_OK = 0;
    /** failure of a stage operation */
    const STATUS_ERROR = 1;
    /** holds plugin configuration variables */
    private static $config = null;


    /**
     *
     * @return stdClass $config 
     */
    public static function get_config() {
        
        if (!isset(course_archiver::$config)) {
            course_archiver::$config = get_config('local_coursearchiver');
        }
        return course_archiver::$config;
    }
    
    /**
     *
     * @global moodle_database $DB
     * @param stdClass $course
     * @return stdClass $record
     * @throws moodle_exception 
     */
    public static function add_to_queue(stdClass $course) {
        global $DB;        
        
        if ($course->id == SITEID) {
            throw new moodle_exception('site cannot be archived');
        }  
        $record = $DB->get_record('course_archiver', array('courseid'=>$course->id));
        if (!empty($record)) {
            throw new moodle_exception('course has already been queued for archival');
        } else {
            $record = new stdClass();
            $record->courseid = $course->id;
            $record->timequeued = time();
            $record->id = $DB->insert_record('course_archiver', $record);
        }
        return $record;
    }
    
    public static function remove_from_queue(stdClass $course) {
        global $DB;
        
        return $DB->delete_records('course_archiver', array('courseid'=>$course->id));
    }
    
    /**
     *
     * @global moodle_database $DB
     * @return boolean 
     */    
    public static function run($timeoverride=null) {
        global $DB;
        
        $total = 0;
        $completed = 0;
        $state = course_archiver::get_state();
        if ($state === course_archiver::STATE_DISABLED) {
            mtrace("Course archiver is currently DISABLED. Check settings");
            return false;
        }
        if ($state === course_archiver::STATE_RUNNING) {
            mtrace("Course archiver is already RUNNING. Execution delayed");
            return false;
        }
        
        $config = course_archiver::get_config();

        course_archiver::set_state_running(true);

        $starttime = microtime();
        $timenow  = time();
        mtrace("Course archiver launched at " . date('r', $timenow));

        // This block removes courses from queue table that have been moved out of archive category
        $sql = "SELECT c.id, c.shortname
                  FROM {course} c
             LEFT JOIN {course_archiver} ca
                    ON ca.courseid = c.id
                 WHERE c.category != :category
                   AND ca.id IS NOT NULL";
        
        $courses = $DB->get_records_sql($sql, array('category'=>$config->archivecategory));
        foreach ($courses as $course) {
            course_archiver::remove_from_queue($course);
            mtrace('Removing course from queue '.$course->shortname);
        }
        
        // This block adds courses to queue table that have been moved into archive category
        $sql = "SELECT c.id, shortname
                  FROM {course} c
             LEFT JOIN {course_archiver} ca
                    ON ca.courseid = c.id
                 WHERE c.category = :category
                   AND ca.id IS NULL";
        
        $courses = $DB->get_records_sql($sql, array('category'=>$config->archivecategory));
        foreach ($courses as $course) {
            course_archiver::add_to_queue($course);
            mtrace('Adding course to queue '.$course->shortname);
        }
        
        // Now start the process c.id, c.category, c.shortname, c.visible 
        $sql = "SELECT c.*
                  FROM {course} c
                  JOIN {course_archiver} ca
                    ON ca.courseid = c.id
                 WHERE ca.status != :error
                   AND ca.timequeued <= :expired";

        $expired = ($config->graceperiod) ? $config->graceperiod + time() : time();
        $params = array('error'=>course_archiver::STATUS_ERROR, 'expired'=>$expired);
        $courses = $DB->get_records_sql($sql, $params, 0, course_archiver::PROCESS_LIMIT);
        $processed = 0;
        if ($courses) {
            if ($timeoverride) {
                $processingtime = $timeoverride;
            } else {
                $processingtime = isset($config->processingtime) ? $config->processingtime : 60;
            }
            $stoptime = time() + $processingtime;
            while (time() < $stoptime and $courses) {
                $processed++;
                $course = array_shift($courses);
                $filename = course_archiver::generate_filename($course);
                $result = course_archiver::backup_course($course, $filename);
                mtrace('Backup course '.$course->shortname. ' to file '.$filename.'... ');
                if (!$result) {
                    $DB->set_field('course_archiver', 'status', course_archiver::STATUS_ERROR, array('courseid'=>$course->id));
                    mtrace('ERROR');
                    continue;
                } else {
                    mtrace('OK');
                }
                mtrace('Deleting course '.$course->shortname.'... ');
                $result = course_archiver::delete_course($course);
                if (!$result) {
                    $DB->set_field('course_archiver', 'status', course_archiver::STATUS_ERROR, array('courseid'=>$course->id));
                    mtrace('ERROR');
                    continue;
                } else {
                    $DB->delete_records('course_archiver', array('courseid'=>$course->id));
                    $completed++;
                    mtrace('OK');
                }
           }
        
        }

        fix_course_sortorder();

        $DB->execute("DELETE
                        FROM {backup_controllers}
                       WHERE purpose = ?", array(course_archiver::MODE_ARCHIVE));
        mtrace("Deleted course archiver backup controllers");

        $difftime = microtime_diff($starttime, microtime());
        mtrace('Processed: '.$processed);
        mtrace('Completed: '.$completed);
        mtrace('Execution took '.$difftime.' seconds');
        
        
        course_archiver::set_state_running(false);
        return true;
    }

    /**
     *
     * @param stdClass $course
     * @param string $filename
     * @return boolean $result
     * @throws moodle_exception
     */
    public static function backup_course(stdClass $course, $filename) {
        global $CFG;

        $outcome = false;
        $config = course_archiver::get_config();
        $admin = get_admin();
        
        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, course_archiver::MODE_ARCHIVE, $admin->id);
        try {
            
            if (!is_writable($config->mbzstoredirectory)) {
                throw new moodle_exception('mbz store directory not writable');
            }
            // Everything we need
            $settings = array(
                'users' => true,
                'role_assignments' => true,
                'activities' => true,
                'blocks' => true,
                'filters' => true,
                'comments' => true,
                'completion_information' => true,
                'logs' => true,
                'histories' => true
            );
            foreach ($settings as $setting => $value) {
                if ($bc->get_plan()->setting_exists($setting)) {
                    $bc->get_plan()->get_setting($setting)->set_value($value);
                }
            }
            
            $bc->set_status(backup::STATUS_AWAITING);

            $bc->execute_plan();
            
            $results = $bc->get_results();
            
            $file = $results['backup_destination']; // may be empty if file already moved to target location
            if ($file) {
                $outcome = $file->copy_content_to($config->mbzstoredirectory.'/'.$filename);
                if ($outcome) {
                    $file->delete();
                }
            }
        } catch (Exception $e) {   
            $bc->log('backup_auto_failed_on_course', backup::LOG_ERROR, $course->shortname); // Log error header.
            $bc->log('Exception: ' . $e->errorcode, backup::LOG_ERROR, $e->a); // Log original exception problem.
            $outcome = false;
            // clean up
            backup_controller_dbops::drop_backup_ids_temp_table($bc->get_backupid());
            if (empty($CFG->keeptempdirectoriesonbackup)) {
                backup_helper::delete_backup_dir($bc->get_backupid()); // Empty backup dir
            }
        }
        $bc->destroy();
        unset($bc);

        return $outcome;
    }

    public static function delete_course(stdClass $course) {
        $outcome = true;
        try {

            delete_course($course);

        } catch (Exception $e) {
            mtrace('Exception: ' . $e->errorcode);
            $outcome = false;
        }
        return $outcome;
    }

    /**
     *
     * @global moodle_database $DB
     * @param stdClass $course
     * @return type
     * @throws coding_exception 
     */
    public static function generate_filename(stdClass $course) {
        global $DB;

        $shortname = '';
        if (!isset($course->shortname)) {
            throw new coding_exception('shortname not found in object');
        }
        $shortname = $course->shortname;
        $context   = context_course::instance($course->id);
        $shortname = format_string($shortname, true, array('context'=>$context));
        $shortname = str_replace(' ', '_', $shortname);
        $shortname = textlib::strtolower(trim(clean_filename($shortname), '_'));
        // Calculate date
        $backupdateformat = str_replace(' ', '_', get_string('backupnameformat', 'langconfig'));
        $date = userdate(time(), $backupdateformat, 99, false);
        $date = textlib::strtolower(trim(clean_filename($date), '_'));

        return $shortname . '-' . $date . '.mbz';
    }
    /**
     * Gets the process state of the of archiver.
     *
     * @global moodle_database $DB
     * @return int One of course_archiver::STATE_*
     */
    public static function get_state() {
        global $DB;

        $config = course_archiver::get_config();
        
        if (empty($config->enabled)){
            return course_archiver::STATE_DISABLED;
        }
        
        if (empty($config->archivecategory)){
            return course_archiver::STATE_DISABLED;
        }
        
        if (!is_dir($config->mbzstoredirectory)){
            return course_archiver::STATE_DISABLED;
        }
        
        if (!empty($config->isrunning)) {
            // Detect if the isrunning semaphore is a valid one
            // by looking for recent activity in the backup_controllers table
            // for backups of type course_archiver::MODE_ARCHIVE
            $timetosee = 60 * 90; // Time to consider in order to clean the semaphore
            $params = array('purpose'=> course_archiver::MODE_ARCHIVE, 'timetolook'=>(time() - $timetosee));
            if ($DB->record_exists_select('backup_controllers',
                "operation = 'backup' AND type = 'course' AND purpose = :purpose AND timemodified > :timetolook", $params)) {
                return course_archiver::STATE_RUNNING; // Recent activity found, still running
            } else {
                // No recent activity found, let's clean the semaphore
                mtrace('No automated archiver activity found in last ' . (int)$timetosee/60 . ' minutes. Cleaning running status');
                course_archiver::set_state_running(false);
            }
        }
        return course_archiver::STATE_READY;
    }
    /**
     * Sets the state of the automated archiver system.
     *
     * @param bool $running
     * @return bool
     */
    public static function set_state_running($running = true) {
        if ($running === true) {
            if (course_archiver::get_state() === course_archiver::STATE_RUNNING) {
                throw new moodle_exception('Course archiver_already_running');
            }
            set_config('isrunning', '1', 'local_coursearchiver');
        } else {
            unset_config('isrunning', 'local_coursearchiver');
        }
        return true;
    }
}
