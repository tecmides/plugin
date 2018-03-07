<?php

namespace tecmides\mining\rule;

require_once("base_rule_mining.php");
require_once(__DIR__ . "/../../minerator/minerator.php");

class assign_rule_mining extends base_rule_mining
{

    public function get_rules( \tecmides\minerator\minerator $minerator )
    {
        $header = $this->get_mining_data_header();
        $attr = array_keys($header);
                
        $rules = array_merge(
            $minerator->generate_rules_by_attr_relativity($this->get_mining_data(), $header, array_search("st_indiv_assign_ltsubmit", $attr), 10),
            $minerator->generate_rules_by_attr_relativity($this->get_mining_data(), $header, array_search("st_group_assign_ltsubmit", $attr), 10),
            $minerator->generate_rules_by_attr_relativity($this->get_mining_data(), $header, array_search("st_indiv_subject_diff", $attr), 10)
        );

        return $this->filter($rules);

    }

}
