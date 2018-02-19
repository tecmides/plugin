<?php

class domain_profile extends domain_base_active_record
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

    public static function get_table_name()
    {
        return "tecmides_profile";

    }

    public static function get_primary_key()
    {
        return "id";

    }

}
