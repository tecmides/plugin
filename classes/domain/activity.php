<?php

namespace tecmides\domain;

require_once(__DIR__ . '/../../../../lib/gradelib.php');
require_once(__DIR__ . '/../../../../grade/querylib.php');

require_once(__DIR__ . "/base_active_record.php");
require_once(__DIR__ . "/profile.php");

class activity extends base_active_record
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

    public static function get_table_name()
    {
        return "tecmides_activity";

    }

    public static function get_primary_key()
    {
        return "id";

    }

    public static function import( $courseid )
    {
        $profiles = profile::find_all([ "courseid" => $courseid ]);

        if ( count($profiles) <= 0 )
        {
            return false;
        }

        $user_counters = self::get_user_counters($profiles);
        $list_counters = self::get_list_counters($user_counters);
        $quartiles = self::generate_quartiles($list_counters);

        foreach ( $user_counters as $userid => $counters )
        {
            $activity = self::generate_from_counters($courseid, $userid, $counters, $quartiles);
            $activity->save();
        }

        return true;

    }

    private static function get_user_counters( $profiles )
    {
        global $DB;

        $user_counters = [];

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

            $user_counters[$profile->userid] = $counters;
        }

        return $user_counters;

    }

    private static function get_list_counters( $activities_counters )
    {
        if ( count($activities_counters) <= 0 )
        {
            return [];
        }

        $list_counters = [];

        foreach ( $activities_counters as $counters )
        {
            foreach ( array_keys($counters) as $counterid )
            {
                if ( ! isset($list_counters[$counterid]) )
                {
                    $list_counters[$counterid] = [];
                }

                $list_counters[$counterid][] = $counters[$counterid];
            }
        }

        foreach ( array_keys($list_counters) as $key )
        {
            sort($list_counters[$key]);
        }

        return $list_counters;

    }

    private static function generate_quartiles( $list )
    {
        $quartiles = [];

        foreach ( $list as $key => $values )
        {
            $n = count($values);

            $q1 = self::getMedian(array_slice($values, 0, floor($n / 2)));
            $q2 = self::getMedian($values);
            $q3 = self::getMedian(array_slice($values, ceil($n / 2), $n));

            $quartiles[$key] = [ $q1, $q2, $q3 ];
        }

        return $quartiles;

    }

    private static function getMedian( $reference )
    {
        $n = count($reference);

        if ( $n % 2 == 0 )
        {
            $q = ($reference[$n / 2] + $reference[($n / 2) - 1]) / 2;
        }
        else
        {
            $q = $reference[$n / 2];
        }

        return $q;

    }

    private static function generate_from_counters( $courseid, $userid, $counters, $quartiles )
    {
        $activity = activity::find_one([ "courseid" => $courseid, "userid" => $userid ]);

        if ( is_null($activity) )
        {
            $activity = new activity();
        }

        $activity->courseid = $courseid;
        $activity->userid = $userid;
        $activity->grade = preg_replace('/[\s0-9,\(\)]/', '', \grade_get_course_grade($userid, $courseid)->str_grade)[0];

        foreach ( $counters as $counterid => $value )
        {
            $quartile = self::classify_by_quartiles($value, $quartiles[$counterid]);

            $name = "q_{$counterid}";

            $activity->$name = $quartile;
        }

        $currentTime = time();
        $activity->timecreated = ! is_null($activity->timecreated) ? $activity->timecreated : $currentTime;
        $activity->timemodified = $currentTime;

        return $activity;

    }

    private static function classify_by_quartiles( $value, $reference )
    {
        if ( $value <= $reference[0] )
        {
            return QUARTILE_LOW;
        }
        else if ( $value > $reference[0] && $value <= $reference[1] )
        {
            return QUARTILE_MEDIUM;
        }
        else if ( $value > $reference[1] && $value <= $reference[2] )
        {
            return QUARTILE_MEDIUMHIGH;
        }
        else if ( $value > $reference[2] )
        {
            return QUARTILE_HIGH;
        }

    }

    public static function get_labels()
    {
        return [
            "grade" => get_string("grade", "report_tecmides"),
            "q_assign_view"  => get_string("q_assign_view", "report_tecmides"),
            "q_assign_submit" => get_string("q_assign_submit", "report_tecmides"),
            "q_forum_create" => get_string("q_forum_create", "report_tecmides"),
            "q_forum_group_access" => get_string("q_group_access", "report_tecmides"),
            "q_forum_discussion_access" => get_string("q_forum_discussion_access", "report_tecmides"),
            "q_resource_view" => get_string("q_resource_view", "report_tecmides")
        ];
    }
}
