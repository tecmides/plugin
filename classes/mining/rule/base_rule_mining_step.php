<?php

namespace tecmides\mining\rule;

require_once(__DIR__ . "/base_rule_mining.php");

abstract class base_rule_mining_step extends base_rule_mining
{
    public function get_rules( \tecmides\minerator\minerator $minerator, $numRules, $minSupport, $minConfidence )
    {
        $header = $this->get_mining_data_header();

        $rules = $minerator->generate_rules($this->get_mining_data(), $header, $numRules, $minSupport, $minConfidence);

        return $this->filter($rules);

    }

    public function get_rules_by_attr_relativity( \tecmides\minerator\minerator $minerator, $numRules, $minSupport, $minConfidence )
    {
        $header = $this->get_mining_data_header();
        $attr = array_keys($header);

        $rules = $minerator->generate_rules_by_attr_relativity($this->get_mining_data(), $header, array_search($this->get_class_attribute(), $attr), $numRules, $minSupport, $minConfidence);

        return $this->filter($rules);

    }

    protected function get_mining_data()
    {
        global $DB;

        $sql = sprintf("SELECT i.userid, %s FROM %s as i INNER JOIN %s as q ON i.courseid = q.courseid AND i.userid = q.userid", implode(",", $this->get_mining_attributes()), ACTIVITY_TABLE, PROFILE_TABLE);

        $data = $DB->get_records_sql($sql);

        return array_values($data);

    }

    protected function get_default_mining_data_header()
    {
        return include(__DIR__ . "/../mining_attributes.php");

    }

    protected function get_mining_attributes()
    {
        return array_keys($this->get_default_mining_data_header());

    }

    protected function get_mining_data_header()
    {
        $default = $this->get_default_mining_data_header();

        $miningAttributes = $this->get_mining_attributes();
        $header = [];

        foreach ( $miningAttributes as $attr )
        {
            $header[$attr] = $default[$attr];
        }

        return $header;

    }

    protected abstract function get_class_attribute();

}
