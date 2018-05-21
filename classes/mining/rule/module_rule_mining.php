<?php

namespace tecmides\mining\rule;

require_once(__DIR__ . "/../../minerator/minerator.php");

interface module_rule_mining
{
    public function get_rules(\tecmides\minerator\minerator $minerator, $numRules);

}
