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
 * Student list report
 * 
 * @package report_studentlist
 * @copyright 2013 The University of Waikato
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/report/studentlist/lib.php');
require_once($CFG->dirroot . '/report/studentlist/locallib.php');
require_once($CFG->dirroot . '/report/studentlist/renderer.php');

$id           = required_param('id', PARAM_INT); // course id
$page         = optional_param('page', 0, PARAM_INT); // which page to show
$search       = optional_param('search', '', PARAM_RAW);
$sort         = optional_param('sort', 'lastnameaz', PARAM_ALPHANUM);
$showall      = optional_param('showall', 0, PARAM_INT); // which page to show

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($course->id);

require_capability('report/studentlist:view', $context);

if (report_studentlist_issite()) {
   throw new invalid_parameter_exception('SITEID really, come on...');
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(new lang_string('studentlistfor', 'report_studentlist', $course->shortname));

$pageparams = array('id' => $course->id);
$pageurl    = new moodle_url('/report/studentlist/index.php', $pageparams);

$pageurl->param('sort', $sort);

$PAGE->set_url($pageurl);
$PAGE->requires->css(new moodle_url('http://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css'));

$bulkoperations = has_capability('moodle/course:bulkmessaging', $context);

$output = $PAGE->get_renderer('report_studentlist');

$studentlist = new report_studentlist($course);
$studentlist->set_search_filter($search);
$studentlist->set_order_by($sort);
if ($showall) {
    $users = $studentlist->all();
} else {
    $users = $studentlist->page($page);
}
$currentlydisplaying = count($users);
$totalmatches = $studentlist->total_matches();

$showallavailable = true;
if ($currentlydisplaying == $totalmatches) {
    $showallavailable = false;
}

$a = new stdClass();
$a->displayingfrom = ($page) ? $page * report_studentlist::DEFAULT_PAGE_SIZE : 1;
$a->displayingto = $page * report_studentlist::DEFAULT_PAGE_SIZE + $currentlydisplaying;
$a->total = $totalmatches;

$pagination = new paging_bar($studentlist->total_matches(), $page, report_studentlist::DEFAULT_PAGE_SIZE, $pageurl);

echo $OUTPUT->header();
echo $OUTPUT->heading(new lang_string('studentlistfor', 'report_studentlist', $course->shortname));

echo html_writer::start_div('input-append');
echo html_writer::start_tag('form', array('action'=>$pageurl->out(false), 'id'=>'searchform'));
echo html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id', 'value'=>$course->id));
$attributes = array('name'=>'search', 'type'=>'text', 'value'=>$search , 'class'=>'span6', 'autocomplete'=>'off');
echo html_writer::empty_tag('input', $attributes);
echo html_writer::tag('button', new lang_string('search'), array('class'=>'btn', 'type'=>'submit'));
$clearurl = clone($pageurl);
$clearurl->remove_params(array('page','sort','search'));
echo html_writer::link($clearurl, new lang_string('clear'), array('class'=>'btn'));
echo html_writer::end_tag('form');
echo html_writer::end_div();

echo html_writer::start_div('manage-groups');
$url = new moodle_url('/group/advanced_assign.php', array('courseid' => $course->id));
echo html_writer::link($url, 'Manage groups', array('class'=>'btn'));
echo html_writer::end_div();

echo $output->sort_by_dropdown(report_studentlist::sort_by_options());

$html = '';
$html .= html_writer::start_div('listing-meta-info');
$html .= html_writer::tag('span', get_string('displaying', 'report_studentlist', $a));
$html .= html_writer::tag('span', get_string('displayingmeta', 'report_studentlist', $a), array('class'=>'pull-right'));
$html .= html_writer::end_div();
echo $html;

if ($bulkoperations) {
    $formurl = new moodle_url('/report/studentlist/action_redir.php');
    echo '<form action="'.$formurl->out(false).'" method="post" id="participantsform">';
    echo '<input type="hidden" name="id" value="'.$course->id.'" />';
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
    echo '<input type="hidden" name="returnto" value="'.s($PAGE->url->out(false)).'" />';
}
// output list
echo $output->student_list($users);
//echo html_writer::div($html, '', array('id'=>'studentlist'));

if ($showallavailable) {
    echo $OUTPUT->render($pagination);
    $pageurl->param('showall', 1);
    echo html_writer::link($pageurl, get_string('showall', '', $totalmatches), array('class'=>'pull-right hidden-print'));
}
if ($bulkoperations) {
    echo '<br/>';
    echo '<div class="buttons">';
    echo '<input type="button" id="checkall" value="'.get_string('selectall').'" /> ';
    echo '<input type="button" id="checknone" value="'.get_string('deselectall').'" /> ';
    $displaylist = array();
    $displaylist['messageselect.php'] = get_string('messageselectadd');
    echo html_writer::tag('label', get_string("withselectedusers"), array('for'=>'formactionid'));
    echo html_writer::select($displaylist, 'formaction', '', array(''=>'choosedots'), array('id'=>'formactionid'));
    echo '</div>';

    echo '<noscript style="display:inline">';
    echo '<div><input type="submit" value="'.get_string('ok').'" /></div>';
    echo '</noscript>';
    echo '</form>';

    $module = array('name'=>'core_user', 'fullpath'=>'/user/module.js');
    $PAGE->requires->js_init_call('M.core_user.init_participation', null, false, $module);
}
echo $OUTPUT->footer();

exit;
