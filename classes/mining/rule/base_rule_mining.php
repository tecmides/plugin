<?php

namespace tecmides\mining\rule;

require_once(__DIR__ . "/module_rule_mining.php");

abstract class base_rule_mining implements module_rule_mining
{

    protected function filter( $rules )
    {
        $operators = [ "st_indiv_assign_ltsubmit", "st_group_assign_ltsubmit", "st_indiv_subject_diff" ];
        $filteredRules = [];

        foreach ( $rules as $rule )
        {
            $hasDicouragedAntecedent = false;
            
            foreach ( $rule->antecedent as $antecedent )
            {
                if ( in_array($antecedent->name, $operators) && $antecedent->value == MINDSTATE_DISCOURAGED )
                {
                    $filteredRules[] = $rule;
                    $hasDicouragedAntecedent = true;
                }
            }
            
            if( $hasDicouragedAntecedent )
            {
               continue;
            }
            
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
