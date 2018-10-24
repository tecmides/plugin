<?php

namespace tecmides\servers;

require_once(__DIR__ . "/../models/tecmides_model.php");
require_once(__DIR__ . "/../models/tree_tecmides_model.php");

use tecmides\models\tecmides_model;
use tecmides\models\tree_tecmides_model;

class tecmides_server {
    
    private function send_json($url, $json) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

        $jsonresponse = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        return [
            "status" => $status,
            "data" => json_decode($jsonresponse)
        ];
    }
    
    public function contribute(tecmides_model $model) {
        $url = $model->get_model_contribute_url();
        $json = json_encode($model->get_model_training_instances());
        $response = $this->send_json($url, $json);
        $status = $response["status"];
        $data = $response["data"];
        
        return $status == "200";
    }
    
    public function classify(tecmides_model $model, $data) {
        $url = $model->get_model_classify_url();
        $json = json_encode($data);
        $response = $this->send_json($url, $json);
        $status = $response["status"];
        $classified = $response["data"];
        
        if( $status == "200" )
        {
            return $classified;
        }
        
        return false;
    }
    
    public function test() {
        $model = new tree_tecmides_model();
        $instances = $model->get_model_instances();
        return false !== $this->classify($model, [$instances[0]]);
    }
}