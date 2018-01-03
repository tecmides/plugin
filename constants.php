<?php

global $CFG;

define("LOG_TABLE", "{$CFG->prefix}logstore_standard_log");
define("INFO_TABLE", "{$CFG->prefix}report_tecmides_info");
define("QUESTIONARY_TABLE", "{$CFG->prefix}report_tecmides_questionary");

const QUARTILES = ["LOW", "MEDIUM", "MEDIUMHIGH", "HIGH"];
define("QUARTILE_LOW", 0);
define("QUARTILE_MEDIUM", 1);
define("QUARTILE_MEDIUMHIGH", 2);
define("QUARTILE_HIGH", 3);

const MINDSTATES = ["SATISFIED", "DISSATISFIED", "ANIMATED", "SAD", "OTHER", "NONE"];
define("MINDSTATE_SATISFIED", 0);
define("MINDSTATE_DISSATISFIED", 1);
define("MINDSTATE_ANIMATED", 2);
define("MINDSTATE_SAD", 3);
define("MINDSTATE_OTHER", 4);
define("MINDSTATE_NONE", 5);

const RECURRENCYS = ["NEVER", "RARELY", "SOMETIMES", "OFTEN", "ALWAYS"];
define("RECURRENCY_NEVER", 0);
define("RECURRENCY_RARELY", 1);
define("RECURRENCY_SOMETIMES", 2);
define("RECURRENCY_OFTEN", 3);
define("RECURRENCY_ALWAYS", 4);