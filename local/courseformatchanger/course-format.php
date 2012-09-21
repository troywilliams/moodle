<?php
/**
 * ================================================================================
 * Description:
 *      Runs through all papers from a certain category with other specific
 *      conditions or attributes, adding/removing or modifying one or more activities
 *      or resources in specific sections within each. Then alters the course format
 *      and rebuilds course page and cache.
 *  Author:
 *      Dean Stringer @ Waikato University, New Zealand
 *      Eugene Venter @ Catalyst IT (port to moodle 2.0)
 *
 * !!! NOTE !!! - the conditions for what courses should be processed are all
 *              managed through the web i/f, but what actvities should actually
 *              be added to the courses are currently manually hard coded in the
 *              section labelled "now do the magick!!"
 *
 *              you will need to change that until such time as this is made
 *              more programatic, e.g. by passing in some data structure or XML
 *              that might represent what you want to change.
 *
 *  Changes:
 *      10th Jun 2011 - port to moodle 2.0
 *      14th Dec 2009 - made more web i/f driven, added moodleform require
 *      30th July 2008 - update to support all-courses or an array of individuals
 *      4th July 2008  - first actual run in MDLTEST/PROD
 *      10th June 2008 - initial release
 *  Other approaches:
 *      sam marshall posts about a similar approach for creating links but uses
 *      direct calls to insert_record instead of this approach of using the API
 *          http://moodle.org/mod/forum/discuss.php?d=88305&parent=390257
 * ================================================================================
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('course-format-form.php');

require_login();

// !!! only let admins run this script !!!
if (!in_array($USER->id, explode(',', $CFG->siteadmins))) {
    print_error('no access, sorry', '', $CFG->wwwroot);
}

// helper libs needed to manipulate the course objects (modules libs are loaded on demand)
require_once($CFG->libdir.'/blocklib.php');
require_once($CFG->libdir.'/dmllib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/adminlib.php');

// misc constants
$title = 'Course format changer report';
$description = "<p>Runs through all papers from a certain category with other specific conditions or " .
        " attributes, adding/removing or modifying one or more activities or resources in specific " .
        " sections within each. Then alters the course format and rebuilds course page and cache</p>" .
        "<p>Please choose these parameters CAREFULLY!!!</p>";
$DEBUG_ERR = 4; $DEBUG_MAIN = 3; $DEBUG_INFO = 2; $DEBUG_FINE = 1;
$debuglevels = array('1'=>'Fine', '2'=>'Info', '3'=>'Main', '4'=>'Error');
$baseCourseURL = $CFG->wwwroot.'/course/view.php?id=';
$timestampCount = 10;              // period between timestamp echos


// !!!!!!! THESE PARAMETERS MUST BE CHOSEN/SET CAREFULLY !!!!!!!
$courseSectionTarget = 0;          // default section, where we want to add our activities


// we need to declare the following options global here, is usually defined in
// mod/resource/lib.php but when we call add_instance for resources
// it seems that the sub-class cant see it, but it can see this one
$RESOURCE_WINDOW_OPTIONS = array('resizable', 'scrollbars', 'directories', 'location',
                                 'menubar', 'toolbar', 'status', 'width', 'height');

// get list of available modules so we can lookup the id of a module using its name
$availModules = array();
$localModules = $DB->get_records("modules");
foreach ($localModules as $thisModule) {
    $availModules[$thisModule->name] = $thisModule->id;
}
admin_externalpage_setup('uowcourseformatchanger');
// Set up page
$syscontext = context_system::instance();
$PAGE->set_context($syscontext);
$PAGE->set_url($CFG->wwwroot.'/local/uow_admin/course-format.php');
$PAGE->set_title($title);
$PAGE->set_heading('UOW Admin');
$PAGE->navbar->add('Course format changer');

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

echo $OUTPUT->box(format_text($description), 'generalbox', 'intro');
echo "<style type='text/css'>
  .changing { color: #009900; font-size: 0.8em; }
  .skip { color: #999900; font-size: 0.8em; }
  .error { color: #990000; font-size: 0.8em; }
</style>";

// get the list of course categories in the system
$catlist = list_categories(NULL);   // format array for display

// build up and display the moodleform to choose what to do
$mform = new course_format_form('', array('categories' => $catlist, 'debuglevels' => $debuglevels));
$mform->display();

// now get our filtered list of courses to process
$catid = optional_param('category', NULL, PARAM_INT);   // the category id we want to process
if (! $catid) {
    // dont process courses unless we have a category id
    echo $OUTPUT->footer();
    exit;
}

$courses = get_courses($catid);

// and check params from the form to decide what to do to them
$processVisibleCourses = optional_param('processvisible', 0, PARAM_INT);
$debugLevel = optional_param('debug', 1, PARAM_INT);
$courseNewFormat = optional_param('format', '', PARAM_TEXT);
$debugLevel = optional_param('debug', 1, PARAM_INT);
$updateMode =  optional_param('updatenow', 0, PARAM_INT);
$maxCoursesToModify = optional_param('maxtochange', 20, PARAM_INT);
$courseMakeVisible = optional_param('makevisible', 0, PARAM_INT);
$processviewed = optional_param('processviewed', 0, PARAM_INT);
$courseViewSkipThreshold = optional_param('viewthreshold', 2, PARAM_INT);
$courseNumSections = optional_param('coursesections', 1, PARAM_INT);    // how many we want displayed
$courseNameContains = optional_param('coursename', '', PARAM_TEXT);

echo $OUTPUT->box_start('generalbox');
echo '<table width="500"><tbody>';
debug($DEBUG_MAIN, 'Start at: ', date('Y-m-d H:i:s'));


$coursesModified = 0;
foreach($courses as $course) {
    if ($coursesModified == $maxCoursesToModify) {
        debug($DEBUG_MAIN,  'breaking', 'changed max courses (' . $maxCoursesToModify . ')');
        break;
    }
    // we dont process courses that dont contain the coursename string
    if ($courseNameContains && (!stripos($course->shortname,$courseNameContains)))  {
        debug($DEBUG_MAIN, 'skipping', 'shortname does not contain: '.$courseNameContains, $course->id, $course->shortname);
        continue;
    }
    // we dont process courses that are visible
    if (!$processVisibleCourses && $course->visible)  {
        debug($DEBUG_MAIN, 'skipping', 'is visible', $course->id, $course->shortname);
        continue;
    }
    // if already in the target format then we've probably already changed it, so skip
    if ($course->format == $courseNewFormat)  {
        debug($DEBUG_MAIN, 'skipping', 'format already updated', $course->id, $course->shortname);
        continue;
    }
    // and we also skip any that have been viewed already
    $viewCount = $DB->count_records('log', array('action' => 'view', 'course' => $course->id));
    if (!$processviewed && ($viewCount>$courseViewSkipThreshold)) {
        debug($DEBUG_MAIN,  'skipping', 'viewed', $course->id, $course->shortname);
        continue;
    }
    // ---------------------------------------------------------------------
    // !!now do the magick!!
    //
    // include entries here you want added to the course, longer term this
    // would be nice to have driven by an XML file or other structured
    // data source
    // ---------------------------------------------------------------------
    $addModules = array(
        array('type'=>'url', 'title'=>'Announcements', 'section'=>$courseSectionTarget,
                'externalurl'=>'http://www.mngt.waikato.ac.nz/myweb/papers/announcements?crs_off_id='.$course->shortname),
        array('type'=>'forum', 'title'=>'General Discussion', 'section'=>$courseSectionTarget, 'externalurl'=>''),
        array('type'=>'chat', 'title'=>'Chat', 'section'=>$courseSectionTarget, 'externalurl'=>''),
        array('type'=>'url', 'title'=>'Assigments', 'section'=>$courseSectionTarget,
                'externalurl'=>'http://www.mngt.waikato.ac.nz/myweb/papers/assessment/index.asp?crs_off_id='.$course->shortname),
        array('type'=>'url', 'title'=>'Marks', 'section'=>$courseSectionTarget,
                'externalurl'=>'http://www.mngt.waikato.ac.nz/myweb/papers/grades/index.asp?crs_off_id='.$course->shortname)
    );

    // if we got this far then we can process this course now
    processCourse($course, $courseNewFormat, $courseSectionTarget,
        $courseNumSections, $courseMakeVisible, $updateMode, $addModules);

    $coursesModified++;
    if (! ($coursesModified % $timestampCount)) { // show a timestamp periodically
        debug($DEBUG_MAIN, 'timestamp ', date('Y-m-d H:i:s'));
    }

    ob_flush(); // flush output so we can monitor progress
    flush();
}

debug($DEBUG_MAIN, 'End at: ', date('Y-m-d H:i:s'));
echo '</tbody></table>';
echo $OUTPUT->box_end();

echo $OUTPUT->footer();

exit;

# ------------------------------------------------------------------------------------
# START OF FUNCS
# ------------------------------------------------------------------------------------

/**
 * Recursively build up a list of categories and their children
 * @param int   $category id of the category we want to crawl
 * @param int   $depth of the current crawl level
 * @param array $catlist the current crawl stack
 * @return array    $catlist
 */
function list_categories($category, $depth=-1, $catlist=array()) {
    if (!empty($category)) {
        $prefix = '';
        for ($i=0; $i<$depth; $i++) {
            $prefix .= '-'; // easier to read in big list of cats
        }
        $catlist[$category->id] = $prefix.$category->name;
    } else {
        $category->id = '0';
    }
    if ($categories = get_categories($category->id)) {   // Print all the children recursively
        $countcats = count($categories);
        $count = 0;
        foreach ($categories as $cat) {
            $count++;
            if ($count == $countcats) {
                $last = true;
            }
            $catlist = list_categories($cat, $depth+1, $catlist);
        }
    }
    return $catlist;
}


function getCoursesByShortname($courseNames) {
 /** -----------------------------------------------------------------------------
 *  given an array of course shortnames call get_record to fetch each course object
 *  and return them all in an array
 *  @param int      $course                 id of course to locate module in
 *  @return array   of course objects
 * -----------------------------------------------------------------------------
 */
    global $DB, $DEBUG_INFO, $DEBUG_ERR, $DEBUG_MAIN, $DEBUG_FINE;
    $courses = array();
    foreach($courseNames as $courseShortName) {
        if ($course = $DB->get_record('course', array('shortname' => $courseShortName))) {
            array_push($courses,$course);
        } else {
            debug($DEBUG_ERR, '', 'No such shortname: ' . $courseShortName);
        }
    }
    return $courses;
}

function processCourse($course, $courseNewFormat, $courseSectionTarget,
            $courseNumSections, $courseMakeVisible, $updateMode, $addModules) {
/** -----------------------------------------------------------------------------
 *  given a course id process it, adding neccessary objects and changing its properties
 *
 *  @param int      $course                 id of course to locate module in
 *  @param string   $courseNewFormat        format to change course to
 *  @param int      $courseSectionTarget    section to place new activities in
 *  @param int      $courseNumSections      how many sections to set for course
 *  @param bool     $courseMakeVisible      whether to make visible or not
 *  @param bool     $updateMode             if true actually make changes, otherwise just a dry run
 * -----------------------------------------------------------------------------
 */
    global $DEBUG_INFO, $DEBUG_ERR, $DEBUG_MAIN, $DEBUG_FINE;

    // we should be good to go now, spit out some debugging info
    debug($DEBUG_MAIN,  'changing', '', $course->id, $course->shortname);
    debug($DEBUG_FINE,  '',
        '<br>ID:' . $course->id .
        '<br>Format:' . $course->format .
        '<br>Visibility:' . $course->visible  // dont want to process active papers
    );
    $courseModules = get_course_mods($course->id);  // lib/datalib.php
    if ($courseModules) { // may not have any
        $modInfo = "Existing modules:<ul>";
        foreach($courseModules as $module) {
            $modInfo .= "<li>" . $module->modname . " - instance=" . $module->instance;
        }
        $modInfo .= "</ul>";
        debug($DEBUG_FINE, '', $modInfo);
    }

    if ($updateMode) {
        foreach ($addModules as $module) {
            addCourseModule($course, $module['type'],$module['title'],$module['section'],$module['externalurl']);
        }
        if ($courseModules) { // may not have any
            hideCourseModule($course->id, $courseModules, 'forum', 'news');
        }
    }

    if ($updateMode) {  // set to '0' to do a dry-run without any changes
        // Change the paper format and repopulate the page, ie rebuild it
        $course->format = $courseNewFormat;
        $course->showgrades = 0;
        $course->newsitems = 0; // to hide the block
        $course->numsections = $courseNumSections;
        $course->fullname = addslashes($course->fullname); // can contain single quotes
        $course->summary = addslashes($course->summary);
        if ($courseMakeVisible) { $course->visible = 1; }
        update_course($course); // course/lib.php
    }

    if ($updateMode) {  // set to '0' to do a dry-run without any changes
        // Re-populate the course page and rebuild the cache
        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        $coursecontext = context_course::instance($course->id, MUST_EXIST);
        blocks_delete_all_for_context($coursecontext->id);
        blocks_add_default_course_blocks($course);
        rebuild_course_cache($course->id);  // course/lib.php
    }
}


function hideCourseModule($course='', $courseModules='', $module='', $type='') {
/** -----------------------------------------------------------------------------
 *  Hide an instance of a module by updating its course_modules entry
 *  NOTE: only actually works with 'news forum' types at the moment
 *
 *  @param int      $course  id of course to locate module in
 *  @param array    $courseModules  array results from call to datalib.php::get_course_mods()
 *  @param int      $module  type of module (e.g '7' = forum)
 *  @param string   $type    subtype of module (e.g. 'news' forum)
 *  @return bool    true on success, false on fail
 *  -----------------------------------------------------------------------------
 */
    global $DB, $DEBUG_INFO, $DEBUG_ERR, $DEBUG_MAIN, $DEBUG_FINE;
    global $availModules;
    foreach($courseModules as $thisModule) {
        // is this module type the one we're looking for
        if ($thisModule->module == $availModules[$module]) {
            // get its full module table record so we can find its instance id
            if ($moduleRecord = $DB->get_record($module, array('type' => $type, 'course' => $course))) {
                debug($DEBUG_INFO, '', 'Found module of type: ' . $type . ' name:' . $moduleRecord->name);
                // use its instance id to get its actual course_modules record
                if ($thisCourseModule = $DB->get_record('course_modules', array('module' => $thisModule->module, 'instance' => $moduleRecord->id))) {
                    $thisCourseModule->visible = 0;
                    $DB->update_record('course_modules', $thisCourseModule);
                    debug($DEBUG_INFO, '', 'Module hidden');
                    return true;
                }
            }
        }
    }
    return false;
}


function addCourseModule($course, $moduleName, $displayName, $sectionTarget, $data='') {
/** -----------------------------------------------------------------------------
* Add a module to a course. This process involves...
*  o locating the helper library for the type of module we want to add
*  o getting the section object we want to add our new module to
*  o creating an instance of the new instance of this module
*  o associating the module instance with the course
*  o associating the module instance with the section
*
* @param int      $course  id of course to place module in
* @param string   $moduleName name of module to add
* @param string   $displayName  name to be added to new module
* @param int      $sectionTarget    id of section to add module to
* @param string   $data optional activity/module data
* @return bool    true on success, false on fail
* -----------------------------------------------------------------------------
*/
    global $DEBUG_INFO, $DEBUG_ERR, $DEBUG_MAIN, $DEBUG_FINE, $CFG, $DB;
    global $availModules;
    $timenow = time();
    debug($DEBUG_FINE, '', 'Adding module type:' . $moduleName);

    // ------------------------------------------------------------
    // Include the helper library for the type of module we want to add
    // and check we are allowed to add that type to our course
    //    select id,course,section,sequence from mdl_course_sections where course=9;
    // ------------------------------------------------------------
    $modIncPath = "$CFG->dirroot/mod/$moduleName/lib.php";
    if ( file_exists ($modIncPath) ) {  // cant trap result of require_once as is fatal
        require_once($modIncPath);
        debug($DEBUG_FINE, '', 'Loaded lib: ' . $modIncPath);
    } else {
        debug($DEBUG_ERR, '', 'No such module library: ' . $modIncPath);
        return false;
    }
    // load module locallib if present
    $modIncPathLocal = "{$CFG->dirroot}/mod/{$moduleName}/locallib.php";
    if (file_exists($modIncPathLocal)) {
        require_once($modIncPathLocal);
        debug($DEBUG_FINE, '', 'Loaded locallib: ' . $modIncPathLocal);
    }
    if (! course_allowed_module($course, $moduleName)) {    // course/lib.php
        debug($DEBUG_ERR,'', 'This module has been disabled for this particular course');
        return false;
    } else {
        debug($DEBUG_FINE, '', 'Module type allowed in course');
    }

    // ------------------------------------------------------------
    // Get the section object we want to add our new module to
    // ------------------------------------------------------------
    $courseSection = get_course_section($sectionTarget, $course->id); // course/lib.php
    debug($DEBUG_INFO, '', "Found course section " . $courseSection->section . ", id=" . $courseSection->id);

    // ------------------------------------------------------------
    $instanceData = new object();   // must be an object for add_record()
    $instanceData->name = $displayName;
    $instanceData->course = $course->id;
    $instanceData->intro = $displayName;
    $instanceData->introformat = FORMAT_HTML;
    $instanceData->section = $courseSection->id;
    $instanceData->coursemodule = $availModules[$moduleName];
    $instanceData->module = $availModules[$moduleName];
    $instanceData->timemodified = $timenow;
    // groupmode => 0   (0 is the default anyway)

    // setup some specific attributes for 'resource' types (e.g. links)
    // for more info see: mod/resource/type/file/resource.class.php

    if ($moduleName == 'forum') {
        $instanceData->forcesubscribe = '';
        $instanceData->type = 'general';
        $instanceData->cmidnumber = '';

    } else if ($moduleName == 'chat') {
        $instanceData->chattime = $timenow;
        $instanceData->schedule = 0;

    } elseif ($moduleName == 'url') {  // see mod/resource/type/file/lib.php
        $instanceData->display = RESOURCELIB_DISPLAY_POPUP;
        $instanceData->popupheight=450;
        $instanceData->popupwidth=620;
        $instanceData->externalurl=$data;

        // these do get commited in the resource record, but the courseidnumber doesnt get process when viewing through the web
        //$instanceData->parameter1 = 'courseidnumber';
        //$instanceData->parse1 = 'crs_off_id';
    }

    // ------------------------------------------------------------
    // add a new instance of this module, every module has a <modulename>_add_instance() func
    //   select id,name from mdl_chat where course=8 order by id;
    // ------------------------------------------------------------
    $addinstancefunction = $moduleName . "_add_instance";
    debug($DEBUG_FINE, '', "add function name =" . $addinstancefunction);
    // Dirty Hack: Chat fails on format_module_intro()->format_text() as cm hasn't been created yet.
    // Cal event trying to be setup inside add_instance.
    if ($moduleName == 'chat') {
        $instanceData->timemodified = time();
        $returnfromfunc = $DB->insert_record("chat", $instanceData);
    } else {
        $returnfromfunc = $addinstancefunction($instanceData, null);
    }
    if (!$returnfromfunc) {
        debug($DEBUG_ERR, '', "Could not add a new instance of $moduleName");
        return false;
    }
    if (is_string($returnfromfunc)) {
        debug($DEBUG_ERR, '', "Could not add a new instance of $moduleName, error was: " . $returnfromfunc);
        return false;
    }
    debug($DEBUG_INFO, '', "Created module: $moduleName , instanceid=" . $returnfromfunc);

    $instanceData->instance = $returnfromfunc;
    
    // ------------------------------------------------------------
    // Associate the module instance with the course
    // note: add_course_module() in /course/lib.php
    //    select * from mdl_course_modules where course=8 order by id;
    // ------------------------------------------------------------
    if (! $instanceData->coursemodule = add_course_module($instanceData) ) {
        debug($DEBUG_ERR, '', "Could not add a new course module");
        return false;
    } else {
        debug($DEBUG_INFO, '', "Added module to course, instance=" .  $instanceData->instance .
            " id: " . $instanceData->coursemodule);
    }

    // ------------------------------------------------------------
    // Associate the module instance with the section in the course
    // note: add_course_module() in /course/lib.php
    //      select * from mdl_course_sections where course=8;
    // ------------------------------------------------------------
    $instanceData->section = $sectionTarget;    // need the sequence here, not id
    if (! $sectionid = add_mod_to_section($instanceData) ) {
        debug($DEBUG_ERR, '', "Could not add the new course module to that section");
        return false;
    } else {
        debug($DEBUG_INFO, '', "Added module to section, id:" . $sectionid);
    }

    add_to_log($course->id, $moduleName, "add", "view.php?id=$instanceData->coursemodule",
        "$instanceData->instance", $instanceData->coursemodule);

    return true;
}


function debug($level = 2, $action='', $details = '', $courseid='', $coursename='') {
// ---------------------------------------------------------------------
    global $debugLevel, $DEBUG_INFO, $DEBUG_ERR, $baseCourseURL;
    if ($level >= $debugLevel) {
        $class = ''; $style = '';
        if ($level == $DEBUG_INFO) {  }
        if ($level == $DEBUG_ERR) { $class = 'error'; $action='Error'; }
        if ($action == 'skipping') { $class = 'skip'; }
        if ($action == 'changing') { $class = 'changing'; }
        if ($class) { $style = " class='$class'"; }
        if ($action != '') {
            echo "\n<tr><td$style>" . $action . '</td><td>';
            if ($coursename) {
                echo '<a href="' . $baseCourseURL . $courseid . '">' . $coursename . '</a>';
            }
            echo '</td><td>' . $details . '</td></tr>';
        } else {
            echo "\n<tr><td>&nbsp;</td><td colspan='2' $style>" . $details . '</td></tr>';
        }
    }
}

?>
