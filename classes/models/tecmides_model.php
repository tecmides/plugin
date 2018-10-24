<?php

namespace tecmides\models;

interface tecmides_model
{
    public function get_model_base_url();
    public function get_model_classify_url();
    public function get_model_contribute_url();
    public function get_model_attributes_definition();
    public function get_model_classification_attribute();
    public function get_model_attributes_names();
    public function get_model_instances();
    public function get_model_training_instances();
    
}
