<?php

require_once(__DIR__ . "/../../config.php");

global $CFG;

define("LOG_TABLE", "{$CFG->prefix}logstore_standard_log");
define("USER_TABLE", "{$CFG->prefix}user");
define("ACTIVITY_TABLE", "{$CFG->prefix}tecmides_activity");
define("PROFILE_TABLE", "{$CFG->prefix}tecmides_profile");

define("RULE_MODEL_CONFIG", "rule_model");
define("TREE_MODEL_CONFIG", "tree_model");

define("QUARTILE_LOW", 0);
define("QUARTILE_MEDIUM", 1);
define("QUARTILE_MEDIUMHIGH", 2);
define("QUARTILE_HIGH", 3);

define("MINDSTATE_SATISFIED", 0);
define("MINDSTATE_DISSATISFIED", 1);
define("MINDSTATE_DISCOURAGED", 2);
define("MINDSTATE_ANIMATED", 3);
define("MINDSTATE_OTHER", 4);
define("MINDSTATE_NONE", 5);

define("RECURRENCE_NEVER", 0);
define("RECURRENCE_RARELY", 1);
define("RECURRENCE_SOMETIMES", 2);
define("RECURRENCE_OFTEN", 3);
define("RECURRENCE_ALWAYS", 4);