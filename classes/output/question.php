<?php

namespace tecmides\output;

require_once("dashboard.php");

class question extends dashboard
{
    const ST_QUESTION = "st";
    const RC_QUESTION = "rc";

    public $type;
    public $inputName;
    public $options;

    public function __construct($id, $type, $question_header, $question, $inputName, $options = [])
    {
        parent::__construct($id, $question_header, $question);

        $this->type = $type;
        $this->inputName = $inputName;
        $this->options = $options;
    }

    public function export_for_template( \renderer_base $output )
    {
        $data = parent::export_for_template($output);

        $data->inputName = $this->inputName;
        $data->options = $this->options;

        return $data;

    }

    public function get_template_name()
    {
        if($this->type == self::ST_QUESTION)
        {
            return "st_question";
        }

        return "rc_question";
    }

}
