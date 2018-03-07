<?php

namespace tecmides\minerator;

interface minerator
{

    public function generate_rules_by_attr_relativity( $data, $header, $idxClassAttr, $numRules );

    public function generate_rules( $data, $header, $idxClassAttr, $numRules );

}
