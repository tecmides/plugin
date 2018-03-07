<?php

namespace tecmides\mining\rule;

require_once("module_rule_mining.php");

abstract class base_rule_mining implements module_rule_mining
{

    protected function get_mining_data()
    {
        global $DB;

        $sql = sprintf("SELECT i.userid, %s FROM %s as i INNER JOIN %s as q ON i.courseid = q.courseid AND i.userid = q.userid", implode(",", $this->get_mining_attributes()), ACTIVITY_TABLE, PROFILE_TABLE);

        $data = $DB->get_records_sql($sql);

        return array_values($data);

    }

    protected function get_mining_data_header()
    {
        return include(__DIR__ . "/../mining_attributes.php");

    }

    protected function get_mining_attributes()
    {
        return array_keys($this->get_mining_data_header());

    }

    protected function filter( $rules )
    {
        $operators = [ "st_indiv_assign_ltsubmit", "st_group_assign_ltsubmit", "st_indiv_subject_diff" ];
        $filteredRules = [];

        foreach ( $rules as $rule )
        {
            foreach ( $rule->consequent as $consequent )
            {
                if ( in_array($consequent->name, $operators) && $consequent->value == MINDSTATE_DISCOURAGED )
                {
                    $filteredRules[] = $rule;
                }
            }
        }

        return $filteredRules;

    }

}
