<?php

namespace tecmides\output;

abstract class dashboard implements \renderable, \templatable
{

    /**
     *
     * @var string Unique id of the dashboard
     */
    public $id;

    /**
     *
     * @var string Title of the dashboard
     */
    public $title;

    /**
     *
     * @var string Subtitle of the dashboard
     */
    public $subtitle = "";
    
    /**
     *
     * @var string Inline style of the dashboard
     */
    protected $inlineStyle = "";
    
    /**
     * Constructor for the basis dashboard
     * 
     * @param string $id
     * @param string $title
     * @param string $subtitle
     */
    public function __construct( $id, $title, $subtitle = "" )
    {
        $this->id = $id;
        $this->title = $title;
        $this->subtitle = $subtitle;

    }

    public function add_style( $property, $value )
    {
        $this->inlineStyle .= "{$property}: {$value};";

    }

    public function export_for_template( \renderer_base $output )
    {
        $data = new \stdClass();
        $data->id = $this->id;
        $data->title = $this->title;
        $data->subtitle = $this->subtitle;
        $data->inlineStyle = $this->inlineStyle;
        
        return $data;
    }
    
    public abstract function get_template_name();
}
