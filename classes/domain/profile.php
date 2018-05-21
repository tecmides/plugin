<?php

namespace tecmides\domain;

require_once(__DIR__ . "/base_active_record.php");

class profile extends base_active_record
{

    public $id;
    public $courseid;
    public $userid;
    public $st_indiv_assign_ltsubmit;
    public $st_group_assign_ltsubmit;
    public $st_indiv_subject_diff;
    public $rc_indiv_assign_ltsubmit;
    public $rc_group_assign_ltsubmit;
    public $rc_indiv_subject_keepup;
    public $rc_indiv_subject_diff;
    public $timecreated;

    public function __construct()
    {
        $this->timecreated = time();
    }

    public static function get_table_name()
    {
        return "tecmides_profile";

    }

    public static function get_primary_key()
    {
        return "id";

    }

    public static function get_labels()
    {
        return [
            "st_indiv_assign_ltsubmit" => get_string("question1", "report_tecmides"),
            "st_group_assign_ltsubmit" => get_string("question2", "report_tecmides"),
            "st_indiv_subject_diff" => get_string("question3", "report_tecmides"),
            "rc_indiv_assign_ltsubmit" => get_string("question4", "report_tecmides"),
            "rc_group_assign_ltsubmit" => get_string("question5", "report_tecmides"),
            "rc_indiv_subject_keepup" => get_string("question6", "report_tecmides"),
            "rc_indiv_subject_diff" => get_string("question7", "report_tecmides")
        ];
    }

}
