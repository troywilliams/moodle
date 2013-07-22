<?php
defined('MOODLE_INTERNAL') || die();

class sis_get_course_activity_by_idnumber_form extends moodleform {
    public function definition() {
        global $CFG;
 
        $mform = $this->_form;
 
        $mform->addElement('header', 'wstestclienthdr', get_string('testclient', 'webservice'));
 
        //note: these values are intentionally PARAM_RAW - we want users to test any rubbish as parameters
        $data = $this->_customdata;
        if ($data['authmethod'] == 'simple') {
            $mform->addElement('text', 'wsusername', 'wsusername');
            $mform->addElement('text', 'wspassword', 'wspassword');
        } else  if ($data['authmethod'] == 'token') {
            $mform->addElement('text', 'token', 'token');
            $mform->setType('token', PARAM_RAW);
        }
        
        $mform->addElement('text', 'idnumber', 'idnumber');
        $mform->setType('idnumber', PARAM_RAW);
        
        $mform->addElement('hidden', 'function');
        $mform->setType('function', PARAM_SAFEDIR);
 
        $mform->addElement('hidden', 'protocol');
        $mform->setType('protocol', PARAM_SAFEDIR);
        
        $mform->addElement('hidden', 'authmethod', $data['authmethod']);
        $mform->setType('authmethod', PARAM_SAFEDIR);
 
        $mform->addElement('static', 'warning', '', get_string('executewarnign', 'webservice'));
 
        $this->add_action_buttons(true, get_string('execute', 'webservice'));
    }
 
    public function get_params() {
        if (!$data = $this->get_data()) {
            return null;
        }
        
        // remove unused from form data
        unset($data->submitbutton);
        unset($data->protocol);
        unset($data->function);
        unset($data->wsusername);
        unset($data->wspassword);
        unset($data->authmethod);
        
        $params = array();
        $params['idnumber'] = $data->idnumber;
 
        return $params;
    }
}

class sis_get_bulk_course_activity_form extends moodleform {
    public function definition() {
        global $CFG;
 
        $mform = $this->_form;
 
        $mform->addElement('header', 'wstestclienthdr', get_string('testclient', 'webservice'));
 
        //note: these values are intentionally PARAM_RAW - we want users to test any rubbish as parameters
        $data = $this->_customdata;
        if ($data['authmethod'] == 'simple') {
            $mform->addElement('text', 'wsusername', 'wsusername');
            $mform->addElement('text', 'wspassword', 'wspassword');
        } else  if ($data['authmethod'] == 'token') {
            $mform->addElement('text', 'token', 'token');
            $mform->setType('token', PARAM_RAW);
        }
        
        $mform->addElement('text', 'datetime', 'datetime');
        $mform->setDefault('datetime', ISO8601_from_epoch(time()));
        
        $mform->addElement('hidden', 'function');
        $mform->setType('function', PARAM_SAFEDIR);
 
        $mform->addElement('hidden', 'protocol');
        $mform->setType('protocol', PARAM_SAFEDIR);
        
        $mform->addElement('hidden', 'authmethod', $data['authmethod']);
        $mform->setType('authmethod', PARAM_SAFEDIR);
 
        $mform->addElement('static', 'warning', '', get_string('executewarnign', 'webservice'));
 
        $this->add_action_buttons(true, get_string('execute', 'webservice'));
    }
 
    public function get_params() {
        if (!$data = $this->get_data()) {
            return null;
        }
        
        // remove unused from form data
        unset($data->submitbutton);
        unset($data->protocol);
        unset($data->function);
        unset($data->wsusername);
        unset($data->wspassword);
        unset($data->authmethod);
        
        $params = array();
        $params['datetime'] = $data->datetime;
 
        return $params;
    }
}

class sis_get_course_assessments_by_idnumber_form extends moodleform {
    public function definition() {
        global $CFG;
 
        $mform = $this->_form;
 
        $mform->addElement('header', 'wstestclienthdr', get_string('testclient', 'webservice'));
 
        //note: these values are intentionally PARAM_RAW - we want users to test any rubbish as parameters
        $data = $this->_customdata;
        if ($data['authmethod'] == 'simple') {
            $mform->addElement('text', 'wsusername', 'wsusername');
            $mform->addElement('text', 'wspassword', 'wspassword');
        } else  if ($data['authmethod'] == 'token') {
            $mform->addElement('text', 'token', 'token');
            $mform->setType('token', PARAM_RAW);
        }
        
        $mform->addElement('text', 'idnumber', 'idnumber');
        $mform->setType('idnumber', PARAM_RAW);
        
        $mform->addElement('hidden', 'function');
        $mform->setType('function', PARAM_SAFEDIR);
 
        $mform->addElement('hidden', 'protocol');
        $mform->setType('protocol', PARAM_SAFEDIR);
        
        $mform->addElement('hidden', 'authmethod', $data['authmethod']);
        $mform->setType('authmethod', PARAM_SAFEDIR);
 
        $mform->addElement('static', 'warning', '', get_string('executewarnign', 'webservice'));
 
        $this->add_action_buttons(true, get_string('execute', 'webservice'));
    }
 
    public function get_params() {
        if (!$data = $this->get_data()) {
            return null;
        }
        
        // remove unused from form data
        unset($data->submitbutton);
        unset($data->protocol);
        unset($data->function);
        unset($data->wsusername);
        unset($data->wspassword);
        unset($data->authmethod);
        
        $params = array();
        $params['idnumber'] = $data->idnumber;
 
        return $params;
    }
}

class sis_get_bulk_course_assessments_form extends moodleform {
    public function definition() {
        global $CFG;
 
        $mform = $this->_form;
 
        $mform->addElement('header', 'wstestclienthdr', get_string('testclient', 'webservice'));
 
        //note: these values are intentionally PARAM_RAW - we want users to test any rubbish as parameters
        $data = $this->_customdata;
        if ($data['authmethod'] == 'simple') {
            $mform->addElement('text', 'wsusername', 'wsusername');
            $mform->addElement('text', 'wspassword', 'wspassword');
        } else  if ($data['authmethod'] == 'token') {
            $mform->addElement('text', 'token', 'token');
            $mform->setType('token', PARAM_RAW);
        }
        
        $mform->addElement('text', 'datetime', 'datetime');
        $mform->setDefault('datetime', ISO8601_from_epoch(time()));
        
        $mform->addElement('hidden', 'function');
        $mform->setType('function', PARAM_SAFEDIR);
 
        $mform->addElement('hidden', 'protocol');
        $mform->setType('protocol', PARAM_SAFEDIR);
        
        $mform->addElement('hidden', 'authmethod', $data['authmethod']);
        $mform->setType('authmethod', PARAM_SAFEDIR);
 
        $mform->addElement('static', 'warning', '', get_string('executewarnign', 'webservice'));
 
        $this->add_action_buttons(true, get_string('execute', 'webservice'));
    }
 
    public function get_params() {
        if (!$data = $this->get_data()) {
            return null;
        }
        // remove unused from form data
        unset($data->submitbutton);
        unset($data->protocol);
        unset($data->function);
        unset($data->wsusername);
        unset($data->wspassword);
 
        $params = array();
        $params['datetime'] = $data->datetime;
 
        return $params;
    }
}

class sis_get_course_results_by_idnumber_form extends moodleform {
    public function definition() {
        global $CFG;
 
        $mform = $this->_form;
 
        $mform->addElement('header', 'wstestclienthdr', get_string('testclient', 'webservice'));
 
        //note: these values are intentionally PARAM_RAW - we want users to test any rubbish as parameters
        $data = $this->_customdata;
        if ($data['authmethod'] == 'simple') {
            $mform->addElement('text', 'wsusername', 'wsusername');
            $mform->addElement('text', 'wspassword', 'wspassword');
        } else  if ($data['authmethod'] == 'token') {
            $mform->addElement('text', 'token', 'token');
            $mform->setType('token', PARAM_RAW);
        }
        
        $mform->addElement('text', 'idnumber', 'idnumber');
        $mform->setType('idnumber', PARAM_RAW);
        
        $mform->addElement('hidden', 'function');
        $mform->setType('function', PARAM_SAFEDIR);
 
        $mform->addElement('hidden', 'protocol');
        $mform->setType('protocol', PARAM_SAFEDIR);
        
        $mform->addElement('hidden', 'authmethod', $data['authmethod']);
        $mform->setType('authmethod', PARAM_SAFEDIR);
 
        $mform->addElement('static', 'warning', '', get_string('executewarnign', 'webservice'));
 
        $this->add_action_buttons(true, get_string('execute', 'webservice'));
    }
 
    public function get_params() {
        if (!$data = $this->get_data()) {
            return null;
        }
        // remove unused from form data
        unset($data->submitbutton);
        unset($data->protocol);
        unset($data->function);
        unset($data->wsusername);
        unset($data->wspassword);
 
        $params = array();
        $params['idnumber'] = $data->idnumber;

        return $params;
    }
}

class sis_get_bulk_course_results_form extends moodleform {
    public function definition() {
        global $CFG;
 
        $mform = $this->_form;
 
        $mform->addElement('header', 'wstestclienthdr', get_string('testclient', 'webservice'));
 
        //note: these values are intentionally PARAM_RAW - we want users to test any rubbish as parameters
        $data = $this->_customdata;
        if ($data['authmethod'] == 'simple') {
            $mform->addElement('text', 'wsusername', 'wsusername');
            $mform->addElement('text', 'wspassword', 'wspassword');
        } else  if ($data['authmethod'] == 'token') {
            $mform->addElement('text', 'token', 'token');
        }
        
        $mform->addElement('text', 'datetime', 'datetime');
        $mform->setDefault('datetime', ISO8601_from_epoch(time()));
        
        $mform->addElement('hidden', 'function');
        $mform->setType('function', PARAM_SAFEDIR);
 
        $mform->addElement('hidden', 'protocol');
        $mform->setType('protocol', PARAM_SAFEDIR);
        
        $mform->addElement('hidden', 'authmethod', $data['authmethod']);
        $mform->setType('authmethod', PARAM_SAFEDIR);
 
        $mform->addElement('static', 'warning', '', get_string('executewarnign', 'webservice'));
 
        $this->add_action_buttons(true, get_string('execute', 'webservice'));
    }
 
    public function get_params() {
        if (!$data = $this->get_data()) {
            return null;
        }
        // remove unused from form data
        unset($data->submitbutton);
        unset($data->protocol);
        unset($data->function);
        unset($data->wsusername);
        unset($data->wspassword);
 
        $params = array();
        $params['datetime'] = $data->datetime;
 
        return $params;
    }
}

?>
