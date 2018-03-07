<?php

namespace tecmides\mining\rule\domain;

class rule
{

    public $antecedent;
    public $consequent;
    public $confidence;
    public $lift;
    public $conviction;

    public function __construct()
    {
        $this->antecedent = [];
        $this->consequent = [];

    }
    
    public function __toString()
    {
        $beautifiedAntecedents = [];
        $beautifiedConsequents = [];

        foreach ( $this->antecedent as $item )
        {
            $beautifiedAntecedents[] = $this->beautify_rule_term($item);
        }

        foreach ( $this->consequent as $item )
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
