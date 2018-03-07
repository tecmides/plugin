<?php

$quartileRange = [ "0", "1", "2", "3" ];
$mindstateRange = [ "0", "1", "2", "3", "4", "5" ];
$recurrenceRange = [ "0", "1", "2", "3", "4" ];

return [
    "grade" => [ "A", "B", "C", "D", "E", "F" ],
    "q_assign_view" => $quartileRange,
    "q_assign_submit" => $quartileRange,
    "q_forum_create" => $quartileRange,
    "q_forum_group_access" => $quartileRange,
    "q_forum_discussion_access" => $quartileRange,
    "q_resource_view" => $quartileRange,
    "st_indiv_assign_ltsubmit" => $mindstateRange,
    "st_group_assign_ltsubmit" => $mindstateRange,
    "st_indiv_subject_diff" => $mindstateRange,
    "rc_indiv_assign_ltsubmit" => $recurrenceRange,
    "rc_group_assign_ltsubmit" => $recurrenceRange,
    "rc_indiv_subject_keepup" => $recurrenceRange,
    "rc_indiv_subject_diff" => $recurrenceRange,
];
