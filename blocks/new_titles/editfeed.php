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

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir .'/simplepie/moodle_simplepie.php');

class feed_edit_form extends moodleform {
    protected $isadding;
    protected $title = '';
    protected $description = '';

    function __construct($actionurl, $isadding, $caneditshared) {
        $this->isadding = $isadding;
        $this->caneditshared = $caneditshared;
        parent::moodleform($actionurl);
    }

    function definition() {
        $mform =& $this->_form;

        // Then show the fields about where this block appears.
        $mform->addElement('header', 'rsseditfeedheader', get_string('feed', 'block_new_titles'));

        $mform->addElement('text', 'url', get_string('feedurl', 'block_new_titles'), array('size' => 60));
        $mform->setType('url', PARAM_URL);
        $mform->addRule('url', null, 'required');

        /*$mform->addElement('checkbox', 'autodiscovery', get_string('enableautodiscovery', 'block_new_titles'));
        $mform->setDefault('autodiscovery', 1);
        $mform->setAdvanced('autodiscovery');
        $mform->addHelpButton('autodiscovery', 'enableautodiscovery', 'block_new_titles');*/

        $mform->addElement('text', 'preferredtitle', get_string('customtitlelabel', 'block_new_titles'), array('size' => 60));
        $mform->setType('preferredtitle', PARAM_NOTAGS);

        $submitlabal = null; // Default
        if ($this->isadding) {
            $submitlabal = get_string('addnewlibrarytitle', 'block_new_titles');
        }
        $this->add_action_buttons(true, $submitlabal);
    }

    function definition_after_data(){
        $mform =& $this->_form;

        /*if($mform->getElementValue('autodiscovery')){
            $mform->applyFilter('url', 'feed_edit_form::autodiscover_feed_url');
        }*/
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $rss =  new moodle_simplepie();
        // set timeout for longer than normal to try and grab the feed
        $rss->set_timeout(10);
        $rss->set_feed_url($data['url']);
        $rss->set_autodiscovery_cache_duration(0);
        $rss->set_autodiscovery_level(SIMPLEPIE_LOCATOR_NONE);
        $rss->init();

        if ($rss->error()) {
            $errors['url'] = get_string('errorloadingfeed', 'block_new_titles', $rss->error());
        } else {
            $this->title = $rss->get_title();
            $this->description = $rss->get_description();
        }

        return $errors;
    }

    function get_data() {
        $data = parent::get_data();
        if ($data) {
            $data->title = '';
            $data->description = '';

            if($this->title){
                $data->title = $this->title;
            }

            if($this->description){
                $data->description = $this->description;
            }
        }
        return $data;
    }

    /**
     * Autodiscovers a feed url from a given url, to be used by the formslibs
     * filter function
     *
     * Uses simplepie with autodiscovery set to maximum level to try and find
     * a feed to subscribe to.
     * See: http://simplepie.org/wiki/reference/simplepie/set_autodiscovery_level
     *
     * @param string URL to autodiscover a url
     * @return string URL of feed or original url if none found
     */
    public static function autodiscover_feed_url($url){
            $rss =  new moodle_simplepie();
            $rss->set_feed_url($url);
            $rss->set_autodiscovery_level(SIMPLEPIE_LOCATOR_ALL);
            // When autodiscovering an RSS feed, simplepie will try lots of
            // rss links on a page, so set the timeout high
            $rss->set_timeout(20);
            $rss->init();

            if($rss->error()){
                return $url;
            }

            return $rss->subscribe_url();
    }
}

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INT);
$rssid = optional_param('rssid', 0, PARAM_INT); // 0 mean create new.

if ($courseid == SITEID) {
    $courseid = 0;
}
if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
}

$managefeeds = has_capability('block/new_titles:managefeeds', $context);


$urlparams = array('rssid' => $rssid);
if ($courseid) {
    $urlparams['courseid'] = $courseid;
}
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}
$managefeedsurl = new moodle_url('/blocks/new_titles/managefeeds.php', $urlparams);

$PAGE->set_url('/blocks/new_titles/editfeed.php', $urlparams);
$PAGE->set_pagelayout('standard');

if ($rssid) {
    $isadding = false;
    $rssrecord = $DB->get_record('block_new_titles', array('id' => $rssid), '*', MUST_EXIST);
} else {
    $isadding = true;
    $rssrecord = new stdClass;
}

$mform = new feed_edit_form($PAGE->url, $isadding, $managefeeds);
$mform->set_data($rssrecord);

if ($mform->is_cancelled()) {
    redirect($managefeeds);

} else if ($data = $mform->get_data()) {
    $data->userid = $USER->id;
   
    if ($isadding) {
        $DB->insert_record('block_new_titles', $data);
    } else {
        $data->id = $rssid;
        $DB->update_record('block_new_titles', $data);
    }

    redirect($managefeedsurl);

} else {
    if ($isadding) {
        $strtitle = get_string('addnewlibrarytitle', 'block_new_titles');
    } else {
        $strtitle = get_string('editafeed', 'block_new_titles');
    }

    $PAGE->set_title($strtitle);
    $PAGE->set_heading($strtitle);

    $settingsurl = new moodle_url('/admin/settings.php?section=blocksettingnew_titles');
    $PAGE->navbar->add(get_string('blocks'));
    $PAGE->navbar->add(get_string('feedstitle', 'block_new_titles'), $settingsurl);
    $PAGE->navbar->add(get_string('managefeeds', 'block_new_titles'));
    $PAGE->navbar->add($strtitle);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle, 2);

    $mform->display();

    echo $OUTPUT->footer();
}
