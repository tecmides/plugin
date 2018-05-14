<?php

namespace tecmides\mining\rule;

require_once(__DIR__ . "/base_rule_mining.php");
require_once(__DIR__ . "/base_rule_mining_step.php");
require_once(__DIR__ . "/../../minerator/minerator.php");

class forum_rule_mining extends base_rule_mining
{

    public function get_rules( \tecmides\minerator\minerator $minerator, $numRules )
    {
        $rules = (new forum_rule_mining_step1())->get_rules($minerator, $numRules);
        
        return $this->filter($rules);

    }

}

class forum_rule_mining_step1 extends base_rule_mining_step
{

    protected function get_mining_attributes()
    {
        $attrs = [
            "grade",
            "q_forum_create",
            "q_forum_group_access",
            "q_forum_discussion_access",
            "st_group_assign_ltsubmit",
            "rc_group_assign_ltsubmit",
            "rc_indiv_subject_keepup",
            "rc_indiv_subject_diff",
        ];

        return $attrs;

    }

    protected function get_class_attribute()
    {
        return "st_group_assign_ltsubmit";

    }

}
