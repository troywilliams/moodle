<?php
define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions

// Need to run as a user. So tokens can be viewed in web interface.
cron_setup_user();

// Need to check if installed from web admin
cli_heading('******************************');
cli_heading('Setting up S.I.S webservice...');
cli_heading('******************************');

$version = get_config('local_sis', 'version');
if (empty($version)) {
    cli_error('plugin is not installed, go to admin notifications page to install plugin first before running this script.');
}
cli_output('Enabling web services');
$wsenabled = get_config('core', 'enablewebservices');
if ($wsenabled) {
    cli_output(' - already enabled');
} else {
    set_config('enablewebservices', 1);
    cli_output(' - enabled');
}
cli_output('Enable protocols');
$availablewebservices = array_keys(get_plugin_list('webservice'));
$activewebservices = empty($CFG->webserviceprotocols) ? array() : explode(',', $CFG->webserviceprotocols);
cli_output(' - checking for SOAP');
if (!in_array('soap', $availablewebservices)) {
    cli_error(' - does not exist check codebase now!');
} else {
    if (!in_array('soap', $activewebservices)) {
        $activewebservices[] = 'soap';
        set_config('webserviceprotocols', implode(',', $activewebservices));
        cli_output(' - enabled');
    } else {
        cli_output(' - already enabled');
    }
}   
cli_output('Turning on Web services authentication');
if (!exists_auth_plugin('webservice')) {
    cli_error(' - this plugin is not installed, check code!');
} else {
    $enabledauths = get_enabled_auth_plugins();
    if (in_array('webservice', $enabledauths)) {
        cli_output(' - already enabled');
    } else {
        $enabledauths[] = 'webservice';
        set_config('auth', implode(',', $enabledauths));
        cli_output(' - enabled');
    }
}

cli_output('Creating role');
$wsrole = $DB->get_record('role', array('shortname'=>'webservice'));
if ($wsrole) {
    cli_output(' - already exists');
} else {
    $wsrole = new stdClass;
    $wsrole->name = 'Web service';
    $wsrole->shortname = 'webservice';
    $wsrole->description = 'Used for external systems/users connecting to Moodle';
    $wsrole->id = create_role($wsrole->name, $wsrole->shortname, $wsrole->description);
    if (!$wsrole->id) {
        cli_error(' - couldn\'t create Web service role');
    }
    cli_output(' - created');
}
cli_output(' - setting context level to system');
set_role_contextlevels($wsrole->id, array(CONTEXT_SYSTEM));
cli_output(' - assigning required capabilities');
$systemcontext = get_system_context();
$requiredcaps = array(
    'moodle/webservice:createtoken',
    'webservice/soap:use',
    'coursereport/log:view',
    'moodle/grade:export',
    'moodle/grade:manage',
    'moodle/grade:viewall',
    'moodle/user:viewalldetails',
    'moodle/user:viewdetails',
    'moodle/user:viewhiddendetails',
 );
foreach ($requiredcaps as $requiredcap) {
    assign_capability($requiredcap, CAP_ALLOW, $wsrole->id, $systemcontext->id, true);
    cli_output(' - allowing capability '.$requiredcap);
}
mark_context_dirty($systemcontext->path);
cli_output('Create user account');
require_once($CFG->dirroot.'/user/lib.php');
$wsusername = 'sisws';
$wsuser = $DB->get_record('user', array('username'=>$wsusername));
if ($wsuser) {
    cli_output(' - already exists');
} else {
    $wsuser = new stdClass;
    $wsuser->auth = 'webservice';
    $wsuser->confirmed = 1;
    $wsuser->deleted = 0;
    $wsuser->username = $wsusername;

    $wsuser->firstname = 'S.I.S';
    $wsuser->lastname = 'Web service';
    $wsuser->email = $wsusername.'.system@127.0.0.1';
    $wsuser->city = 'Hamilton';
    $wsuser->country = 'NZ';
    $wsuser->timezone = 99;
    $password = 'H3yfru1ty#';
    $wsuser->password = $password;
    
    $wsuser->id = user_create_user($wsuser);
    if (!$wsrole->id) {
        cli_error(' - couldn\'t user sis');
    }
    cli_output(' - created password: '.$password);
}
cli_output('Add to Web service role');
role_assign($wsrole->id, $wsuser->id, $systemcontext->id);
cli_output(' - assigned');
cli_output('External service definition');
$servicename = 'S.I.S';
if ($DB->record_exists('external_services', array('name' => $servicename))) {
    cli_output(' - already exists');
} else {
    require_once($CFG->dirroot.'/webservice/lib.php');
    $service = new stdClass;
    $service->name = $servicename;
    $service->restrictedusers = 1;
    $service->enabled = 1;
    $wsfunctions = array('sis_get_course_activity_by_idnumber', 
                         'sis_get_bulk_course_activity', 
                         'sis_get_course_assessments_by_idnumber',
                         'sis_get_bulk_course_assessments',
                         'sis_get_course_results_by_idnumber',
                         'sis_get_bulk_course_results');
    $webservicemanager = new webservice();
    $service->id = $webservicemanager->add_external_service($service);
    cli_output(' - created');
    foreach ($wsfunctions as $wsfunction) { 
        $webservicemanager->add_external_function_to_service($wsfunction, $service->id);
    }
    cli_output(' - functions assigned to service');
    // Bullshit method
    $user = new stdClass;
    $user->externalserviceid = $service->id;
    $user->userid = $wsuser->id ;
    $webservicemanager->add_ws_authorised_user($user);
    cli_output(' - user account assigned to service');

    external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id,
                        $wsuser->id, get_context_instance(CONTEXT_SYSTEM),
                        0, '');
    cli_output(' - token generated');
    
}

$admin = $DB->get_record('user', array('username'=>'admin'));
$DB->set_field('external_tokens', 'creatorid', $admin->id, array('userid'=>$wsuser->id));
cli_output(' - set creator ontoken to admin, so can see in web inteface');

cli_output('Done');

/**
 * Write text
 * @param $text
 * @return void
 */
function cli_output($text){
    return fwrite(STDOUT, $text."\n");
}

?>
