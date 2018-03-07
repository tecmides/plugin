<?php

namespace tecmides\mining\rule;

require_once("base_rule_mining.php");
require_once(__DIR__ . "/../../minerator/minerator.php");

class forum_rule_mining extends base_rule_mining
{

    public function get_rules( \tecmides\minerator\minerator $minerator )
    {
        $header = $this->get_mining_data_header();
        $attr = array_keys($header);

        $rules = $minerator->generate_rules($this->get_mining_data(), $header, array_search("st_group_assign_ltsubmit", $attr), 20);
        
        return $this->filter($rules);

    }

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

    protected function get_mining_data_header()
    {
        $default = parent::get_mining_data_header();

        $miningAttributes = $this->get_mining_attributes();
        $header = [];

        foreach ( $miningAttributes as $attr )
        {
            $header[$attr] = $default[$attr];
        }

        return $header;

    }

}
