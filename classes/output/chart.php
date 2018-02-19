<?php

namespace tecmides\output;

require_once("dashboard.php");

class chart extends dashboard
{

    public $type;
    public $labels;
    private $datasets = [];
    
    public function add_dataset( $label, $data, $colors )
    {
        $this->datasets[] = [
            "label" => $label,
            "data" => $data,
            "backgroundColor" => $colors,
        ];

    }

    public function get_template_name()
    {
        return "chart";

    }

    public function export_for_template( \renderer_base $output )
    {
        $data = parent::export_for_template($output);

        // This properties will be used in JS of the template
        $data->type = json_encode($this->type);
        $data->labels = json_encode($this->labels);
        $data->datasets = json_encode($this->datasets);

        return $data;

    }

}
