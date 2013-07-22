<?php
if ($hassiteconfig) {
    $temp = new admin_settingpage('userpiximport', get_string('pluginname', 'local_uowofficialuserpix'));
    $temp->add(new admin_setting_configtext('userpiximport_repodir', 'Image Repository', 'Enter the network address of the image repository folder', '', PARAM_PATH));
    $temp->add(new admin_setting_configcheckbox('userpiximport_all', 'Import all', 'Check if you want to import images for all partisipant or leave blank if only for students', 0));
    $ADMIN->add('localplugins', $temp);
}
