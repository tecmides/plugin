<?php

namespace tecmides\models;

require_once(__DIR__ . "/tecmides_model.php");

use tecmides\models\tecmides_model;

class tree_tecmides_model implements tecmides_model {

    public function get_model_base_url()
    {
        global $CFG;
        
        return $CFG->TecmidesWebserviceURL . "/" . TREE_MODEL_CONFIG;
    }

    public function get_model_contribute_url()
    {
        return $this->get_model_base_url() . "/contribute";
    }
    
    public function get_model_classify_url()
    {
        return $this->get_model_base_url() . "/classify";
    }
    
    public function get_model_attributes_definition()
    {
        $quartiles = ["low", "medium", "medium-high", "high"];
        
        return [
            "q_assign_view" => $quartiles,
            "q_assign_submit" => $quartiles,
            "q_forum_create" => $quartiles,
            "q_forum_group_access" => $quartiles,
            "q_forum_discussion_access" => $quartiles,
            "q_resource_view" => $quartiles
        ];
    }
    
    public function get_model_classification_attribute() {
        $approved = ["no", "yes"];
        
        return ["approved" => $approved];
    }

    public function get_model_attributes_names()
    {
        return array_keys($this->get_model_attributes_definition());
    }
    
    public function get_model_instances()
    {
        $instances = $this->get_model_training_instances();
        
        for($i = 0; $i < count($instances); $i++)
        {
            unset($instances[$i]->approved);
        }
        
        return $instances;
    }

    public function get_model_training_instances() {
        global $DB;

        $sql = sprintf("SELECT i.userid, i.courseid, %s, grade FROM %s as i", implode(",", $this->get_model_attributes_names()), ACTIVITY_TABLE);

        $data = $DB->get_records_sql($sql);

        $instances = array_values($data);
        $attributes = $this->get_model_attributes_definition();
        
        for($i = 0; $i < count($instances); $i++)
        {
            $fields = get_object_vars($instances[$i]);
            
            foreach($fields as $field => $idx)
            {
                if(!in_array($field, ["courseid", "userid", "grade"]))
                {
                    $instances[$i]->$field = $attributes[$field][intval($idx)];
                }
            }
            
            if(in_array($instances[$i]->grade, ["A", "B", "C"]))
            {
                $instances[$i]->approved = "yes";
            }
            else
            {
                $instances[$i]->approved = "no";
            }
            
            unset($instances[$i]->grade);
        }        
        
        return $instances;
    }

}
