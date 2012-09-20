<?php

/**
 * @author David Vega M.
 * @package moodle uow user
 *
 * User Import Plugin: Import Users unsupervised
 * 
 * It follows the same style of import as the 
 * admin/uploaduser.php page but it is design 
 * to work unsupervised and in regular basis 
 * i.e. as a job.  
 * 
 * It works by reading all the files in the 
 * configured path (CP) and trying to upload 
 * users from them.  Once the files are 
 * processed (either on success or failure) it 
 * will move them to a CP subfolder (processed)
 * clearing CP and ready for the next round.  
 *
 *
 * 2007-09-11  File created.
 * 2012-09-19  Updated for 2.2.x Troy Williams
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/textlib.class.php');
require_once($CFG->dirroot .'/local/logger/logger.php');

define('LINE_MAX_SIZE', 1024);

/**
 * Upload/update users from a csv file in the same way as the Upload users
 * form (admin/uploaduser.php)
 *
 * @param string  $filename       - The path (best if absolute) of the csv file to import
 * @param boolean $createpassword - Whether to create a password (true) if is none in the file or if it is a required field (false)
 * @param boolean $updateaccounts - If true it will update existing accounts with the info in the file otherwise it will ignore it
 * @param boolean $allowrenames   - Whether it allow renaming a user (true will rename, false will ignore changes)
 */
function user_load_users_from_file($filename, $createpassword=false, $updateaccounts=false, $allowrenames=false, $skipduplicates=false, $defaultvalues=null) {
    global $CFG, $DB;

    $textlib = textlib_get_instance();
    $csv_delimiter = isset($CFG->CSV_DELIMITER) ? $CFG->CSV_DELIMITER : ',';
    $csv_encode = '&#' . (isset($CFG->CSV_ENCODE) ? $CFG->CSV_ENCODE : ord($csv_delimiter));

    //  Initialise the logger
    $logger = logger_get_logger('user_import', 'user_load_users_from_file');

    // // //-->  The code below cames from uploaduser.php  <--\\ \\ \\

    // make arrays of valid fields for error checking
    // the value associated to each field is: 0 = optional field, 1 = field required either in default values or in data file
    $fields = array(
        'firstname' => 1,
        'lastname' => 1,
        'username' => 1,
        'email' => 1,
        'city' => 1,
        'country' => 1,
        'lang' => 1,
        'auth' => 1,
        'timezone' => 1,
        'mailformat' => 1,
        'maildisplay' => 1,
        'htmleditor' => 1,
        'autosubscribe' => 1,
        'trackforums' => 0,
        'mnethostid' => 0,
        'institution' => 0,
        'department' => 0,
        'idnumber' => 0,
        'icq' => 0,
        'phone1' => 0,
        'phone2' => 0,
        'address' => 0,
        'url' => 0,
        'description' => 0,
        'icq' => 0,
        'oldusername' => 0,
        'emailstop' => 1,
        'deleted' => 0,
        'password' => !$createpassword,
    );

    $fp = fopen($filename, 'r');
    $linenum = 1; // since header is line 1
    // get header (field names) and remove Unicode BOM from first line, if any
    $line = explode($csv_delimiter, $textlib->trim_utf8_bom(fgets($fp,LINE_MAX_SIZE)));
    // check for valid field names
    $headers = array();
    foreach ($line as $key => $value) {
        $value = trim($value); // remove whitespace
        if (!in_array($value, $fields) && // if not a standard field and not an enrolment field, then we have an error
            !preg_match('/^course\d+$/', $value) && !preg_match('/^group\d+$/', $value) &&
            !preg_match('/^type\d+$/', $value) && !preg_match('/^role\d+$/', $value)) {
            $logger->error(get_string('invalidfieldname', 'error', $value));
        }
        $headers[$key] = $value;
    }

    // check that required fields are present or a default value for them exists
    $headersOk = true;
    // disable the check if we also have deleting information (ie. deleted column)
    if (!in_array('deleted', $headers)) {
        foreach ($fields as $key => $required) {
            if($required && !in_array($key, $headers) && (!isset($defaultvalues[$key]) || $defaultvalues[$key]==='')) {
                $logger->error(get_string('missingfield', 'error', $key));
                $headersOk = false;
            }
        }
    }
    if($headersOk) {
        $usersnew     = 0;
        $usersupdated = 0;
        $userserrors  = 0;
        $usersdeleted = 0;
        $renames      = 0;
        $renameerrors = 0;
        $deleteerrors = 0;
        $newusernames = array();
        // We'll need courses a lot, so fetch it early and keep it in memory, indexed by their shortname
        $tmp =& get_courses('all','','c.id,c.shortname,c.visible');
        $courses = array();
        foreach ($tmp as $c) {
            $courses[$c->shortname] = $c;
        }
        unset($tmp);

        while (!feof ($fp)) {
            $errors = '';
            $user = new object();
            // by default, use the local mnet id (this may be changed in the file)
            $user->mnethostid = $CFG->mnet_localhost_id;
            $line = explode($csv_delimiter, utf8_encode(fgets($fp,LINE_MAX_SIZE)));

            if (count($line) == 1) {
                continue;
            }

            ++$linenum;
            // add fields to user object
            foreach ($line as $key => $value) {
                if($value !== '') {
                    $key = $headers[$key];
                    //decode encoded commas
                    $value = str_replace($csv_encode,$csv_delimiter,trim($value));
                    // special fields: password and username
                    if ($key == 'password' && !empty($value)) {
                        $user->$key = hash_internal_user_password($value);
                    } else if($key == 'username') {
                        $value = $textlib->strtolower(addslashes($value));
                        if(empty($CFG->extendedusernamechars)) {
                            $value = eregi_replace('[^(-\.[:alnum:])]', '', $value);
                        }
                        @$newusernames[$value]++;
                        $user->$key = $value;
                    } else {
                        $user->$key = addslashes($value);
                    }
                }
            }

            // add default values for remaining fields
            foreach ($fields as $key => $required) {
                if(isset($user->$key)) {
                    continue;
                }
                if(!isset($defaultvalues[$key]) || $defaultvalues[$key]==='') { // no default value was submited
                    // if the field is required, give an error only if we are adding the user or deleting a user with unkown username
                    if($required && (empty($user->deleted) || $key == 'username')) {
                        $errors .= get_string('missingfield', 'error', $key) . ' ';
                    }
                    continue;
                }
                // process templates
                $template = $defaultvalues[$key];
                $templatelen = strlen($template);
                $value = '';
                for ($i = 0 ; $i < $templatelen; ++$i) {
                    if($template[$i] == '%') {
                        $case = 0; // 1=lowercase, 2=uppercase
                        $len = 0; // number of characters to keep
                        $info = null; // data to process
                        for($j = $i + 1; is_null($info) && $j < $templatelen; ++$j) {
                            $car = $template[$j];
                            if ($car >= '0' && $car <= '9') {
                                $len = $len * 10 + (int)$car;
                            } else if($car == '-') {
                                $case = 1;
                            } else if($car == '+') {
                                $case = 2;
                            } else if($car == 'f') { // first name
                                $info = @$user->firstname;
                            } else if($car == 'l') { // last name
                                $info = @$user->lastname;
                            } else if($car == 'u') { // username
                                $info = @$user->username;
                            } else if($car == '%' && $j == $i+1) {
                                $info = '%';
                            } else { // invalid character
                                $info = '';
                            }
                        }
                        if($info==='' || is_null($info)) { // invalid template
                            continue;
                        }
                        $i = $j - 1;
                        // change case
                        if($case == 1) {
                            $info = $textlib->strtolower($info);
                        } else if($case == 2) {
                            $info = $textlib->strtoupper($info);
                        }
                        if($len) { // truncate data
                            $info = $textlib->substr($info, 0, $len);
                        }
                        $value .= $info;
                    } else {
                        $value .= $template[$i];
                    }
                }

                if($key == 'username') {
                    $value = $textlib->strtolower($value);
                    if(empty($CFG->extendedusernamechars)) {
                        $value = eregi_replace('[^(-\.[:alnum:])]', '', $value);
                    }
                    @$newusernames[$value]++;
                    // check for new username duplicates
                    if($newusernames[$value] > 1) {
                        if($skipduplicates) {
                            $errors .= get_string('duplicateusername', 'error') . ' (' . stripslashes($value) . '). ';
                            continue;
                        } else {
                            $value .= $newusernames[$value];
                            $logger->warning("Found duplicate username: $value, but added to the system any ways");
                        }
                    }
                }
                $user->$key = $value;
            }
            if($errors) {
                $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . $errors);
                ++$userserrors;
                continue;
            }

            // delete user
            if(@$user->deleted) {
                $info = ': ' . $user->username . '. ';
                if($user = $DB->get_record('user', array('username'=>$user->username, 'mnethostid'=>$user->mnethostid))) {
                    $user->timemodified = time();
                    $user->username     = $user->email . $user->timemodified;  // Remember it just in case
                    $user->deleted      = 1;
                    $user->email        = '';    // Clear this field to free it up
                    $user->idnumber     = '';    // Clear this field to free it up
                    if ($DB->update_record('user', $user)) {
                        // not sure if this is needed. unenrol_student($user->id);  // From all courses
                        $DB->delete_records('role_assignments', array('userid'=>$user->id)); // unassign all roles
                        // remove all context assigned on this user?
                        $logger->fine(get_string('userdeleted', 'local_userimport') . $info);
                        ++$usersdeleted;
                    } else {
                        $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('usernotdeletederror', 'error') . $info);
                        ++$deleteerrors;
                    }
                } else {
                    $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('usernotdeletedmissing', 'error') . $info);
                    ++$deleteerrors;
                }
                continue;
            }

            // save the user to the database
            $user->confirmed = 1;
            $user->timemodified = time();

            // before insert/update, check whether we should be updating an old record instead
            if ($allowrenames && !empty($user->oldusername) ) {
                $user->oldusername = $textlib->strtolower($user->oldusername);
                $info = ': ' . stripslashes($user->oldusername) . '-->' . stripslashes($user->username) . '. ';
                if ($olduser = $DB->get_record('user', array('username'=>$user->oldusername, 'mnethostid'=>$user->mnethostid))) {
                    if ($DB->set_field('user', 'username', $user->username, array('id'=>$olduser->id))) {
                        $logger->fine(get_string('userrenamed', 'local_userimport') . $info);
                        $renames++;
                    } else {
                        $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('usernotrenamedexists', 'error') . $info);
                        $renameerrors++;
                        continue;
                    }
                } else {
                    $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('usernotrenamedmissing', 'error') . $info);
                    $renameerrors++;
                    continue;
                }
            }

            // save the information
            $olduser = $DB->get_record('user', array('username' => $user->username, 'mnethostid' => $user->mnethostid));
            if ($olduser) {
                $user->id = $olduser->id;
                $info = ': ' . stripslashes($user->username) .' (ID = ' . $user->id . ')';
                if ($updateaccounts) {
                    // Record is being updated
                    if ($DB->update_record('user', $user)) {
                        $logger->fine(get_string('useraccountupdated', 'local_userimport') . $info);
                        $usersupdated++;
                    } else {
                        $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('usernotupdatederror', 'error') . $info);
                        $userserrors++;
                        continue;
                    }
                } else {
                    //Record not added - user is already registered
                    //In this case, output userid from previous registration
                    //This can be used to obtain a list of userids for existing users
                    $logger->warning(get_string('usernotaddedregistered', 'error') . $info);
                    $userserrors++;
                }
            } else { // new user
                if ($user->id = $DB->insert_record('user', $user)) {  
                    $info = ': ' . stripslashes($user->username) .' (ID = ' . $user->id . ')';
                    $logger->fine(get_string('newuser') . $info);
                    $usersnew++;
                    if (empty($user->password) && $createpassword) {
                        // passwords will be created and sent out on cron
                        $DB->insert_record('user_preferences', array( 'userid' => $user->id, 'name'   => 'create_password', 'value'  => 1));
                        $DB->insert_record('user_preferences', array( 'userid' => $user->id, 'name'   => 'auth_forcepasswordchange', 'value'  => 1));
                    }
                } else {
                    // Record not added -- possibly some other error
                    $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('usernotaddederror', 'error') . ': ' . stripslashes($user->username));
                    $userserrors++;
                    continue;
                }
            }

            // find course enrolments, groups and roles/types
            for($ncourses = 1; $addcourse = @$user->{'course' . $ncourses}; ++$ncourses) {
                // find course
                if(!$course = @$courses[$addcourse]) {
                    $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('unknowncourse', 'error', $addcourse));
                    continue;
                }
                // find role
                if ($addrole = @$user->{'role' . $ncourses}) {
                    $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
                    if (!$ok = role_assign($addrole, $user->id, 0, $coursecontext->id)) {
                        $logger->warning('-->' . get_string('cannotassignrole', 'error'));
                    }
                } else {
                    // if no role, then find "old" enrolment type
                    switch ($addtype = @$user->{'type' . $ncourses}) {
                        case 2:   // teacher
                            $ok = add_teacher($user->id, $course->id, 1);
                            break;
                        case 3:   // non-editing teacher
                            $ok = add_teacher($user->id, $course->id, 0);
                            break;
                        case 1:   // student
                        default:
                            $ok = enrol_student($user->id, $course->id);
                            break;
                    }
                }
                if ($ok) {   // OK
                    $logger->fine('-->' . get_string('enrolledincourse', '', $addcourse));
                } else {
                    $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('enrolledincoursenot', '', $addcourse));
                }

                // find group to add to
                if ($addgroup = @$user->{'group' . $ncourses}) {
                    if ($gid = groups_get_group_by_name($course->id, $addgroup)) {
                        $coursecontext =& get_context_instance(CONTEXT_COURSE, $course->id);
                        if (count(get_user_roles($coursecontext, $user->id))) {
                            if (groups_add_member($gid, $user->id)) {
                                $logger->fine('-->' . get_string('addedtogroup','',$addgroup));
                            } else {
                                $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('addedtogroupnot','',$addgroup));
                            }
                        } else {
                            $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('addedtogroupnotenrolled','',$addgroup));
                        }
                    } else {
                        $logger->warning(get_string('erroronline', 'error', $linenum). ': ' . get_string('groupunknown','error',$addgroup));
                    }
                }
            }
        }

        $summary = get_string('userscreated', 'local_userimport') . ": $usersnew<br />" . get_string('usersupdated', 'local_userimport') . ": $usersupdated<br />" . get_string('usersdeleted', 'local_userimport') . ": $usersdeleted<br />"  . get_string('deleteerrors', 'local_userimport') . ": $deleteerrors";

        if ($allowrenames) {
            $summary .= '<br />'. get_string('usersrenamed', 'local_userimport') .": $renames<br />". get_string('renameerrors', 'local_userimport') .": $renameerrors";
        }

        $logger->fine($summary);
    }

    fclose($fp);

    return $headersOk;
}

/**
 * It attach prefix to the file name and it moves it to
 * a processed folder in the same path of file.  If the
 * processed folder does not exist it will try to create
 * it.
 *
 * @param string  $filename - The path (best if absolute) of the file rename and move
 * @param string  $prefix   - The prefix to attach to the file's name
 */

function user_move_import_file($filename, $prefix) {

    $logger =& logger_get_logger('user_import', 'user_move_import_file');

    //  Get the path of the processed folder
    $pathparts = pathinfo($filename);
    $processdir = $pathparts['dirname'] . DIRECTORY_SEPARATOR . 'processed';

    //  Create the processed folder (if it doesn't exist already)
    //  TODO: Check for permissions
    if (!is_dir($processdir)) {
        mkdir($processdir);
        $logger->info("Create process folder at $processdir");
    }

    //  Delete file if all ok or move and rename if not
    if ($prefix == '') {
        unlink($filename);
        $logger->info('Import file has been deleted');
    }
    else {
        rename($filename, $processdir . DIRECTORY_SEPARATOR . $prefix . $pathparts['basename']);
        $logger->info('Moved import file to '. $processdir . DIRECTORY_SEPARATOR . $prefix . $pathparts['basename']);
    }
}

/**
 * Searches for files to upload and calls
 * user_load_users_from_file for each of them
 */
function user_import_users() {
    global $CFG;

    //  Initialise the logger
    $logger =& logger_get_logger('user_import', 'user_import_users');

    //  Init vars
    $processfiles   = 0;
    $status         = false;
    $prefix         = '';
    $importdir      = get_config(null, 'userimport_repodir');
    $createpassword = null;
    $updateaccounts = null;
    $allowrenames   = null;
    $skipduplicates = null;
    $defaultvalues  = null;

    //  Make sure we have the right path to search
    if (($importdir != null) && is_dir($importdir)) {

        $files     = scandir($importdir);
        $importdir = rtrim($importdir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;


        if (count($files)) {
            foreach ($files as $filename) {

                if (is_file($importdir . $filename)) {
                    //  Make sure that it is a file and not a folder
                    $processfiles++;

                    if ($processfiles == 1) {
                        //  If we do have files to load, get the configuration variables
                        $defaultvalues  = array();
                        $createpassword = get_config(null, 'userimport_createpassword');
                        $updateaccounts = get_config(null, 'userimport_updateaccounts');
                        $allowrenames   = get_config(null, 'userimport_allowrenames');
                        $skipduplicates = get_config(null, 'userimport_duplicatehandling');
                        $logger->info("Import config parameters:<br />Import directory: $importdir<br />Create password? $createpassword<br />Update existing accounts? $updateaccounts<br />Allow renames? $allowrenames<br />Handle duplicates $skipduplicates");

                        $defaultArr = array('username','auth','email','maildisplay','emailstop','mailformat','autosubscribe','trackforums','htmleditor','city','country','timezone','lang','url','institution','department','phone1','phone2','address');

                        foreach($defaultArr as $field) {
                           $defaultvalues[$field] =  get_config(null, "userimport_$field");
                        }


                    }

                    $logger->fine("Processing file $filename for user imports...");

                    //  Upload the users in the file
                    $status = user_load_users_from_file($importdir . $filename, $createpassword, $updateaccounts, $allowrenames, $skipduplicates, $defaultvalues);

                    if ($status) {
                        $prefix = '';
                        $logger->fine("The file $filename finish to process ok");
                    }
                    else {
                        //  If an error occurred add a err_ prefix to the file
                        $prefix = 'err_';
                        $logger->error("The file $filename could not be processed");
                    }

                    //  Move the file to the process folder
                    user_move_import_file($importdir . $filename, $prefix);
                }
            }

            if ($processfiles > 0) {
                $logger->fine("$processfiles file(s) processed");
            }
            else {
                $logger->fine('No files to be imported!');
            }
        }
    }
    else {
        $logger->error("The supplied import repository ($importdir) is not a valid path");
        return false;
    }

    return true;
}

?>
