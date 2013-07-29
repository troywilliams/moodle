<?php

/**
 * Script to let a user edit the properties of a particular RSS feed.
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir .'/simplepie/moodle_simplepie.php');

require_login();
if (isguestuser()) {
    print_error('guestsarenotallowed');
}

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INTEGER);
$rssid = required_param('rssid', PARAM_INTEGER);

if ($courseid = SITEID) {
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

$urlparams = array('rssid' => $rssid);
if ($courseid) {
    $urlparams['courseid'] = $courseid;
}
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}
$PAGE->set_url('/blocks/new_titles/viewfeed.php', $urlparams);
$PAGE->set_pagelayout('popup');

$rssrecord = $DB->get_record('block_new_titles', array('id' => $rssid), '*', MUST_EXIST);

$rss = new moodle_simplepie($rssrecord->url);

if ($rss->error()) {
    debugging($rss->error());
    print_error('errorfetchingrssfeed');
}

$strviewfeed = get_string('viewfeed', 'block_new_titles');

$PAGE->set_title($strviewfeed);
$PAGE->set_heading($strviewfeed);

$settingsurl = new moodle_url('/admin/settings.php?section=blocksettingnew_titles');
$managefeeds = new moodle_url('/blocks/new_titles/managefeeds.php', $urlparams);
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('feedstitle', 'block_new_titles'), $settingsurl);
$PAGE->navbar->add(get_string('managefeeds', 'block_new_titles'));
$PAGE->navbar->add($strviewfeed);
echo $OUTPUT->header();

if (!empty($rssrecord->preferredtitle)) {
    $feedtitle = $rssrecord->preferredtitle;
} else {
    $feedtitle =  $rss->get_title();
}
echo '<table align="center" width="50%" cellspacing="1">'."\n";
echo '<tr><td colspan="2"><strong>'. $feedtitle .'</strong></td></tr>'."\n";
foreach ($rss->get_items() as $item) {
    echo '<tr><td valign="middle">'."\n";
    echo '<a href="'. $item->get_link() .'" target="_blank"><strong>'. $item->get_title();
    echo '</strong></a>'."\n";
    echo '</td>'."\n";
    echo '</tr>'."\n";
    echo '<tr><td colspan="2"><small>';
    echo $item->get_description() .'</small></td></tr>'."\n";
}
echo '</table>'."\n";

echo $OUTPUT->footer();
