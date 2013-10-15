<?php
 
class block_explorancebpi_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Define specific configuration for the block url.
        $mform->addElement('header', 'config_header', get_string('blocksettingtitle', 'block_explorancebpi'));
        $mform->addElement('text', 'config_url', get_string('blockurl', 'block_explorancebpi'));
        $mform->addElement('text', 'config_height', get_string('customheight', 'block_explorancebpi'));        
    }
}
