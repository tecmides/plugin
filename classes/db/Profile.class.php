<?php

require_once(__DIR__ . "/BaseActiveRecord.class.php");

class Profile extends BaseActiveRecord
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
    
    public static function getTableName()
    {
        return "tecmides_profile";

    }

    public static function getPrimaryKey()
    {
        return "id";

    }

}