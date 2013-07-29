<?php
//defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/logger/logger.php');
    
//  Check that the user is allow to perforn this task (i.e. admin)        
require_login();
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('edittokenrestrictions');


$stroperation = get_string('operation', 'webservice');
$strtoken = get_string('token', 'webservice');
$strservice = get_string('service', 'webservice');
$struser = get_string('user');
$strcontext = get_string('context', 'webservice');
$strvaliduntil = get_string('validuntil', 'webservice');
$striprestriction = get_string('iprestriction', 'webservice');
echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox webservicestokenui');

$table = new html_table();
$table->head  = array($strtoken, $struser, $strservice, $striprestriction, $strvaliduntil, $stroperation);
$table->colclasses = array('leftalign', 'leftalign', 'leftalign', 'centeralign', 'centeralign', 'centeralign');
$table->id = 'webservicetokens';
$table->attributes['class'] = 'admintable generaltable';
$table->data  = array();

$tokenpageurl = "$CFG->wwwroot/local/uowedittokenrestriction/tokens.php?sesskey=" . sesskey();


// allow siteamins to see and delete any
if (is_siteadmin()) {
    $sql = "SELECT t.id, t.token, u.id AS userid, u.firstname, u.lastname, s.name, t.iprestriction, t.validuntil, s.id AS serviceid
              FROM {external_tokens} t, {user} u, {external_services} s
             WHERE t.tokentype = ? AND s.id = t.externalserviceid AND t.userid = u.id";
    $params = array(EXTERNAL_TOKEN_PERMANENT);
} else {
    $sql = "SELECT t.id, t.token, u.id AS userid, u.firstname, u.lastname, s.name, t.iprestriction, t.validuntil, s.id AS serviceid
              FROM {external_tokens} t, {user} u, {external_services} s
             WHERE t.creatorid=? AND t.tokentype = ? AND s.id = t.externalserviceid AND t.userid = u.id";
    $params = array($USER->id, EXTERNAL_TOKEN_PERMANENT);
}
$tokens = $DB->get_records_sql($sql, $params);
if (!empty($tokens)) {
    foreach ($tokens as $token) {
        //TODO: retrieve context
        $edit = "<a href=\"".$tokenpageurl."&amp;action=edit&amp;tokenid=".$token->id."\">";
        $edit .= get_string('edit')."</a>";
        
        $delete = "<a href=\"".$tokenpageurl."&amp;action=delete&amp;tokenid=".$token->id."\">";
        $delete .= get_string('delete')."</a>";

        $validuntil = '';
        if (!empty($token->validuntil)) {
            $validuntil = userdate($token->validuntil, get_string('strftimedatetime', 'langconfig'));
        }

        $iprestriction = '';
        if (!empty($token->iprestriction)) {
            $iprestriction = $token->iprestriction;
        }

        $userprofilurl = new moodle_url('/user/profile.php?id='.$token->userid);
        $useratag = html_writer::start_tag('a', array('href' => $userprofilurl));
        $useratag .= $token->firstname." ".$token->lastname;
        $useratag .= html_writer::end_tag('a');

        //check user missing capabilities
        require_once($CFG->dirroot . '/webservice/lib.php');
        $webservicemanager = new webservice();
        $usermissingcaps = $webservicemanager->get_missing_capabilities_by_users(
                array(array('id' => $token->userid)), $token->serviceid);

        if (!is_siteadmin($token->userid) and
                array_key_exists($token->userid, $usermissingcaps)) {
            $missingcapabilities = implode(', ',
                    $usermissingcaps[$token->userid]);
            if (!empty($missingcapabilities)) {
                $useratag .= html_writer::tag('div',
                                get_string('usermissingcaps', 'webservice',
                                        $missingcapabilities)
                                . '&nbsp;' . $OUTPUT->help_icon('missingcaps', 'webservice'),
                                array('class' => 'missingcaps'));
            }
        }

        $table->data[] = array($token->token, $useratag, $token->name, $iprestriction, $validuntil, $edit.'&nbsp;'.$delete);
    }

    echo html_writer::table($table);
} else {
    echo get_string('notoken', 'webservice');
}

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

