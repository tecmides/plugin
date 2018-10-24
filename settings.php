<?php

defined("MOODLE_INTERNAL") || die;
if ($ADMIN->fulltree) {
    $settings->add( new admin_setting_configcheckbox( "TecmidesSendTrainingData", get_string( "TecmidesSendTrainingData", "report_tecmides" ), get_string( "TecmidesSendTrainingData_description", "report_tecmides" ), 1) );
    $settings->add( new admin_setting_configtext( "TecmidesWebserviceURL", get_string( "TecmidesWebserviceURL", "report_tecmides" ), get_string( "TecmidesWebserviceURL_description", "report_tecmides" ), "http://test-tecmides.url/" ) );
    
    $availablemodels = [
        "rule_model" => get_string("rule_model_name", "report_tecmides"),
        "tree_model" => get_string("tree_model_name", "report_tecmides")
    ];
    
    $settings->add( new admin_setting_configselect("TecmidesModel", get_string( "TecmidesModel", "report_tecmides" ), get_string( "TecmidesModel_description", "report_tecmides" ), "rule_model", $availablemodels));
}