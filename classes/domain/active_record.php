<?php

namespace tecmides\domain;

interface active_record
{

    public static function get_table_name();

    public static function get_primary_key();

    public static function get_attributes();

    public static function find_one( $conditions );

    public static function find_all( $conditions );

    public function validate();

    public function populate( $record );

    public function save();

    public function delete();

}
