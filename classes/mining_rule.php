<?php

require_once(__DIR__ . "/mining.php");
require_once(__DIR__ . "/domain_activity.php");
require_once(__DIR__ . "/domain_profile.php");

class mining_rule implements mining
{

    public function get_rules( minerator $minerator )
    {
        return $minerator->generate_rules($this->get_mining_data(), $this->get_mining_data_header());

    }

    public function get_matching_students( $courseid, $rules )
    {
        $students = $this->get_students($courseid);

        foreach ( $students as $student )
        {
            $student->matches = [];
        }

        foreach ( $rules as $rule )
        {
            $matchingStudents = $this->query_rule($rule, $courseid);

            foreach ( $matchingStudents as $userid )
            {
                $students[$userid]->matches[] = $this->beautify_rule($rule);
            }
        }

        return $students;

    }

    private function get_mining_data()
    {
        global $DB;

        $sql = sprintf("SELECT i.userid, %s FROM %s as i INNER JOIN %s as q ON i.courseid = q.courseid AND i.userid = q.userid", implode(",", self::get_mining_attributes()), ACTIVITY_TABLE, PROFILE_TABLE);

        $users = $DB->get_records_sql($sql);

        return array_values($users);

    }

    public static function get_mining_attributes()
    {
        $ignoreColumns = [ "id", "courseid", "userid", "timecreated", "timemodified" ];
        $activityColumns = array_diff(domain_activity::get_attributes(), $ignoreColumns);
        $profileColumns = array_diff(domain_profile::get_attributes(), $ignoreColumns);

        return array_merge($activityColumns, $profileColumns);

    }

    private function get_mining_data_header()
    {
        $quartileRange = [ "0", "1", "2", "3" ];
        $mindstateRange = [ "0", "1", "2", "3", "4", "5" ];
        $recurrenceRange = [ "0", "1", "2", "3", "4" ];

        return [
            "grade" => [ "A", "B", "C", "D", "E", "F" ],
            "q_assign_view" => $quartileRange,
            "q_assign_submit" => $quartileRange,
            "q_forum_create" => $quartileRange,
            "q_forum_group_access" => $quartileRange,
            "q_forum_discussion_access" => $quartileRange,
            "q_resource_view" => $quartileRange,
            "st_indiv_assign_ltsubmit" => $mindstateRange,
            "st_group_assign_ltsubmit" => $mindstateRange,
            "st_indiv_subject_diff" => $mindstateRange,
            "rc_indiv_assign_ltsubmit" => $recurrenceRange,
            "rc_group_assign_ltsubmit" => $recurrenceRange,
            "rc_indiv_subject_keepup" => $recurrenceRange,
            "rc_indiv_subject_diff" => $recurrenceRange,
        ];

    }

    private function get_students( $courseid )
    {
        global $DB;

        $profiles = domain_profile::find_all([ "courseid" => $courseid ]);

        $users = [];

        foreach ( $profiles as $profile )
        {
            $users[$profile->userid] = $DB->get_record("user", [ "id" => $profile->userid ]);
        }

        return $users;

    }

    private function query_rule( $rule, $courseid )
    {
        global $DB;

        $antecedents = [];
        $consequents = [];

        foreach ( $rule->antecedent as $operand )
        {
            $antecedents[] = "{$operand->name}='{$operand->value}'";
        }

        foreach ( $rule->consequent as $operand )
        {
            $consequents[] = "{$operand->name}='{$operand->value}'";
        }

        $where = implode(" AND ", $antecedents) . " AND " . implode(" AND ", $consequents);

        $sql = sprintf("SELECT i.userid FROM %s as i INNER JOIN %s as q ON i.courseid = q.courseid AND i.userid = q.userid WHERE i.courseid=? AND %s", ACTIVITY_TABLE, PROFILE_TABLE, $where);

        return array_keys($DB->get_records_sql($sql, [ $courseid ]));

    }

    private function beautify_rule( $rule )
    {
        $beautifiedAntecedents = [];
        $beautifiedConsequents = [];

        foreach ( $rule->antecedent as $item )
        {
            $beautifiedAntecedents[] = $this->beautify_rule_term($item);
        }

        foreach ( $rule->consequent as $item )
        {
            $beautifiedConsequents[] = $this->beautify_rule_term($item);
        }

        return sprintf(get_string("ruleformat", "report_tecmides"), implode(" e ", $beautifiedAntecedents), implode(" e ", $beautifiedConsequents));

    }

    private function beautify_rule_term( $item )
    {
        $operand = $item->name;
        $value = $item->value;

        $beautifiedItem = get_string($operand, "report_tecmides");

        if ( strpos($operand, "q_", 0) !== false )
        {
            $beautifiedItem .= "=" . get_string("quartile" . $value, "report_tecmides");
        }
        else if ( strpos($operand, "st_", 0) !== false )
        {
            $beautifiedItem .= "=" . get_string("mindstate" . $value, "report_tecmides");
        }
        else if ( strpos($operand, "rc_", 0) !== false )
        {
            $beautifiedItem .= "=" . get_string("recurrence" . $value, "report_tecmides");
        }
        else
        {
            $beautifiedItem .= "=" . $value;
        }

        return "'" . $beautifiedItem . "'";

    }

}
