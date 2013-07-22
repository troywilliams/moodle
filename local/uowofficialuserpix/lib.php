<?php

/**
 * @author David Vega M.
 * @package moodle uow userpix
 *
 * Userpix Plugin: Import User Image
 *
 * This class is a collection of functions 
 * that admin the import process.
 *
 * 2007-07-31  File created.
 * 
 * @uses $CFG
 * @uses lib/gdlib
 * @uses lib/dmlib 
 * @uses uow/logger
 * 
 * 
 * TODO: We need a way to check if the image have change in the repository and then
 *       upload the new image to moodle
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir  .'/gdlib.php');
require_once($CFG->dirroot .'/local/logger/logger.php');

class userpix_import {
    
    /**
     * It loads an image for a moodle user by passing the user id and the file name.
     * @param   int      $userid    The id of the user that the image will be attach against
     * @param   string   $filename  The pull path of the file
     * @return  boolean  True if the image is import successfully, false otherwise. 
     */    
    public static function load_user_image($userid, $filename) {
        global $CFG, $DB;
        $import = false;
        $logger =& logger_get_logger('userpix_import', 'load_user_image');        //  Initialise the logger
                  
        //  Make sure that the targer file exist
        if (is_file($filename)) {

            if (!empty($CFG->gdversion) && empty($CFG->disableuserimages)) {
                //  The process_new_icon (lib/gdlib) does all the work for us
                $context = context_user::instance($userid, MUST_EXIST);
                if (process_new_icon($context, 'user', 'icon', 0, $filename)) {
                    $DB->set_field('user', 'picture', 1, array('id'=>$userid));
                    $import = true;
                }
                else {
                    $logger->info("Fail to run gdlib/save_profile_image for user-id: $userid and file: $filename"); 
                }
            }
            else {
                $logger->info('Global setting for gdversion or disableuserimages have been desabled');
            }
        }
        else {
            $logger->info("File: $filename was not found");
        }
        return $import;
    }
    
    /**
     * It loads an image for a moodle user by passing the id number and the file name.
     * @param   int      $idnumber  The id number of the user that the image will be attach against
     * @param   string   $filename  The original name of the file as it is in the repository
     * @return  boolean  True if the image is import successfully, false otherwise. 
     */    
    public static function load_participant_image($idnumber, $filename) {
        global $CFG, $DB;
        $import = false;
        
        //  Get the user record by idnumber
        if (! $user = $DB->get_record('user', array('idnumber' => $idnumber))) {
            return false;
        }
        
        //  Load the image
        $import = userpix_import::load_user_image($user->id, $filename);
        
        return $import;
    }
    
    /**
     * It loads images for all users that don't already have an image.  This function assumes
     * that the file name for the image is the same as user's id number and that the file 
     *  has a ".jpg" extesion.
     * 
     * @return  boolean  True if all files were uploaded successfully, false otherwise. 
     */
    public static function load_all_user_images() {
        global $CFG, $DB;
        
        //  Initialise the logger
        $logger =& logger_get_logger('userpix_import', 'load_all_user_images');

        $roleid = $DB->get_field('role', 'id', array('shortname' => 'student'));

        //  If you only want to get images for students
        $sql = "SELECT DISTINCT u.id AS id, u.idnumber AS idnumber 
                FROM {user} u
                    INNER JOIN {role_assignments} ra
                    ON u.id = ra.userid
                    WHERE ra.roleid = $roleid AND u.picture = 0";
        
        //  This one if you want to get pictures for all users
        if (get_config(null, 'userpiximport_all') == 1) {
            $sql = "SELECT id, idnumber
                    FROM {user}
                    WHERE picture = 0";

            $logger->info('Load images for ALL users (including staff)');
        }        
        
        $userlist = $DB->get_records_sql($sql);
        $didimport = true;
        
        if (!is_array($userlist) || !count($userlist)) {
            $logger->fine('No images to import');
            return true;
        }
        
        if (! $repodir = get_config(null, 'userpiximport_repodir')) {
            $logger->error('No image repository configured.  Under the Site Administration menu go to User->Userpix Import and enter an address to the image repository');
            return false;
        }
        else {
            $repodir = rtrim($repodir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (!file_exists($repodir)) {
                $logger->error('Can\'t access image repository configured at: ' . $repodir);
                return false;
            }
            $logger->info('Imager repository: '. $repodir);
        }
        
        $logger->fine('Number of images to import: '. count($userlist));

        if (count($userlist)) {
            foreach ($userlist as $user) {
                $subdir = substr($user->idnumber, 0, 2);
                if ($user->idnumber == '') {
                    $didimport = false;
                    $logger->info("Fail to load image for user-id: $user->id, missing student-id");
                } else if (!userpix_import::load_user_image($user->id, $repodir . $subdir . DIRECTORY_SEPARATOR . $user->idnumber .'.jpg')) {
                    $didimport = false;
                    $logger->warning("Fail to load image for user-id: $user->id, student-id: $user->idnumber"); 
                } else {
                    $logger->fine("Image import successfully for user-id: $user->id, student-id: $user->idnumber");
                }                        
            }
        }

        return $didimport;
    }
}  // end of userpix_import Class
