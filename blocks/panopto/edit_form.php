<?php
require_once($CFG->dirroot.'/blocks/panopto/lib/panopto_data.php');
class block_panopto_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB, $USER, $COURSE, $PAGE;
        
        $servername = get_servername_setting();
        $instancename = get_instancename_setting();
        $applicationkey = get_application_key_setting();

        if (!empty($servername) && !empty($instancename) && !empty($applicationkey)) {
            $selectlist = array();
            $selecteditem = false;
            // Construct the Panopto data proxy object
            $panoptodata = new panopto_data($COURSE->id);
            // Check course has a associated foldermap entry
            if ($DB->record_exists('block_panopto_foldermap', array('moodleid'=>$COURSE->id))) {
                $courseinfo = $panoptodata->get_course();
                if ($courseinfo->Access != 'Error') {
                    $selecteditem = $courseinfo->PublicID;
                }
            }
            // Get available Panopto folders
            $panoptofolders = $panoptodata->get_courses();
             if (!empty($panoptofolders)) {
                 foreach($panoptofolders as $panoptofolder) {
                     if ($panoptofolder->Access == 'Viewer') {
                         continue; // skip courses with viewer access
                     }
                     $displayname = s($panoptofolder->DisplayName);
                     $selectlist[$panoptofolder->Access][$panoptofolder->PublicID] = shorten_text($displayname, 80);
                 }
             }
             // Fields for editing block contents.
            $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
            // No match so need to allow for provisioning the folder on Panopto
            if (!$selecteditem) {
                $html = '';
                $html .= '<p>No existing folder found linked to this Paper on the Panopto server<p>';
                $createfolderurl = new moodle_url('/blocks/panopto/createfolder.php', array('courseid'=>$COURSE->id));//, 'returnurl'=>urlencode($PAGE->url.'&sesskey='.sesskey())
                $html .= html_writer::link($createfolderurl, html_writer::tag('h2', 'Create new folder'));
                $html .= '<p>or, select an existing folder:<p>';
                $mform->addElement('html', $html);
                // We have to use dirty ol config_ prefix
                //$elselect  = &$mform->addElement('selectgroups', 'config_mappedfolder', '', $selectlist, array('disabled'), false);
                $elselect  = &$mform->addElement('selectgroups', 'config_mappedfolder', '', $selectlist, array('disabled'), true);
                $mform->addElement('html', '<input type="button" name="'.get_string('edit').'" value="'.get_string('edit').'" onclick="document.getElementById(\'id_config_mappedfolder\').disabled = false" /><br/><br/>');
            } else {
                $elselect  = &$mform->addElement('selectgroups', 'config_mappedfolder', 'Currently using folder:', $selectlist, array('disabled'), false);
                $mform->addElement('html', '<input type="button" name="'.get_string('edit').'" value="'.get_string('edit').'" onclick="document.getElementById(\'id_config_mappedfolder\').disabled = false" /><br/><br/>');
                $elselect->setSelected($selecteditem);
            }
            $mform->addElement('hidden', 'config_courseid', $COURSE->id);
            $mform->setType('config_courseid', PARAM_INT);
        } else {
          $notice = '<div class="error">Cannot configure block instance: Global configuration incomplete.<br />Please contact your system administrator.</div>';
          $mform->addElement('html', $notice);
        }
    }
}
