<?php

namespace tecmides\output;

require_once("dashboard.php");

class itemlist extends dashboard
{

    /**
     *
     * @var array Columns of the list
     */
    private $columns = [];

    /**
     *
     * @var array Items of the list
     */
    private $items = [];

    /**
     * Adds a column name to the list
     * 
     * @param string $id Column id
     * @param string $name Column description/name
     * @param string $type Type of the column. Can be 'numeric' or 'text'
     */
    public function add_column( $id, $name, $type )
    {
        $this->columns[$id] = [
            "name" => $name,
            "type" => $type,
        ];

    }

    /**
     * Adds an row, relative to the already seted columns
     * 
     * @param array $item Associative array, in the format of 'columnid' => 'value'
     */
    public function add_item( $item )
    {
        $row = [];

        foreach ( $item as $column => $value )
        {
            $row[] = [
                "value" => $value,
                "type" => $this->columns[$column]["type"],
            ];
        }

        $this->items[] = $row;

    }

    public function get_template_name()
    {
        return "itemlist";

    }

    public function export_for_template( \renderer_base $output )
    {
        $data = parent::export_for_template($output);

        $data->columns = array_values($this->columns);
        $data->items = $this->items;

        return $data;

    }

}
