<?php

require_once(__DIR__ . "/minerator.php");

class minerator_tecmideswebservice implements minerator
{

    private $client;

    public function __construct()
    {
        $this->client = new SoapClient("http://127.0.0.1:9876/?wsdl");

    }

    public function generate_rules( $data, $header )
    {
        $arffString = $this->generate_arff($data, $header);

        return $this->parse_response($this->client->generateRules($arffString));

    }

    private function generate_arff( $data, $header )
    {
        $strHeader = "@RELATION tecmides\n";

        foreach ( $header as $key => $item )
        {
            $strHeader .= "@ATTRIBUTE {$key} {" . implode(",", $item) . "}\n";
        }

        $strData = "@DATA\n";

        foreach ( $data as $item )
        {
            $info = [];

            foreach ( array_keys($header) as $column )
            {
                $info[] = $item->$column;
            }

            // Substitui o '-' por '?'
            $strData .= str_replace("-", "?", implode(",", $info)) . "\n";
        }

        return $strHeader . $strData;

    }

    private function parse_response( $json )
    {
        $mining = json_decode($json);

        if ( ! is_array($mining) )
        {
            $prop = array_keys(get_object_vars($mining));

            foreach ( $prop as $p )
            {
                if ( is_string($mining->$p) )
                {
                    $mining->$p = $this->convert_json($mining->$p);
                }
            }
        }

        return $mining;

    }

}
