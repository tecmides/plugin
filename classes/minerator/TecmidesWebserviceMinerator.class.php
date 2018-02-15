<?php

require_once(__DIR__ . "/Minerator.php");

class TecmidesWebserviceMinerator implements Minerator
{

    private $client;

    public function __construct()
    {
        $this->client = new SoapClient("http://127.0.0.1:9876/?wsdl");

    }

    public function generateRules( $data, $header )
    {
        $arffString = $this->generateARFF($data, $header);

        return $this->convertJSON($this->client->generateRules($arffString));

    }

    private function generateARFF( $data, $header )
    {
        $strHeader = $this->convertToHeaderSection($header);
        $strData = $this->convertToDataSection($data, $header);

        return $strHeader . $strData;

    }

    private function convertToHeaderSection( $header )
    {
        $strHeader = "@RELATION tecmides\n";

        foreach ( $header as $key => $item )
        {
            $strHeader .= "@ATTRIBUTE {$key} {{$item}}\n";
        }

        return $strHeader;

    }

    private function convertToDataSection( $data, $header )
    {
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

        return $strData;

    }

    private function convertJSON( $json )
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
