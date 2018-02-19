<?php

interface mining
{

    public function get_matching_students( $courseid, $rules );

    public function get_rules( minerator $minerator );

}
