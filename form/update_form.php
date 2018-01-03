<?php

require_once("$CFG->libdir/formslib.php");
 
class update_form extends moodleform {
    
    public function definition() {
        global $CFG;
 
        $mform = $this->_form;
        
        $mform->addElement('header', 'questionary_info_header', get_string('updatequestionary', 'report_tecmides'));
        $mform->addElement('filepicker', 'questionary_info', get_string('updatequestionary', 'report_tecmides'));
        $mform->addElement('submit', 'submitbutton', get_string('update'));
        
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}