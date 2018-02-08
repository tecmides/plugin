<?php

require_once(__DIR__ . "/BaseActiveRecord.class.php");
require_once(__DIR__ . '/Profile.class.php');

require_once(__DIR__ . '/../../../../grade/querylib.php');
require_once(__DIR__ . '/../../../../lib/gradelib.php');

class Activity extends BaseActiveRecord
{

    public $id;
    public $courseid;
    public $userid;
    public $grade;
    public $q_assign_view;
    public $q_assign_submit;
    public $q_forum_create;
    public $q_forum_group_access;
    public $q_forum_discussion_access;
    public $q_resource_view;
    public $timecreated;
    public $timemodified;

    public static function getTableName()
    {
        return "tecmides_activity";

    }

    public static function getPrimaryKey()
    {
        return "id";

    }

    public static function import( $courseid )
    {
        $profiles = Profile::findAll([ "courseid" => $courseid ]);

        if ( count($profiles) <= 0 )
        {
            return false;
        }

        $userCounters = self::getUserCounters($profiles);
        $maxCounters = self::getMaxCounters($userCounters);

        foreach ( $userCounters as $userid => $counters )
        {
            $activity = self::generateFromCounters($courseid, $userid, $counters, $maxCounters);
            $activity->save();
        }

        return true;

    }

    private static function getUserCounters( $profiles )
    {
        global $DB;
        
        $userCounters = [];

        foreach ( $profiles as $profile )
        {
            $counters = [
                "assign_view" => $DB->count_records("logstore_standard_log", [ "component" => 'mod_assign', "action" => 'viewed', "courseid" => $profile->courseid, "userid" => $profile->userid ]),
                "assign_submit" => $DB->count_records("logstore_standard_log", [ "component" => 'mod_assign', "action" => 'submitted', "courseid" => $profile->courseid, "userid" => $profile->userid ]),
                "forum_create" => $DB->count_records("logstore_standard_log", [ "component" => 'mod_forum', "action" => 'created', "courseid" => $profile->courseid, "userid" => $profile->userid ]),
                "forum_group_access" => $DB->count_records("logstore_standard_log", [ "component" => 'mod_forum', "action" => 'viewed', "target" => 'course_module', "courseid" => $profile->courseid, "userid" => $profile->userid ]),
                "forum_discussion_access" => $DB->count_records("logstore_standard_log", [ "component" => 'mod_forum', "action" => 'viewed', "target" => 'discussion', "courseid" => $profile->courseid, "userid" => $profile->userid ]),
                "resource_view" => $DB->count_records("logstore_standard_log", [ "component" => 'mod_resource', "action" => 'viewed', "courseid" => $profile->courseid, "userid" => $profile->userid ]),
            ];

            $userCounters[$profile->userid] = $counters;
        }

        return $userCounters;

    }

    private static function getMaxCounters( $activitiesCounters )
    {
        if ( count($activitiesCounters) <= 0 )
        {
            return [];
        }

        $maxes = array_values($activitiesCounters)[0];

        foreach ( $activitiesCounters as $counters )
        {
            foreach ( $maxes as $idcounter => $count )
            {
                if ( $counters[$idcounter] > $count )
                {
                    $maxes[$idcounter] = $counters[$idcounter];
                }
            }
        }

        return $maxes;

    }
    
    private static function generateFromCounters($courseid, $userid, $counters, $maxCounters) {
        $activity = Activity::findOne([ "courseid" => $courseid, "userid" => $userid ]);

        if ( is_null($activity) )
        {
            $activity = new Activity();
        }

        $activity->courseid = $courseid;
        $activity->userid = $userid;
        $activity->grade = preg_replace('/[\s0-9,\(\)]/', '', grade_get_course_grade($userid, $courseid)->str_grade)[0];

        foreach ( $counters as $counter => $value )
        {
            $quartile = self::getQuartile($value, $maxCounters[$counter]);

            $name = "q_{$counter}";

            $activity->$name = $quartile;
        }

        $currentTime = time();
        $activity->timecreated = ! is_null($activity->timecreated) ? $activity->timecreated : $currentTime;
        $activity->timemodified = $currentTime;
        
        return $activity;
    }
    
    private static function getQuartile( $value, $reference )
    {
        $q1 = round(($reference) * 0.25);
        $q2 = round(($reference) * 0.5);
        $q3 = round(($reference) * 0.75);

        if ( $value <= $q1 )
        {
            return QUARTILE_LOW;
        }
        else if ( $value > $q1 && $value <= $q2 )
        {
            return QUARTILE_MEDIUM;
        }
        else if ( $value > $q2 && $value <= $q3 )
        {
            return QUARTILE_MEDIUMHIGH;
        }
        else if ( $value > $q3 )
        {
            return QUARTILE_HIGH;
        }

    }

}