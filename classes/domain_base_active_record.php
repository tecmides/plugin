<?php

require_once(__DIR__ . "/domain_active_record.php");

abstract class domain_base_active_record implements domain_active_record
{

    private $__is_new_record = true;

    public static function get_attributes()
    {
        $attributes = array_keys(get_class_vars(get_class(new static())));
        $ignore = array_keys(get_class_vars(__CLASS__));

        return array_diff($attributes, $ignore);

    }

    public function populate( $record )
    {
        foreach ( static::get_attributes() as $attribute )
        {
            if ( isset($record->$attribute) )
            {
                $this->$attribute = $record->$attribute;
            }
        }

    }

    public function validate()
    {
        foreach ( static::get_attributes() as $attribute )
        {
            if ( $attribute !== static::get_primary_key() && is_null($this->$attribute) )
            {
                return false;
            }
        }

        return true;

    }

    public function save()
    {
        global $DB;

        if ( $this->validate() )
        {
            $data = $this->to_stdClass();

            if ( ! $this->__is_new_record )
            {
                return $DB->update_record(static::get_table_name(), $data);
            }
            else
            {
                $pk = static::get_primary_key();
                unset($data->$pk);

                $this->$pk = $DB->insert_record(static::get_table_name(), $data, true);
                $this->__is_new_record = is_null($this->$pk);

                return ! $this->__is_new_record;
            }
        }

        return false;

    }

    private function to_stdClass()
    {
        $data = new stdClass();
        $attributes = static::get_attributes();

        foreach ( $attributes as $attribute )
        {
            $data->$attribute = $this->$attribute;
        }

        return $data;

    }

    public function delete()
    {
        global $DB;

        $pk = static::get_primary_key();

        return $DB->delete_records(static::get_table_name(), [ $pk => $this->$pk ]);

    }

    public static function find_one( $conditions )
    {
        global $DB;

        $row = $DB->get_record(static::get_table_name(), $conditions);

        if ( $row )
        {
            $record = new static();

            foreach ( static::get_attributes() as $attribute )
            {
                $record->$attribute = $row->$attribute;
            }

            $record->__is_new_record = false;

            return $record;
        }

        return null;

    }

    public static function find_all( $conditions )
    {
        global $DB;

        $rows = $DB->get_records(static::get_table_name(), $conditions);

        $records = [];

        foreach ( $rows as $row )
        {
            $record = new static();

            foreach ( static::get_attributes() as $attribute )
            {
                $record->$attribute = $row->$attribute;
            }

            $records[] = $record;
        }

        return $records;

    }

}
