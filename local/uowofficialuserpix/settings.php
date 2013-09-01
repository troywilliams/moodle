<?php
if ($hassiteconfig) {
    $temp = new admin_settingpage('uowofficialuserpix', get_string('pluginname', 'local_uowofficialuserpix'));
    $temp->add(new admin_setting_configtext('local_uowofficialuserpix/repodir', 'Image Repository', 'Enter the network address of the image repository folder', '', PARAM_PATH));
    $temp->add(new admin_setting_configcheckbox('local_uowofficialuserpix/all', 'Import all', 'Check if you want to import images for all partisipant or leave blank if only for students', 0));
    $ADMIN->add('localplugins', $temp);
}
