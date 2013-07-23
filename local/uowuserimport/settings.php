<?php
if ($hassiteconfig) {
//  Add the import user cron to the Users/Accounts submenu
    $temp = new admin_settingpage('local_uowuserimport', 'UOW User Import');
    $temp->add(new admin_setting_configtext('local_uowuserimport/filelocation', 'File location', '', '', PARAM_PATH));
    $options = textlib::get_encodings();
    $temp->add(new admin_setting_configselect('local_uowuserimport/encoding', get_string('encoding', 'local_uowuserimport'), '', 'UTF-8', $options));
    
    $temp->add(new admin_setting_configselect('local_uowuserimport/allowupdate', 'Allow updates', '', 1, array('No','Yes')));
     
    // Default values
    $templateuser = get_admin();
    $auths = get_plugin_list('auth');
    $auth_options = array();
    foreach ($auths as $auth => $unused) {
        $auth_options[$auth] = get_string('pluginname', "auth_{$auth}");
    }
    
    $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_auth', get_string('chooseauthmethod','auth'), '', $templateuser->auth, $auth_options));
    $temp->add(new admin_setting_configtext('local_uowuserimport/defaultfield_city', get_string('city'), '', $templateuser->city, PARAM_MULTILANG));
    $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_country', get_string('selectacountry'), '', $templateuser->country, get_string_manager()->get_list_of_countries()));
    $choices = get_list_of_timezones();
    $choices['99'] = get_string('serverlocaltime');
    $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_timezone', get_string('timezone'), '', $templateuser->timezone, $choices));
    $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_lang', get_string('preferredlanguage'), '', $templateuser->lang, get_string_manager()->get_list_of_translations()));
    
    $choices = array(get_string('emaildisplayno'), get_string('emaildisplayyes'), get_string('emaildisplaycourse'), '');
    $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_maildisplay', get_string('emaildisplay'), '', 2, $choices));
    $choices = array(get_string('emailenable'), get_string('emaildisable'), '');
    $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_emailstop', get_string('emailactive'), '', 0, $choices));
    $choices = array(get_string('textformat'), get_string('htmlformat'), '');
    $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_mailformat', get_string('emailformat'), '', 1, $choices));
    $choices = array(get_string('autosubscribeno'), get_string('autosubscribeyes'), '');
    $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_autosubscribe', get_string('autosubscribe'), '', 1, $choices));
    $choices = array(get_string('trackforumsno'), get_string('trackforumsyes'), '');
    if (!isset($CFG->forum_trackreadposts)) {
        $trackreadposts = 0;
    } else {
        $trackreadposts = $CFG->forum_trackreadposts;
    }
    $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_trackforums', get_string('trackforums'), '', $trackreadposts, $choices));
        
    $editors = editors_get_enabled();
    if (count($editors) > 1) {
        $choices = array(get_string('texteditor'), get_string('htmleditor'), '');
        $temp->add(new admin_setting_configselect('local_uowuserimport/defaultfield_htmleditor', get_string('textediting'), '', 1, $choices));
    }
    
    /*
    
    $temp->add(new admin_setting_configselect('userimport_createpassword', 'Password field handling', '', 0, array('Field required in file','Create password if needed')));
    $temp->add(new admin_setting_configselect('userimport_updateaccounts', 'Update existing accounts', '', 0, array('No','Yes')));
    $temp->add(new admin_setting_configselect('userimport_allowrenames', 'Allow renames', '', 0, array('No','Yes')));

    */
    $ADMIN->add('localplugins', $temp);
}