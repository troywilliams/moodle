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
require_once($CFG->libdir . '/tablelib.php');

require_login();

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INT);
$deleterssid = optional_param('deleterssid', 0, PARAM_INT);

if ($courseid == SITEID) {
    $courseid = 0;
}
if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$managefeeds = has_capability('block/new_titles:managefeeds', $context);
require_capability('block/new_titles:managefeeds', $context);


$urlparams = array();
$extraparams = '';
if ($courseid) {
    $urlparams['courseid'] = $courseid;
    $extraparams = '&courseid=' . $courseid;
}
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
    $extraparams = '&returnurl=' . $returnurl;
}

$baseurl = new moodle_url('/blocks/new_titles/managefeeds.php', $urlparams);
$PAGE->set_url($baseurl);


// Process any actions
if ($deleterssid && confirm_sesskey()) {
    $DB->delete_records('block_new_titles', array('id'=>$deleterssid));

    redirect($PAGE->url, get_string('feeddeleted', 'block_new_titles'));
}


$feeds = $DB->get_records('block_new_titles', null, $DB->sql_order_by_text('preferredtitle'));

$strmanage = get_string('managefeeds', 'block_new_titles');

$PAGE->set_pagelayout('standard');
$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$settingsurl = new moodle_url('/admin/settings.php?section=blocksettingnew_titles');
$managefeeds = new moodle_url('/blocks/new_titles/managefeeds.php', $urlparams);
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('feedstitle', 'block_new_titles'), $settingsurl);
$PAGE->navbar->add(get_string('managefeeds', 'block_new_titles'), $managefeeds);
echo $OUTPUT->header();

$table = new flexible_table('new-library-titles-display-feeds');

$table->define_columns(array('feed', 'actions'));
$table->define_headers(array(get_string('feed', 'block_new_titles'), get_string('actions', 'moodle')));
$table->define_baseurl($baseurl);

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'rssfeeds');
$table->set_attribute('class', 'generaltable generalbox');
$table->column_class('feed', 'feed');
$table->column_class('actions', 'actions');

$table->setup();

foreach($feeds as $feed) {
    if (!empty($feed->preferredtitle)) {
        $feedtitle = s($feed->preferredtitle);
    } else {
        $feedtitle =  s($feed->title);
    }

    $viewlink = html_writer::link($CFG->wwwroot .'/blocks/new_titles/viewfeed.php?rssid=' . $feed->id . $extraparams, $feedtitle);

    $feedinfo = '<div class="title">' . $viewlink . '</div>' .
        '<div class="url">' . html_writer::link($feed->url, $feed->url) .'</div>' .
        '<div class="description">' . $feed->description . '</div>';

    $editurl = new moodle_url('/blocks/new_titles/editfeed.php?rssid=' . $feed->id . $extraparams);
    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));

    $deleteurl = new moodle_url('/blocks/new_titles/managefeeds.php?deleterssid=' . $feed->id . '&sesskey=' . sesskey() . $extraparams);
    $deleteicon = new pix_icon('t/delete', get_string('delete'));
    $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon, new confirm_action(get_string('deletefeedconfirm', 'block_new_titles')));

    $feedicons = $editaction . ' ' . $deleteaction;

    $table->add_data(array($feedinfo, $feedicons));
}

$table->print_html();

$url = $CFG->wwwroot . '/blocks/new_titles/editfeed.php?' . substr($extraparams, 1);
echo '<div class="actionbuttons">' . $OUTPUT->single_button($url, get_string('addnewlibrarytitle', 'block_new_titles'), 'get') . '</div>';


if ($returnurl) {
    echo '<div class="backlink">' . html_writer::link($returnurl, get_string('back')) . '</div>';
}

echo $OUTPUT->footer();
