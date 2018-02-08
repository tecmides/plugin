<?php

require(__DIR__ . "/ActiveRecord.php");

abstract class BaseActiveRecord implements ActiveRecord
{

    private $__isNewRecord = true;

    public static function getAttributes()
    {
        $attributes = array_keys(get_class_vars(get_class(new static())));
        $ignore = array_keys(get_class_vars(__CLASS__));

        return array_diff($attributes, $ignore);

    }

    public function populate( $record )
    {
        foreach ( static::getAttributes() as $attribute )
        {
            if ( isset($record->$attribute) )
            {
                $this->$attribute = $record->$attribute;
            }
        }

    }

    public function validate()
    {
        foreach ( static::getAttributes() as $attribute )
        {
            if ( $attribute !== static::getPrimaryKey() && is_null($this->$attribute) )
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
            $data = $this->toStdClass();

            if ( ! $this->__isNewRecord )
            {
                return $DB->update_record(static::getTableName(), $data);
            }
            else
            {
                $pk = static::getPrimaryKey();
                unset($data->$pk);

                $this->$pk = $DB->insert_record(static::getTableName(), $data, true);
                $this->__isNewRecord = is_null($this->$pk);

                return ! $this->__isNewRecord;
            }
        }

        return false;

    }

    private function toStdClass()
    {
        $data = new stdClass();
        $attributes = static::getAttributes();

        foreach ( $attributes as $attribute )
        {
            $data->$attribute = $this->$attribute;
        }

        return $data;

    }

    public function delete()
    {
        global $DB;

        $pk = static::getPrimaryKey();

        return $DB->delete_records(static::getTableName(), [ $pk => $this->$pk ]);

    }

    public static function findOne( $conditions )
    {
        global $DB;

        $row = $DB->get_record(static::getTableName(), $conditions);
        
        if ( $row )
        {
            $record = new static();

            foreach ( static::getAttributes() as $attribute )
            {
                $record->$attribute = $row->$attribute;
            }

            $record->__isNewRecord = false;

            return $record;
        }

        return null;

    }

    public static function findAll( $conditions )
    {
        global $DB;

        $rows = $DB->get_records(static::getTableName(), $conditions);

        $records = [];

        foreach ( $rows as $row )
        {
            $record = new static();

            foreach ( static::getAttributes() as $attribute )
            {
                $record->$attribute = $row->$attribute;
            }

            $records[] = $record;
        }

        return $records;

    }

}
