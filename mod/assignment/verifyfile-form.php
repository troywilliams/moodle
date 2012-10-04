<?php

class assignment_verifyfile_form extends moodleform {

    function definition() {
        global $COURSE;
        $mform    =& $this->_form;
        $id = $this->_customdata['id'];
        
        $mform->addElement('text', 'receipt', get_string('receipt', 'assignment'), array('size' => 60, 'maxlength' => 47));
        $mform->addRule('receipt', null, 'required');
        $mform->addRule('receipt', null, 'minlength', 47, 'client');
        $mform->addRule('receipt', null, 'maxlength', 47, 'client');
        $maxbytes =  $COURSE->maxbytes;
        $mform->addElement('filemanager', 'attachment', get_string('uploadafile', 'assignment'), null,
                    array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1, 'accepted_types' => array('*')));

        $this->add_action_buttons(true, get_string('verifyfile', 'assignment'));

        $mform->addElement('hidden', 'id', $id);
    }

}
?>