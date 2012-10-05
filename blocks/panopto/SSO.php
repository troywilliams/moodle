<?php
require('../../config.php');
require_once($CFG->dirroot. '/blocks/panopto/lib/block_panopto_lib.php');
require_once($CFG->libdir . '/weblib.php');

$server_name            = required_param('serverName', PARAM_RAW);
$callback_url           = required_param('callbackURL', PARAM_RAW);
$expiration             = required_param('expiration', PARAM_RAW);
$request_auth_code      = required_param('authCode', PARAM_RAW);
$action                 = optional_param('action', '', PARAM_RAW);

// Page setup
$pageparams = array('authCode'=>$request_auth_code,
                    'serverName'=>$server_name,
                    'expiration'=>$expiration,
                    'callbackURL'=>urlencode($callback_url));
$pageurl = new moodle_url('/blocks/panopto/SSO.php', $pageparams);
$PAGE->set_url($pageurl);
$syscontext = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($syscontext);

$relogin = ($action == "relogin"); 

if($relogin || (isset($USER->username) && ($USER->username == "guest")))
{
	require_logout();

	// Return to this page, minus the "action=relogin" parameter.
        $redirecturl = $CFG->wwwroot .
                       '/blocks/panopto/SSO.php?' .
		       "authCode=$request_auth_code" .
		       "&serverName=$server_name" .
		       "&expiration=$expiration" .
		       "&callbackURL=" . urlencode($callback_url);
	redirect($redirecturl);
	return;
}

// No course ID (0).  Don't autologin guests (false).
require_login(0, false);

// Reproduce canonically-ordered incoming auth payload.
$request_auth_payload = "serverName=" . $server_name . "&expiration=" . $expiration;

// Verify passed in parameters are properly signed.
if(validate_auth_code($request_auth_payload, $request_auth_code))
{
	$user_key = decorate_username($USER->username);

	// Generate canonically-ordered auth payload string
	$response_params = "serverName=" . $server_name . "&externalUserKey=" . $user_key . "&expiration=" . $expiration;
	// Sign payload with shared key and hash.
	$response_auth_code = generate_auth_code($response_params);
	
	$separator = (strpos($callback_url, "?") ? "&" : "?");
	$redirect_url = $callback_url . $separator . $response_params . "&authCode=" . $response_auth_code;  
	
	// Redirect to Panopto CourseCast login page.
	redirect($redirect_url);
}
else
{
	echo $OUTPUT->header();

        print_error('Invalid auth code! Contact your system administrator.');

	echo $OUTPUT->footer();
}
