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
 * Local library
 *
 * @package report_studentlist
 * @copyright 2013 The University of Waikato
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Used to build SQL needed to retrieve student list
 * records based on user options.
 *
 */
class report_studentlist {

    const DEFAULT_PAGE_SIZE = 20;

    protected $sqlselect    = null;
    protected $sqlbase      = null;
    protected $sqlparams    = array();
    protected $sqlwhere     = null;
    protected $sqlorderby   = '';
    protected $searchtext   = null;
    protected $config       = null;
    protected $course       = null;
    protected $currentgroup = null;
    protected $cache        = null;
    protected $currentpage  = 0;
    public $pages           = 0;


    protected static $fields = array('id',
                                     'username',
                                     'idnumber',
                                     'firstname',
                                     'lastname',
                                     'email',
                                     'picture',
                                     'imagealt',
                                     'phone1',
                                     'phone2',
                                     'city',
                                     'country',
                                     'maildisplay');

    protected static $sortbyoptions  = array('lastnameaz',
                                             'lastnameza',
                                             'firstnameaz',
                                             'firstnameza',
                                             'idnumber09',
                                             'idnumber90');

    public function __construct($course) {

        $this->config       = get_config('report_studentlist');
        $this->course       = $course;
        $this->context      = context_course::instance($course->id);
        $this->currentgroup = groups_get_course_group($course, true);

        $this->initialise_basesql();

        if (is_null($this->cache)) {
            $this->cache  = cache::make('report_studentlist', 'listdisplay');
        }
    }

    /**
     * Builds base SQL needed to run.
     *
     */
    protected function initialise_basesql() {
        $params = array();

        $params['courseid'] = $this->course->id;
        $params['roleid']   = $this->config->studentroleid;

        list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->context, null, $this->currentgroup, true);

        $params = array_merge($params, $enrolledparams);

        $sqlbase = "FROM {user} u
                    JOIN ($enrolledsql) e ON e.id = u.id
               LEFT JOIN {user_lastaccess} ul ON (ul.userid = u.id AND ul.courseid = :courseid)
                    JOIN {role_assignments} ra ON ra.userid = u.id AND ra.roleid = :roleid";

        $this->sqlselect  = "SELECT " . self::fields() . " ";
        $this->sqlbase    = $sqlbase;
        $this->sqlparams  = $params;
    }

    /**
     * Clear serach filter, unset variables
     */
    public function clear_search_filter() {
        $this->searchtext = null;
        $this->sqlwhere   = null;
        unset($this->sqlparams['search1']);
        unset($this->sqlparams['search3']);
        unset($this->sqlparams['search4']);
    }
    /**
     * Builds SQL ORDER BY based on a sort by option passed
     * in.
     *
     * @param string $sort
     */
    public function set_order_by($sort) {

         switch ($sort) {
             case 'lastnameaz':
                 $this->sqlorderby = 'ORDER BY u.lastname';
                 break;
             case 'lastnameza':
                 $this->sqlorderby = 'ORDER BY u.lastname DESC';
                 break;
             case 'firstnameaz':
                 $this->sqlorderby = 'ORDER BY u.firstname';
                 break;
             case 'firstnameza':
                 $this->sqlorderby = 'ORDER BY u.firstname  DESC';
                 break;
             case 'idnumber09':
                 $this->sqlorderby = 'ORDER BY u.idnumber';
                 break;
             case 'idnumber90':
                 $this->sqlorderby = 'ORDER BY u.idnumber  DESC';
                 break;
             default:
                 $this->sqlorderby = '';
         }
    }
    /**
     * Build SQL WHERE based on passed in search string, searches
     * against fullname, username and idnumber.
     *
     * @global stdClass $DB
     * @param string $text
     */
    public function set_search_filter($text) {
        global $DB;

        $search = $text;
        if (!empty($search)) {
            $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
            $this->sqlwhere = " WHERE (". $DB->sql_like($fullname, ':search1', false, false) .
                              " OR ". $DB->sql_like('username', ':search3', false, false) .
                              " OR ". $DB->sql_like('idnumber', ':search4', false, false) .") ";
            $this->sqlparams['search1'] = "%$search%";
            $this->sqlparams['search3'] = "%$search%";
            $this->sqlparams['search4'] = "%$search%";
        }
    }
    /**
     * Returns the available sort by options
     *
     * @return array $sortbyoptions
     */
    public static function sort_by_options() {
        return self::$sortbyoptions;
    }
    /**
     * Returns required fields prefixed
     *
     * @return array $fields
     */
    public static function fields() {
        $fields = array();
        foreach (self::$fields as $field) {
            $fields[$field] = "u.{$field}";
        }
        return implode(',', $fields) . ", COALESCE(ul.timeaccess, 0) AS lastaccess";
    }
    /**
     * Returns a page of records, based on default page size.
     *
     * @global stdClass $DB
     * @param int $page
     * @return array records
     */
    public function page($page = 0) {
        global $DB;

        $this->pages = round($this->total_matches() / self::DEFAULT_PAGE_SIZE, 0, PHP_ROUND_HALF_UP);

        $limitfrom = $page * self::DEFAULT_PAGE_SIZE;

        return $DB->get_records_sql($this->sqlselect . $this->sqlbase . $this->sqlwhere . $this->sqlorderby, $this->sqlparams, $limitfrom, self::DEFAULT_PAGE_SIZE);
    }
    /**
     * Return all records
     *
     * @global global $DB
     * @return array records
     */
    public function all() {
        global $DB;

        return $DB->get_records_sql($this->sqlselect . $this->sqlbase . $this->sqlwhere . $this->sqlorderby, $this->sqlparams);
    }
    /**
     * Returns count of all matching records
     *
     * @global stdClass $DB
     * @return int $count
     */
    public function total_matches() {
        global $DB;
        return $DB->count_records_sql("SELECT COUNT(1) " . $this->sqlbase . $this->sqlwhere, $this->sqlparams);
    }

}

/**
 * Offical student picture extends the user picture class.
 * Create url referencing users profile image off the
 * framework server.
 *
 */
class official_student_picture extends user_picture {
    /**
     * @var string Image class attribute
     */
    public $class = 'officalstudentpicture';
    /**
     * @var bool Add course profile link to image
     */
    public $link = false;

    /**
     * @var int Size in pixels. Special values are (true/1 = 100px) and
     * (false/0 = 35px)
     * for backward compatibility.
     */
    public $size = 64;
     /**
     * Works out the URL for the users picture.
     *
     * This method is recommended as it avoids costly redirects of user pictures
     * if requests are made for non-existent files etc.
     *
     * @param renderer_base $renderer
     * @return moodle_url
     */
    public function get_url(moodle_page $page, renderer_base $renderer = NULL) {
        global $CFG;

        if ((!empty($CFG->forcelogin) and !isloggedin()) ||
                (!empty($CFG->forceloginforprofileimage) && (!isloggedin() || isguestuser()))) {
            // protect images if login required and not logged in;
            // also if login is required for profile images and is not logged in or guest
            // do not use require_login() because it is expensive and not suitable here anyway
            return $renderer->pix_url('u/f1');
        }

        $imageurl = new moodle_url("http://framework-prod.its.waikato.ac.nz/photoapp/thumb.php", array('id' => base64_encode($this->user->idnumber)));
        // Return the URL that has been generated.
        return $imageurl;
    }
}

/**
 *
 * @global type $DB
 * @staticvar type $groups
 * @staticvar type $groupmemberships
 * @param type $courseid
 * @param type $userid
 * @return type
 */
function report_studentlist_get_user_groups($courseid, $userid) {
    global $DB;

    static $groups;
    static $groupmemberships;

    $usersgroups = array();

    if (!isset($groups)) {
        $groups = groups_get_all_groups($courseid);
    }

    if (!isset($groupmemberships) and !empty($groups)) {
        $sql = "SELECT gm.groupid, gm.userid
                  FROM {groups_members} gm
                 WHERE gm.groupid "; // leave trailing space
        list($insql, $inparams) = $DB->get_in_or_equal(array_keys($groups), SQL_PARAMS_NAMED);
        $rs = $DB->get_recordset_sql($sql.$insql, $inparams);
        $groupmemberships = array();
        if ($rs->valid()) {
            foreach ($rs as $record) {
                $groupmemberships[$record->userid][$record->groupid] = $groups[$record->groupid];
            }
        }
        $rs->close();
    }
    if (isset($groupmemberships[$userid])) {
        $usersgroups = $groupmemberships[$userid];
    }
    return $usersgroups;
}

/**
 *
 * @global type $SITE
 * @global type $COURSE
 * @return type
 */
function report_studentlist_issite(){
    global $SITE, $COURSE;

    return ($SITE->id == $COURSE->id);
}

/**
 *
 * @global type $CFG
 * @param type $relativeurl
 * @return type
 */
function report_studentlist_url($relativeurl) {
    global $CFG;
    return $CFG->wwwroot.'/report/studentlist/'.$relativeurl;
}

