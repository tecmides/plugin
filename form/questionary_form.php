<?php

require(__DIR__ . '/../constants.php');

require_once("$CFG->libdir/formslib.php");
 
class questionary_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        
        $hidden = $mform->createElement("hidden", "st_indiv_assign_ltsubmit");
        $hidden->setType(PARAM_RAW);
        $mform->addElement($hidden);
        $mform->addElement('header', 'questionary_info_header', get_string('questionheader', 'report_tecmides') . " 1");
        $mform->addElement('html', "<div id='gew-q1' class='gew' data-input='st_indiv_assign_ltsubmit' data-required></div>");
        
        $hidden = $mform->createElement("hidden", "st_group_assign_ltsubmit");
        $hidden->setType(PARAM_RAW);
        $mform->addElement($hidden);
        $mform->addElement('header', 'questionary_info_header', get_string('questionheader', 'report_tecmides') . " 2");
        $mform->addElement('html', "<div id='gew-q2' class='gew' data-input='st_group_assign_ltsubmit' data-required></div>");
        
        $hidden = $mform->createElement("hidden", "st_indiv_subject_diff");
        $hidden->setType(PARAM_RAW);
        $mform->addElement($hidden);
        $mform->addElement('header', 'questionary_info_header', get_string('questionheader', 'report_tecmides') . " 3");
        $mform->addElement('html', "<div id='gew-q3' class='gew' data-input='st_indiv_subject_diff' data-required></div>");
        
        $mform->addElement('header', 'questionary_info_header', get_string('questionheader', 'report_tecmides') . " 4");
        $mform->addElement('select', 'rc_indiv_assign_ltsubmit', get_string('forumtype', 'forum'), RECURRENCYS);
        
        $mform->addElement('header', 'questionary_info_header', get_string('questionheader', 'report_tecmides') . " 5");
        $mform->addElement('select', 'rc_group_assign_ltsubmit', get_string('forumtype', 'forum'), RECURRENCYS);
        
        $mform->addElement('header', 'questionary_info_header', get_string('questionheader', 'report_tecmides') . " 6");
        $mform->addElement('select', 'rc_indiv_subject_keepup', get_string('forumtype', 'forum'), RECURRENCYS);
        
        $mform->addElement('header', 'questionary_info_header', get_string('questionheader', 'report_tecmides') . " 7");
        $mform->addElement('select', 'rc_indiv_subject_diff', get_string('forumtype', 'forum'), RECURRENCYS);
        
        $mform->addElement('submit', 'submitbutton', get_string('update'));
        
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}