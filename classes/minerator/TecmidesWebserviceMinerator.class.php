<?php

require_once(__DIR__ . "/Minerator.php");
require_once(__DIR__ . "/../db/Activity.class.php");
require_once(__DIR__ . "/../db/Profile.class.php");

class TecmidesWebserviceMinerator implements Minerator
{

    private $client;

    public function __construct()
    {
        $this->client = new SoapClient("http://127.0.0.1:9876/?wsdl");

    }

    public function generateRules()
    {
        $arffString = $this->generateARFF();

        return $this->convertJSON($this->client->generateRules($arffString));

    }

    private function generateARFF()
    {
        $header = $this->getHeader();
        $data = $this->getDataSection();

        return $header . $data;

    }

    private function getHeader()
    {
        return "@RELATION tecmides

                @ATTRIBUTE grade                     {A,B,C,D,E,F}
                @ATTRIBUTE q_assign_view             {0,1,2,3}
                @ATTRIBUTE q_assign_submit           {0,1,2,3}
                @ATTRIBUTE q_forum_create            {0,1,2,3}
                @ATTRIBUTE q_forum_group_access      {0,1,2,3}
                @ATTRIBUTE q_forum_discussion_access {0,1,2,3}
                @ATTRIBUTE q_resource_view           {0,1,2,3}
                @ATTRIBUTE st_indiv_assign_ltsubmit  {0,1,2,3,4,5}
                @ATTRIBUTE st_group_assign_ltsubmit  {0,1,2,3,4,5}
                @ATTRIBUTE st_indiv_subject_diff     {0,1,2,3,4,5}
                @ATTRIBUTE rc_indiv_assign_ltsubmit  {0,1,2,3,4}
                @ATTRIBUTE rc_group_assign_ltsubmit  {0,1,2,3,4}
                @ATTRIBUTE rc_indiv_subject_keepup   {0,1,2,3,4}
                @ATTRIBUTE rc_indiv_subject_diff     {0,1,2,3,4}

                @DATA
                ";
    }

    private function getDataSection()
    {
        global $DB;

        $ignoreColumns = [ "id", "courseid", "userid", "timecreated", "timemodified" ];

        $infoColumns = array_diff(Activity::getAttributes(), $ignoreColumns);
        $questionaryColumns = array_diff(Profile::getAttributes(), $ignoreColumns);

        $columns = array_merge($infoColumns, $questionaryColumns);

        $sql = sprintf("SELECT i.userid, %s FROM %s as i INNER JOIN %s as q ON i.courseid = q.courseid AND i.userid = q.userid", implode(",", $infoColumns) . "," . implode(",", $questionaryColumns), ACTIVITY_TABLE, PROFILE_TABLE);

        $users = $DB->get_records_sql($sql);

        $data = "";

        foreach ( $users as $user )
        {
            $info = [];

            foreach ( $columns as $column )
            {
                $info[] = $user->$column;
            }

            // Substitui o '-' por '?'
            $data .= str_replace("-", "?", implode(",", $info)) . "\n";
        }

        return $data;

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
