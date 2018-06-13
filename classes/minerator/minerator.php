<?php

namespace tecmides\minerator;

interface minerator
{

    public function generate_rules_by_attr_relativity( $data, $header, $idxClassAttr, $numRules, $minSupport, $minConfidence );

    public function generate_rules( $data, $header, $numRules, $minSupport, $minConfidence );

}
