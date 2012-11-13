<?php
if ($hassiteconfig) {
//  Add the import user cron to the Users/Accounts submenu
    $temp = new admin_settingpage('userimport', 'UoW User Import');
    $temp->add(new admin_setting_configtext('userimport_filelocation', 'File location', '', '', PARAM_PATH));
    $temp->add(new admin_setting_configselect('userimport_createpassword', 'Password field handling', '', 0, array('Field required in file','Create password if needed')));
    $temp->add(new admin_setting_configselect('userimport_updateaccounts', 'Update existing accounts', '', 0, array('No','Yes')));
    $temp->add(new admin_setting_configselect('userimport_allowrenames', 'Allow renames', '', 0, array('No','Yes')));

    $choices = array('Add duplicates', 'Skip duplicates', '');
    $temp->add(new admin_setting_configselect('userimport_duplicatehandling', 'Duplicated usernames', '', 0, $choices));

    //-> Default values
    $templateuser = get_admin();
    $temp->add(new admin_setting_configtext('userimport_username', get_string('username'), '', '', PARAM_TEXT));
    $auths = get_plugin_list('auth');
    $auth_options = array();
    foreach ($auths as $auth => $unused) {
        $auth_options[$auth] = get_string('pluginname', "auth_{$auth}");
    }
    $temp->add(new admin_setting_configselect('userimport_auth', get_string('chooseauthmethod','auth'), '', $templateuser->auth, $auth_options));
    $temp->add(new admin_setting_configtext('userimport_email', get_string('email'), '', '', PARAM_TEXT));
    $choices = array(get_string('emaildisplayno'), get_string('emaildisplayyes'), get_string('emaildisplaycourse'), '');
    $temp->add(new admin_setting_configselect('userimport_maildisplay', get_string('emaildisplay'), '', 2, $choices));
    $choices = array(get_string('emailenable'), get_string('emaildisable'), '');
    $temp->add(new admin_setting_configselect('userimport_emailstop', get_string('emailactive'), '', 0, $choices));
    $choices = array(get_string('textformat'), get_string('htmlformat'), '');
    $temp->add(new admin_setting_configselect('userimport_mailformat', get_string('emailformat'), '', 1, $choices));
    $choices = array(get_string('autosubscribeno'), get_string('autosubscribeyes'), '');
    $temp->add(new admin_setting_configselect('userimport_autosubscribe', get_string('autosubscribe'), '', 1, $choices));
    $choices = array(get_string('trackforumsno'), get_string('trackforumsyes'), '');
    if (!isset($CFG->forum_trackreadposts)) {
        $trackreadposts = 0;
    } else {
        $trackreadposts = $CFG->forum_trackreadposts;
    }
    $temp->add(new admin_setting_configselect('userimport_trackforums', get_string('trackforums'), '', $trackreadposts, $choices));
        
    $editors = editors_get_enabled();
    if (count($editors) > 1) {
        $choices = array(get_string('texteditor'), get_string('htmleditor'), '');
        $temp->add(new admin_setting_configselect('userimport_htmleditor', get_string('textediting'), '', 1, $choices));
    }
    $temp->add(new admin_setting_configtext('userimport_city', get_string('city'), '', $templateuser->city, PARAM_MULTILANG));
    $temp->add(new admin_setting_configselect('userimport_country', get_string('selectacountry'), '', $templateuser->country, get_string_manager()->get_list_of_countries()));
    $choices = get_list_of_timezones();
    $choices['99'] = get_string('serverlocaltime');
    $temp->add(new admin_setting_configselect('userimport_timezone', get_string('timezone'), '', $templateuser->timezone, $choices));
    $temp->add(new admin_setting_configselect('userimport_lang', get_string('preferredlanguage'), '', $templateuser->lang, get_string_manager()->get_list_of_translations()));
    $temp->add(new admin_setting_configtext('userimport_url', get_string('webpage'), '', '', PARAM_URL));
    $temp->add(new admin_setting_configtext('userimport_institution', get_string('institution'), '', $templateuser->institution, PARAM_MULTILANG));
    $temp->add(new admin_setting_configtext('userimport_department', get_string('department'), '', $templateuser->department, PARAM_MULTILANG));
    $temp->add(new admin_setting_configtext('userimport_phone1', get_string('phone'), '', '', PARAM_CLEAN));
    $temp->add(new admin_setting_configtext('userimport_phone2', get_string('phone'), '', '', PARAM_CLEAN));
    $temp->add(new admin_setting_configtext('userimport_address', get_string('address'), '', '', PARAM_MULTILANG));

    //if (!$ADMIN->locate('accounts')){ // Do we need to build 'accounts' category first
      //$ADMIN->add('users', new admin_category('accounts', get_string('accounts', 'admin')));
    //}
    $ADMIN->add('localplugins', $temp);
}