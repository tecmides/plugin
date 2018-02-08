<?php

interface ActiveRecord
{
    public static function getTableName();
    public static function getPrimaryKey();
    public static function getAttributes();
    public static function findOne($conditions);
    public static function findAll($conditions);
    
    public function validate();
    public function populate($record);
    public function save();
    public function delete();
    
}
