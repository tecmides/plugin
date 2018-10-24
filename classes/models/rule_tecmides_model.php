<?php

namespace tecmides\models;

require_once(__DIR__ . "/tecmides_model.php");

use tecmides\models\tecmides_model;

class rule_tecmides_model implements tecmides_model {

    public function get_model_base_url()
    {
        global $CFG;
        
        return $CFG->TecmidesWebserviceURL . "/" . RULE_MODEL_CONFIG;
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
        $grades = ["A", "B", "C", "D", "F"];
        $quartiles = ["low", "medium", "medium-high", "high"];
        $recurrency = ["never", "rarely", "sometimes", "often", "always"];
        $state = ["satisfied", "dissatisfied", "discouraged", "animated", "other", "none"];
        
        return [
            "grade" => $grades,
            "q_assign_view" => $quartiles,
            "q_assign_submit" => $quartiles,
            "q_forum_create" => $quartiles,
            "q_forum_group_access" => $quartiles,
            "q_forum_discussion_access" => $quartiles,
            "q_resource_view" => $quartiles,
            "st_indiv_assign_ltsubmit" => $state,
            "st_group_assign_ltsubmit" => $state,
            "st_indiv_subject_diff" => $state,
            "rc_indiv_assign_ltsubmit" => $recurrency,
            "rc_group_assign_ltsubmit" => $recurrency,
            "rc_indiv_subject_keepup" => $recurrency,
            "rc_indiv_subject_diff" => $recurrency
        ];
    }
    
    public function get_model_classification_attribute()
    {
        $discouraged = ["no", "yes"];
        
        return ["discouraged" => $discouraged];
    }

    public function get_model_attributes_names()
    {
        return array_keys($this->get_model_attributes_definition());
    }
    
    public function get_model_instances()
    {
        return $this->get_model_training_instances();
    }

    public function get_model_training_instances() {
        global $DB;

        $sql = sprintf("SELECT i.userid, i.courseid, %s FROM %s as i INNER JOIN %s as q ON i.courseid = q.courseid AND i.userid = q.userid", implode(",", $this->get_model_attributes_names()), ACTIVITY_TABLE, PROFILE_TABLE);

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
                    $instances[$i]->$field = $attributes[$field][$idx];
                }
            }
        }
        
        return $instances;
    }

}
