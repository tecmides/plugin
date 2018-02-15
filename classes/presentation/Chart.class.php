<?php

class Chart implements JsonSerializable
{
    private $id;
    private $title;
    private $subtitle;
    private $type;
    private $datasets;
    private $labels;
    private $style;
    
    public function __construct($id, $title, $subtitle)
    {
        $this->id = $id;
        $this->title = $title;
        $this->subtitle = $subtitle;

    }
    
    public function setType($value) {
        $this->type = $value;
    }
    
    public function setDatasets($value) {
        $this->datasets = $value;
    }
    
    public function setStyle($value) {
        $this->style = $value;
    }
    
    public function setLabels($value) {
        $this->labels = $value;
    }
    
    public static function generateDataset($label, $data, $colors) {
        return [
            "label" => $label,
            "data" => $data,
            "backgroundColor" => $colors,
        ];
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }

}