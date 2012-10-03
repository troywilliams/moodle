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
 * Library of interface functions and constants for module pcast
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the pcast specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod_pcast
 * @copyright 2010 Stephen Bourget
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define("PCAST_SHOW_ALL_CATEGORIES", 0);
define("PCAST_SHOW_NOT_CATEGORISED", -1);

define("PCAST_NO_VIEW", -1);
define("PCAST_STANDARD_VIEW", 0);
define("PCAST_CATEGORY_VIEW", 1);
define("PCAST_DATE_VIEW", 2);
define("PCAST_AUTHOR_VIEW", 3);
define("PCAST_ADDENTRY_VIEW", 4);
define("PCAST_APPROVAL_VIEW", 5);
define("PCAST_ENTRIES_PER_PAGE", 20);

define("PCAST_DATE_UPDATED", 100);
define("PCAST_DATE_CREATED", 101);
define("PCAST_AUTHOR_LNAME", 200);
define("PCAST_AUTHOR_FNAME", 201);

define("PCAST_EPISODE_VIEW", 300);
define("PCAST_EPISODE_COMMENT_AND_RATE", 301);
define("PCAST_EPISODE_VIEWS", 302);


/** example constant */
//define('PCAST_ULTIMATE_ANSWER', 42);

/**
 * If you for some reason need to use global variables instead of constants, do not forget to make them
 * global as this file can be included inside a function scope. However, using the global variables
 * at the module level is not a recommended.
 */
//global $PCAST_GLOBAL_VARIABLE;
//$PCAST_QUESTION_OF = array('Life', 'Universe', 'Everything');

/**
 * Lists supported features
 *
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 **/
function pcast_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_RATE:                    return true;

        default: return null;
    }

}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $pcast An object from the form in mod_form.php
 * @global stdClass $DB
 * @global stdClass $USER
 * @return int The id of the newly inserted pcast record
 */
function pcast_add_instance($pcast) {
    global $DB, $USER;

    $pcast->timecreated = time();

    // If it is a new instance time created is the same as modified
    $pcast->timemodified = $pcast->timecreated;

    // Handle ratings
    if (empty($pcast->assessed)) {
        $pcast->assessed = 0;
    }

    if (empty($pcast->ratingtime) or empty($pcast->assessed)) {
        $pcast->assesstimestart  = 0;
        $pcast->assesstimefinish = 0;
    }

    // If no owner then set it to the instance creator.
    if (isset($pcast->enablerssitunes) and ($pcast->enablerssitunes == 1)) {
        if (!isset($pcast->userid)) {
            $pcast->userid = $USER->id;
        }
    }

    // Get the episode category information
    $defaults->topcategory = 0;
    $defaults->nestedcategory = 0;
    $pcast = pcast_get_itunes_categories($pcast, $defaults);

    # You may have to add extra stuff in here #

    $result = $DB->insert_record('pcast', $pcast);

    $cmid = $pcast->coursemodule;
    $draftitemid = $pcast->image;
    // we need to use context now, so we need to make sure all needed info is already in db
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_pcast', 'logo', 0, array('subdirs'=>false));
    }

    return $result;

}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $pcast An object from the form in mod_form.php
 * @global stdClass $DB
 * @global stdClass $USER
 * @return boolean Success/Fail
 */
function pcast_update_instance($pcast) {
    global $DB, $USER;

    $pcast->timemodified = time();

    // Handle ratings
    if (empty($pcast->assessed)) {
        $pcast->assessed = 0;
    }

    if (empty($pcast->ratingtime) or empty($pcast->assessed)) {
        $pcast->assesstimestart  = 0;
        $pcast->assesstimefinish = 0;
    }

    $pcast->id = $pcast->instance;

    // If no owner then set it to the instance creator.
    if (isset($pcast->enablerssitunes) and ($pcast->enablerssitunes == 1)) {
        if (!isset($pcast->userid)) {
            $pcast->userid = $USER->id;
        }
    }

    // Get the episode category information
    $defaults->topcategory = 0;
    $defaults->nestedcategory = 0;
    $pcast = pcast_get_itunes_categories($pcast, $defaults);

    # You may have to add extra stuff in here #

    $result = $DB->update_record('pcast', $pcast);

    $cmid = $pcast->coursemodule;
    $draftitemid = $pcast->image;
    // we need to use context now, so we need to make sure all needed info is already in db
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_pcast', 'logo', 0, array('subdirs'=>false));
    }

    return $result;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @global stdClass $DB
 * @global stdClass $USER
 * @return boolean Success/Failure
 */
function pcast_delete_instance($id) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/rating/lib.php');

    if (! $pcast = $DB->get_record('pcast', array('id' => $id))) {
        return false;
    }
    if (!$cm = get_coursemodule_from_instance('pcast', $id)) {
        return false;
    }
    if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
        return false;
    }

    # Delete any dependent records here #

    // Delete Comments
    $episode_select = "SELECT id FROM {pcast_episodes} WHERE pcastid = ?";
    $DB->delete_records_select('comments', "contextid=? AND commentarea=? AND itemid IN ($episode_select)", array($id, 'pcast_episode', $context->id));

    // Delete all files
    $fs = get_file_storage();
    $fs->delete_area_files($context->id);

    //delete ratings
    $rm = new rating_manager();
    $ratingdeloptions = new stdClass();
    $ratingdeloptions->contextid = $context->id;
    $rm->delete_ratings($ratingdeloptions);

    //Delete Views
    $episode_select = "SELECT id FROM {pcast_episodes} WHERE pcastid = ?";
    $DB->delete_records_select('pcast_views', "episodeid  IN ($episode_select)", array($pcast->id));

    //Delete Episodes
    $DB->delete_records('pcast_episodes', array('pcastid' => $pcast->id));

    //Delete Podcast
    $DB->delete_records('pcast', array('id' => $pcast->id));

    return true;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @global stdClass $DB
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $pcast
 * @return object $result
 */
function pcast_user_outline($course, $user, $mod, $pcast) {

    global $DB;

    if ($logs = $DB->get_records("log", array('userid'=>$user->id, 'module'=>'pcast',
                                              'action'=>'view', 'info'=>$pcast->id), "time ASC")) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new object();
        $result->info = get_string("numviews", "", $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return null;

}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @global stdClass $DB
 * @global stdClass $CFG
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $pcast
 * @return object $result
 */
function pcast_user_complete($course, $user, $mod, $pcast) {
    global $CFG, $DB;

    if ($logs = $DB->get_records("log", array('userid'=>$user->id, 'module'=>'pcast',
                                              'action'=>'view', 'info'=>$pcast->id), "time ASC")) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string("mostrecently");
        $strnumviews = get_string("numviews", "", $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string("noviews", "pcast");
    }

}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in pcast activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @global stdClass $CFG
 * @global stdClass $USER
 * @global stdClass $DB
 * @global stdClass $OUTPUT
 * @param stdClass $course
 * @param bool $viewfullnames
 * @param int $timestart
 * @return bool 
 */

function pcast_print_recent_activity($course, $viewfullnames, $timestart) {

    // return false;  //  True if anything was printed, otherwise false

    global $CFG, $USER, $DB, $OUTPUT;

    $modinfo = get_fast_modinfo($course);
    $ids = array();
    foreach ($modinfo->cms as $cm) {
        if ($cm->modname != 'pcast') {
            continue;
        }
        if (!$cm->uservisible) {
            continue;
        }
        $ids[$cm->instance] = $cm->instance;
    }

    if (!$ids) {
        return false;
    }

    $plist = implode(',', $ids); // there should not be hundreds of glossaries in one course, right?

    if (!$episodes = $DB->get_records_sql("SELECT e.id, e.name, e.approved, e.timemodified, e.pcastid,
                                                 e.userid, u.firstname, u.lastname, u.email, u.picture
                                            FROM {pcast_episodes} e
                                            JOIN {user} u ON u.id = e.userid
                                           WHERE e.pcastid IN ($plist) AND e.timemodified > ?
                                        ORDER BY e.timemodified ASC", array($timestart))) {
        return false;
    }

    $editor  = array();

    foreach ($episodes as $episodeid => $episode) {
        if ($episode->approved) {
            continue;
        }

        if (!isset($editor[$episode->pcastid])) {
            $editor[$episode->pcastid] = has_capability('mod/pcast:approve', get_context_instance(CONTEXT_MODULE, $modinfo->instances['pcast'][$episode->pcastid]->id));
        }

        if (!$editor[$episode->pcastid]) {
            unset($episodes[$episodeid]);
        }
    }

    if (!$episodes) {
        return false;
    }
    echo $OUTPUT->heading(get_string('newepisodes', 'pcast').':');

    $strftimerecent = get_string('strftimerecent');
    foreach ($episodes as $episode) {
        $link = new moodle_url('/mod/pcast/showepisode.php', array('eid'=>$episode->id));
        if ($episode->approved) {
            $out = html_writer::start_tag('div', array('class'=>'head')). "\n";
        } else {
            $out = html_writer::start_tag('div', array('class'=>'head dimmed_text')). "\n";
        }

        $out .= html_writer::start_tag('div', array('class'=>'date')). "\n";
        $out .= userdate($episode->timemodified, $strftimerecent);
        $out .= html_writer::end_tag('div') . "\n";
        $out .= html_writer::start_tag('div', array('class'=>'name')). "\n";
        $out .= fullname($episode, $viewfullnames);
        $out .= html_writer::end_tag('div') . "\n";
        $out .= html_writer::end_tag('div') . "\n";
        $out .= html_writer::start_tag('div', array('class'=>'info')). "\n";
        $out .= html_writer::tag('a', format_text($episode->name, true), array('href'=>$link));
        $out .= html_writer::end_tag('div') . "\n";

        echo $out;

    }

    return true;
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 **/
function pcast_cron () {
    return true;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of pcast. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $pcastid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function pcast_get_participants($pcastid) {
    global $DB;

    //Get participants
    $participants = $DB->get_records_sql("SELECT DISTINCT u.id, u.id
                                            FROM {user} u, {pcast_episodes} p
                                           WHERE p.pcastid = ? AND u.id = p.userid", array($pcastid));

    //Return participants array (it contains an array of unique users)

    return $participants;
}

/**
 * This function returns if a scale is being used by one pcast
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $pcastid ID of an instance of this module
 * @param int $scaleid
 * @global $DB
 * @return mixed
 */
function pcast_scale_used($pcastid, $scaleid) {
    global $DB;

    $return = false;

    $rec = $DB->get_record("pcast", array("id" => "$pcastid", "scale" => "-$scaleid"));

    if (!empty($rec) && !empty($scaleid)) {
        $return = true;
    }

    return $return;
}

/**
 * Checks if scale is being used by any instance of pcast.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any pcast
 */
function pcast_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('pcast', array('scale'=>-$scaleid)))  {
        return true;
    } else {
        return false;
    }
}


/**
 * Lists all browsable file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array
 */
function pcast_get_file_areas($course, $cm, $context) {
    //$areas = array('pcast_episode','pcast_logo');
    $areas = array();
    $areas['logo']   = get_string('arealogo', 'pcast');
    $areas['episode'] = get_string('areaepisode', 'pcast');

    return $areas;
}

/**
 * Support for the Reports (Participants)
 * @return array()
 */
function pcast_get_view_actions() {
    return array('view', 'view all', 'get attachment');
 }
/**
 * Support for the Reports (Participants)
 * @return array()
 */
function pcast_get_post_actions() {
    return array('add', 'update');
 }

 /**
  * Tells if files in moddata are trusted and can be served without XSS protection.
  *
  * @return bool (true if file can be submitted by teacher only, otherwise false)
  */

function pcast_is_moddata_trusted() {
    return false;
}

/**
 * Adds module specific settings to the navigation block
 * @global stdClass $CFG
 * @param stdClass $navigation
 * @param stdClass $course
 * @param stdClass $module
 * @param stdClass $cm
 */

function pcast_extend_navigation($navigation, $course, $module, $cm) {
    global $CFG;
    $navigation->add(get_string('standardview', 'pcast'), new moodle_url('/mod/pcast/view.php', array('id'=>$cm->id, 'mode'=>PCAST_STANDARD_VIEW)));
    $navigation->add(get_string('categoryview', 'pcast'), new moodle_url('/mod/pcast/view.php', array('id'=>$cm->id, 'mode'=>PCAST_CATEGORY_VIEW)));
    $navigation->add(get_string('dateview', 'pcast'), new moodle_url('/mod/pcast/view.php', array('id'=>$cm->id, 'mode'=>PCAST_DATE_VIEW)));
    $navigation->add(get_string('authorview', 'pcast'), new moodle_url('/mod/pcast/view.php', array('id'=>$cm->id, 'mode'=>PCAST_AUTHOR_VIEW)));
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $pcastnode The node to add module settings to
 */
function pcast_extend_settings_navigation(settings_navigation $settings, navigation_node $pcastnode) {
    global $PAGE, $DB, $CFG, $USER;

    $mode = optional_param('mode', '', PARAM_ALPHA);
    $hook = optional_param('hook', 'ALL', PARAM_CLEAN);
    $group = optional_param('group', '', PARAM_ALPHANUM);

    if (has_capability('mod/pcast:approve', $PAGE->cm->context) && ($hiddenentries = $DB->count_records('pcast_episodes', array('pcastid'=>$PAGE->cm->instance, 'approved'=>0)))) {
        $pcastnode->add(get_string('waitingapproval', 'pcast'), new moodle_url('/mod/pcast/view.php', array('id'=>$PAGE->cm->id, 'mode'=>PCAST_APPROVAL_VIEW)));
    }

    if (has_capability('mod/pcast:write', $PAGE->cm->context)) {
        $pcastnode->add(get_string('addnewepisode', 'pcast'), new moodle_url('/mod/pcast/edit.php', array('cmid'=>$PAGE->cm->id)));
    }

    $pcast = $DB->get_record('pcast', array("id" => $PAGE->cm->instance));

    if (!empty($CFG->enablerssfeeds) && !empty($CFG->pcast_enablerssfeeds)
    && $pcast->rssepisodes) {
        require_once("$CFG->libdir/rsslib.php");

        $string = get_string('rsslink', 'pcast');

        //Sort out groups
        if (is_numeric($group)) {
            $currentgroup = $group;

        } else {
            $groupmode = groups_get_activity_groupmode($PAGE->cm);
            if ($groupmode > 0) {
                $currentgroup = groups_get_activity_group($PAGE->cm);
            } else {
                $currentgroup = 0;
            }

        }
        $args = $pcast->id . '/'.$currentgroup;

        $url = new moodle_url(rss_get_url($PAGE->cm->context->id, $USER->id, 'pcast', $args));
        $pcastnode->add($string, $url, settings_navigation::TYPE_SETTING, null, null, new pix_icon('i/rss', ''));

        
        if (!empty($CFG->pcast_enablerssitunes)) {
            $string = get_string('pcastlink', 'pcast');
            require_once("$CFG->dirroot/mod/pcast/rsslib.php");
            $url = pcast_rss_get_url($PAGE->cm->context->id, $USER->id, 'pcast', $args);
            $pcastnode->add($string, $url, settings_navigation::TYPE_SETTING, null, null, new pix_icon('i/rss', ''));

        }

    }
}


function pcast_get_itunes_categories($item, $pcast) {

    // Split the category info into the top category and nested category
    if (isset($item->category)) {
        $length = strlen($item->category);
        switch ($length) {
            case 4:
                $item->topcategory = substr($item->category, 0, 1);
                $item->nestedcategory = (int)substr($item->category, 1, 3);
                break;
            case 5:
                $item->topcategory = substr($item->category, 0, 2);
                $item->nestedcategory = (int)substr($item->category, 2, 3);
                break;
            case 6:
                $item->topcategory = substr($item->category, 0, 3);
                $item->nestedcategory = (int)substr($item->category, 3, 3);
                break;

            default:
                // SHOULD NEVER HAPPEN
                $item->topcategory = $pcast->topcategory;
                $item->nestedcategory = $pcast->nestedcategory;
                break;
        }
    } else {
        // Will only happen if categories are disabled
        $item->topcategory = $pcast->topcategory;
        $item->nestedcategory = $pcast->nestedcategory;
    }
    return $item;
}

/**
 * Serves all files for the pcast module.
 *
 * @global stdClass $CFG
 * @global stdClass $DB
 * @global stdClass $USER (used only for logging if available)
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - justsend the file
 */
function pcast_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {

    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    if ($filearea === 'episode') {
        $episodeid = (int)array_shift($args);

        if (!$episode = $DB->get_record('pcast_episodes', array('id'=>$episodeid))) {

            return false;
        }

        if (!$pcast = $DB->get_record('pcast', array('id'=>$cm->instance))) {

            return false;
        }

        if ($pcast->requireapproval and !$episode->approved and !has_capability('mod/pcast:approve', $context)) {

            return false;
        }
        $relativepath = implode('/', $args);
        $filecontext = get_context_instance(CONTEXT_MODULE, $cm->id);
        $fullpath = "/$filecontext->id/mod_pcast/$filearea/$episodeid/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {

            return false;
        }

        // Log the file as viewed
        $pcast->URL = $CFG->wwwroot . '/pluginfile.php' . $fullpath;
        $pcast->filename = implode('/', $args);
        if (!empty($USER->id)){
            pcast_add_view_instance($pcast, $USER->id);
        }

        // finally send the file
        send_stored_file($file, 0, 0, $forcedownload); // download MUST be forced - security!

    } else if ($filearea === 'logo') {

        $relativepath = implode('/', $args);
        $filecontext = get_context_instance(CONTEXT_MODULE, $cm->id);
        $fullpath = "/$filecontext->id/mod_pcast/$filearea/$relativepath";

        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {

            return false;
        }

        // finally send the file
        send_stored_file($file, 0, 0, $forcedownload); // download MUST be forced - security!

    }

    return false;
}

/**
 * logs the pcast files.
 *
 * @global stdClass $CFG
 * @param stdClass $pcast
 * @param string $userid
 * @return bool false if error else true
 */
function pcast_add_view_instance($pcast, $userid) {
    global $DB;

    //lookup the user add add to the view count
    if (!$view = $DB->get_record("pcast_views", array("episodeid" => $pcast->id, "userid" => $userid))) {
        $view=null;
        unset($view);
        $view->userid=$userid;
        $view->views=1;
        $view->episodeid=$pcast->id;
        $view->lastview = time();

        if(!$result=$DB->insert_record("pcast_views", $view, $returnid=false, $bulk=false)) {
            print_error('databaseerror', 'pcast');
        }
    } else { //Never viewed the file before
        $temp_view = $view->views + 1;
        $view->views = $temp_view;
        $view->lastview=time();
        if(!$result = $DB->update_record("pcast_views", $view, $bulk=false)) {
            print_error('databaseerror', 'pcast');
        }
    }

    add_to_log($pcast->course, "pcast", "view", $pcast->URL, $pcast->filename,0 ,$userid);

    return $result;
}

/**
 * Returns all other caps used in module
 * @return array
 */
function pcast_get_extra_capabilities() {
    return array('moodle/comment:post',
                 'moodle/comment:view',
                 'moodle/site:viewfullnames',
                 'moodle/site:trustcontent',
                 'moodle/rating:view',
                 'moodle/rating:viewany',
                 'moodle/rating:viewall',
                 'moodle/rating:rate',
                 'moodle/site:accessallgroups');
}

// Course reset code
/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the pcast.
 * @param stdClass $mform form passed by reference
 */
function pcast_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'pcastheader', get_string('modulenameplural', 'pcast'));
    $mform->addElement('checkbox', 'reset_pcast_all', get_string('resetpcastsall', 'pcast'));

    $mform->addElement('checkbox', 'reset_pcast_notenrolled', get_string('deletenotenrolled', 'pcast'));
    $mform->disabledIf('reset_pcast_notenrolled', 'reset_pcast_all', 'checked');

    $mform->addElement('checkbox', 'reset_pcast_ratings', get_string('deleteallratings'));
    $mform->disabledIf('reset_pcast_ratings', 'reset_pcast_all', 'checked');

    $mform->addElement('checkbox', 'reset_pcast_comments', get_string('deleteallcomments'));
    $mform->disabledIf('reset_pcast_comments', 'reset_pcast_all', 'checked');

    $mform->addElement('checkbox', 'reset_pcast_views', get_string('deleteallviews', 'pcast'));
    $mform->disabledIf('reset_pcast_views', 'reset_pcast_all', 'checked');
}

/**
 * Course reset form defaults.
 * @return array
 */
function pcast_reset_course_form_defaults($course) {
    return array('reset_pcast_all'=>0, 'reset_pcast_ratings'=>1, 'reset_pcast_comments'=>1, 'reset_pcast_notenrolled'=>0, 'reset_pcast_views'=>1);
}

/**
 * Removes all grades from gradebook
 *
 * @global stdClass
 * @param int $courseid
 * @param string optional type
 */
// TODO LOOK AT AFTER GRADES ARE IMPLEMENTED
function pcast_reset_gradebook($courseid, $type='') {
    global $DB;

    $sql = "SELECT g.*, cm.idnumber as cmidnumber, g.course as courseid
              FROM {pcast} g, {course_modules} cm, {modules} m
             WHERE m.name='pcast' AND m.id=cm.module AND cm.instance=g.id AND g.course=?";

    if ($pcasts = $DB->get_records_sql($sql, array($courseid))) {
        foreach ($pcasts as $pcast) {
            pcast_grade_item_update($pcast, 'reset');
        }
    }
}
/**
 * Actual implementation of the rest coures functionality, delete all the
 * pcast responses for course $data->courseid.
 *
 * @global stdClass
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function pcast_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/rating/lib.php');

    $componentstr = get_string('modulenameplural', 'pcast');
    $status = array();

    $allepisodessql = "SELECT e.id
                        FROM {pcast_episodes} e
                             JOIN {pcast} p ON e.pcastid = p.id
                       WHERE p.course = ?";

    $allpcastssql = "SELECT p.id
                           FROM {pcast} p
                          WHERE p.course = ?";

    $params = array($data->courseid);

    $fs = get_file_storage();

    $rm = new rating_manager();
    $ratingdeloptions = new stdClass();
    $ratingdeloptions->component = 'mod_pcast';
    $ratingdeloptions->ratingarea = 'episode';

    // delete entries if requested
    if (!empty($data->reset_pcast_all)) {

        $params[] = 'pcast_episode';
        $DB->delete_records_select('comments', "itemid IN ($allepisodessql) AND commentarea=?", $params);
        $DB->delete_records_select('pcast_episodes', "pcastid IN ($allpcastssql)", $params);

        // now get rid of all attachments
        if ($pcasts = $DB->get_records_sql($allpcastssql, $params)) {
            foreach ($pcasts as $pcastid => $unused) {
                if (!$cm = get_coursemodule_from_instance('pcast', $pcastid)) {
                    continue;
                }
                $context = get_context_instance(CONTEXT_MODULE, $cm->id);
                $fs->delete_area_files($context->id, 'mod_pcast', 'episode');

                //delete ratings
                $ratingdeloptions->contextid = $context->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

        // remove all grades from gradebook
        if (empty($data->reset_gradebook_grades)) {
            pcast_reset_gradebook($data->courseid);
        }

        $status[] = array('component'=>$componentstr, 'item'=>get_string('resetpcastsall', 'pcast'), 'error'=>false);

    }
    // remove entries by users not enrolled into course
    else if (!empty($data->reset_pcast_notenrolled)) {

        $course_context = get_context_instance(CONTEXT_COURSE, $data->courseid);
        // Get list of enrolled users
        $people = get_enrolled_users($course_context);
        $list ='';
        $list2 ='';
        foreach ($people as $person) {
            $list .=' AND e.userid != ?';
            $list2 .=' AND userid != ?';
            $params[] = $person->id;

        }
        // Construct SQL to episodes from users whe are no longer enrolled
            $unenrolledepisodessql = "SELECT e.id
                                      FROM {pcast_episodes} e
                                      WHERE e.course = ? " . $list;

        $params[] = 'pcast_episode';
        $DB->delete_records_select('comments', "itemid IN ($unenrolledepisodessql) AND commentarea=?", $params);
        $DB->delete_records_select('pcast_episodes', "course =? ". $list2, $params);

        // now get rid of all attachments
        if ($pcasts = $DB->get_records_sql($unenrolledepisodessql, $params)) {
            foreach ($pcasts as $pcastid => $unused) {
                if (!$cm = get_coursemodule_from_instance('pcast', $pcastid)) {
                    continue;
                }
                $context = get_context_instance(CONTEXT_MODULE, $cm->id);
                $fs->delete_area_files($context->id, 'mod_pcast', 'episode');

                //delete ratings
                $ratingdeloptions->contextid = $context->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

        // remove all grades from gradebook
        if (empty($data->reset_gradebook_grades)) {
            pcast_reset_gradebook($data->courseid);
        }

        $status[] = array('component'=>$componentstr, 'item'=>get_string('deletenotenrolled', 'pcast'), 'error'=>false);

    }

    // remove all ratings
    if (!empty($data->reset_pcast_ratings)) {
        //remove ratings
        if ($pcasts = $DB->get_records_sql($allpcastssql, $params)) {
            foreach ($pcasts as $pcastid => $unused) {
                if (!$cm = get_coursemodule_from_instance('pcast', $pcastid)) {
                    continue;
                }
                $context = get_context_instance(CONTEXT_MODULE, $cm->id);

                //delete ratings
                $ratingdeloptions->contextid = $context->id;
                $rm->delete_ratings($ratingdeloptions);
            }
        }

        // remove all grades from gradebook
        if (empty($data->reset_gradebook_grades)) {
            pcast_reset_gradebook($data->courseid);
        }
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallratings'), 'error'=>false);
    }

    // remove comments
    if (!empty($data->reset_pcast_comments)) {
        $params[] = 'pcast_episode';
        $DB->delete_records_select('comments', "itemid IN ($allepisodessql) AND commentarea= ? ", $params);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallcomments'), 'error'=>false);
    }

    // remove views
    if (!empty($data->reset_pcast_views)) {
//        $params[] = 'pcast_episode';
        $DB->delete_records_select('pcast_views',"episodeid IN ($allepisodessql) ", $params);
//        $DB->delete_records_select('pcast_views', "itemid IN ($allepisodessql) AND commentarea= ? ", $params);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallviews','pcast'), 'error'=>false);
    }
    /// updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates('pcast', array('assesstimestart', 'assesstimefinish'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}


//TODO: RATINGS CODE -UNTESTED

/**
 * Return grade for given user or all users.
 *
 * @global stdClass
 * @param int $pcastid id of pcast
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function pcast_get_user_grades($pcast, $userid=0) {

    global $CFG;

    require_once($CFG->dirroot.'/rating/lib.php');
    $rm = new rating_manager();

    $ratingoptions = new stdClass();

    //need these to work backwards to get a context id. Is there a better way to get contextid from a module instance?
    $ratingoptions->modulename = 'pcast';
    $ratingoptions->moduleid   = $pcast->id;
    $ratingoptions->component = 'mod_pcast';
    $ratingoptions->ratingarea = 'episode';

    $ratingoptions->userid = $userid;
    $ratingoptions->aggregationmethod = $pcast->assessed;
    $ratingoptions->scaleid = $pcast->scale;
    $ratingoptions->itemtable = 'pcast_episodes';
    $ratingoptions->itemtableusercolumn = 'userid';

    $rm = new rating_manager();
    return $rm->get_user_grades($ratingoptions);
}

/**
 * Running addtional permission check on plugin, for example, plugins
 * may have switch to turn on/off comments option, this callback will
 * affect UI display, not like pluginname_comment_validate only throw
 * exceptions.
 * Capability check has been done in comment->check_permissions(), we
 * don't need to do it again here.
 *
 * @param stdClass $comment_param {
 *              context  => context the context object
 *              courseid => int course id
 *              cm       => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return array
 */
function pcast_comment_permissions($comment_param) {
    return array('post'=>true, 'view'=>true);
}

/**
 * Validate comment parameter before perform other comments actions
 *
 * @param stdClass $comment_param {
 *              context  => context the context object
 *              courseid => int course id
 *              cm       => stdClass course module object
 *              commentarea => string comment area
 *              itemid      => int itemid
 * }
 * @return boolean
 */
function pcast_comment_validate($comment_param) {
    global $DB;
    // validate comment area
    if ($comment_param->commentarea != 'pcast_episode') {
        throw new comment_exception('invalidcommentarea');
    }
    if (!$record = $DB->get_record('pcast_episodes', array('id'=>$comment_param->itemid))) {
        throw new comment_exception('invalidcommentitemid');
    }
    if (!$pcast = $DB->get_record('pcast', array('id'=>$record->pcastid))) {
        throw new comment_exception('invalidid', 'data');
    }
    if (!$course = $DB->get_record('course', array('id'=>$pcast->course))) {
        throw new comment_exception('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance('pcast', $pcast->id, $course->id)) {
        throw new comment_exception('invalidcoursemodule');
    }
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    if ($pcast->requireapproval and !$record->approved and !has_capability('mod/pcast:approve', $context)) {
        throw new comment_exception('notapproved', 'pcast');
    }
    // validate context id
    if ($context->id != $comment_param->context->id) {
        throw new comment_exception('invalidcontext');
    }
    // validation for comment deletion
    if (!empty($comment_param->commentid)) {
        if ($comment = $DB->get_record('comments', array('id'=>$comment_param->commentid))) {
            if ($comment->commentarea != 'pcast_episode') {
                throw new comment_exception('invalidcommentarea');
            }
            if ($comment->contextid != $comment_param->context->id) {
                throw new comment_exception('invalidcontext');
            }
            if ($comment->itemid != $comment_param->itemid) {
                throw new comment_exception('invalidcommentitemid');
            }
        } else {
            throw new comment_exception('invalidcommentid');
        }
    }
    return true;
}





/**
 * Return rating related permissions
 * @param string $options the context id
 * @return array an associative array of the user's rating permissions
 */
function pcast_rating_permissions($contextid, $component, $ratingarea) {

    if ($component != 'mod_pcast' || $ratingarea != 'episode') {

        // We don't know about this component/ratingarea so just return null to get the
        // default restrictive permissions.
        return null;

    }
    $context = get_context_instance_by_id($contextid);

    if (!$context) {
        print_error('invalidcontext');
        return null;
    } else {
        return array('view'=>has_capability('mod/pcast:viewrating', $context),
            'viewany'=>has_capability('mod/pcast:viewanyrating', $context),
            'viewall'=>has_capability('mod/pcast:viewallratings', $context),
            'rate'=>has_capability('mod/pcast:rate', $context));
    }
}


/**
 * Validates a submitted rating
 * @param array $params submitted data
 *            context => object the context in which the rated items exists [required]
 *            itemid => int the ID of the object being rated
 *            scaleid => int the scale from which the user can select a rating. Used for bounds checking. [required]
 *            rating => int the submitted rating
 *            rateduserid => int the id of the user whose items have been rated. NOT the user who submitted the ratings. 0 to update all. [required]
 *            aggregation => int the aggregation method to apply when calculating grades ie RATING_AGGREGATE_AVERAGE [optional]
 * @return boolean true if the rating is valid. Will throw rating_exception if not
 */
function pcast_rating_validate($params) {
    global $DB, $USER;

    // Check the component is mod_pcast

    if ($params['component'] != 'mod_pcast') {
        throw new rating_exception('invalidcomponent');
    }

    // Check the ratingarea is episode (the only rating area in pcast)

    if ($params['ratingarea'] != 'episode') {
        throw new rating_exception('invalidratingarea');
    }

    // Check the rateduserid is not the current user .. you can't rate your own posts
    if ($params['rateduserid'] == $USER->id) {
        throw new rating_exception('nopermissiontorate');
    }


    $pcastsql = "SELECT p.id as pcastid, p.scale, p.course, e.userid as userid, e.approved, e.timecreated, p.assesstimestart, p.assesstimefinish
                      FROM {pcast_episodes} e
                      JOIN {pcast} p ON e.pcastid = p.id
                     WHERE e.id = :itemid";
    
    $pcastparams = array('itemid'=>$params['itemid']);
    $info = $DB->get_record_sql($pcastsql, $pcastparams);
    if (!$info) {
        //item doesn't exist
        throw new rating_exception('invaliditemid');
    }

    if ($info->scale != $params['scaleid']) {
        //the scale being submitted doesnt match the one in the database
        throw new rating_exception('invalidscaleid');
    }

    //check that the submitted rating is valid for the scale
    if ($params['rating'] < 0) {
        throw new rating_exception('invalidnum');
    } else if ($info->scale < 0) {
        //its a custom scale
        $scalerecord = $DB->get_record('scale', array('id' => -$info->scale));
        if ($scalerecord) {
            $scalearray = explode(',', $scalerecord->scale);
            if ($params['rating'] > count($scalearray)) {
                throw new rating_exception('invalidnum');
            }
        } else {
            throw new rating_exception('invalidscaleid');
        }
    } else if ($params['rating'] > $info->scale) {
        //if its numeric and submitted rating is above maximum
        throw new rating_exception('invalidnum');
    }    

    if (!$info->approved) {
        //item isnt approved
        throw new rating_exception('nopermissiontorate');
    }

    //check the item we're rating was created in the assessable time window
    if (!empty($info->assesstimestart) && !empty($info->assesstimefinish)) {
        if ($info->timecreated < $info->assesstimestart || $info->timecreated > $info->assesstimefinish) {
            throw new rating_exception('notavailable');
        }
    }

    $cm = get_coursemodule_from_instance('pcast', $info->pcastid, $info->course, false, MUST_EXIST);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id, MUST_EXIST);

    //if the supplied context doesnt match the item's context
    if ($context->id != $params['context']->id) {
        throw new rating_exception('invalidcontext');
    }

    return true;
}



// Gradebook functions (Based on mod_glossary) Not sure how these are called

/**
 * Update activity grades
 *
 * @global stdClass
 * @global stdClass
 * @param stdClass $pcast null means all glossaries (with extra cmidnumber property)
 * @param int $userid specific user only, 0 means all
 */
function pcast_update_grades($pcast=null, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if (!$pcast->assessed) {
        pcast_grade_item_update($pcast);

    } else if ($grades = pcast_get_user_grades($pcast, $userid)) {
        pcast_grade_item_update($pcast, $grades);

    } else if ($userid and $nullifnone) {
        $grade = new object();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        pcast_grade_item_update($pcast, $grade);

    } else {
        pcast_grade_item_update($pcast);
    }
}

/**
 * Update all grades in gradebook.
 *
 * @global stdClass
 */
function pcast_upgrade_grades() {
    global $DB;

    $sql = "SELECT COUNT('x')
              FROM {pcast} g, {course_modules} cm, {modules} m
             WHERE m.name='pcast' AND m.id=cm.module AND cm.instance=g.id";
    $count = $DB->count_records_sql($sql);

    $sql = "SELECT g.*, cm.idnumber AS cmidnumber, g.course AS courseid
              FROM {pcast} g, {course_modules} cm, {modules} m
             WHERE m.name='pcast' AND m.id=cm.module AND cm.instance=g.id";
    if ($rs = $DB->get_recordset_sql($sql)) {
        $pbar = new progress_bar('pcastupgradegrades', 500, true);
        $i=0;
        foreach ($rs as $pcast) {
            $i++;
            upgrade_set_timeout(60*5); // set up timeout, may also abort execution
            pcast_update_grades($pcast, 0, false);
            $pbar->update($i, $count, "Updating pcast grades ($i/$count).");
        }
        $rs->close();
    }
}

/**
 * Create/update grade item for given pcast
 *
 * @global stdClass
 * @param stdClass $pcast object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int, 0 if ok, error code otherwise
 */
function pcast_grade_item_update($pcast, $grades=null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = array('itemname'=>$pcast->name, 'idnumber'=>$pcast->cmidnumber);

    if (!$pcast->assessed or $pcast->scale == 0) {
        $params['gradetype'] = GRADE_TYPE_NONE;

    } else if ($pcast->scale > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $pcast->scale;
        $params['grademin']  = 0;

    } else if ($pcast->scale < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$pcast->scale;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/pcast', $pcast->course, 'mod', 'pcast', $pcast->id, 0, $grades, $params);
}

/**
 * Delete grade item for given pcast
 *
 * @global stdClass
 * @param stdClass $pcast object
 */
function pcast_grade_item_delete($pcast) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/pcast', $pcast->course, 'mod', 'pcast', $pcast->id, 0, null, array('deleted'=>1));
}
