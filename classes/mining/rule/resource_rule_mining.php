<?php

namespace tecmides\mining\rule;

require_once(__DIR__ . "/base_rule_mining.php");
require_once(__DIR__ . "/base_rule_mining_step.php");
require_once(__DIR__ . "/../../minerator/minerator.php");

class resource_rule_mining extends base_rule_mining
{

    public function get_rules( \tecmides\minerator\minerator $minerator, $numRules, $minSupport = 0.2, $minConfidence = 0.7 )
    {
        $rules = $rules = array_merge(
            (new resource_rule_mining_step1())->get_rules($minerator, $numRules, $minSupport, $minConfidence),
            (new resource_rule_mining_step2())->get_rules($minerator, $numRules, $minSupport, $minConfidence)
        );

        return $this->filter($rules);

    }

}

class resource_rule_mining_step1 extends base_rule_mining_step
{

    protected function get_mining_attributes()
    {
        $attrs = [
            "st_indiv_assign_ltsubmit",
            "st_indiv_subject_diff",
            "rc_indiv_assign_ltsubmit",
            "rc_indiv_subject_diff",
            "rc_indiv_subject_keepup",
            "grade",
            "q_resource_view"
        ];

        return $attrs;

    }

    protected function get_class_attribute()
    {
        return "";

    }

}

class resource_rule_mining_step2 extends base_rule_mining_step
{

    protected function get_mining_attributes()
    {
        $attrs = [
            "st_indiv_assign_ltsubmit",
            "st_indiv_subject_diff",
            "grade",
            "q_resource_view"
        ];

        return $attrs;

    }

    protected function get_class_attribute()
    {
        return "";

    }

}