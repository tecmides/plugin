<?php

require(__DIR__ . '/../../config.php');
require(__DIR__ . '/lib.php');
require(__DIR__ . '/constants.php');

require(__DIR__ . '/../../grade/querylib.php');
require(__DIR__ . '/../../lib/gradelib.php');

require(__DIR__ . '/form/update_form.php');

$params = ['id' => required_param('id', PARAM_INT)];

// General data aquiring
$course = $DB->get_record('course', $params, '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($course->id);

// Page setup
$url = new moodle_url("/report/tecmides/index.php", $params);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('pluginname', 'report_tecmides'));
$PAGE->set_title(get_string('pluginname', 'report_tecmides'));
$PAGE->set_url($url->out());
$PAGE->set_pagelayout('report');

echo $OUTPUT->header();

$mform = new update_form($url->out());

$formData = $mform->get_data();

if ($formData) {
    $content = explode("\n", $mform->get_file_content('questionary_info'));
    
    // REALIZAR O PROCESSAMENTO DO ARQUIVO
    // - Encontrar courseid
    // - Encontrar userid
    // - Inserir respostas
} else {
    $mform->display();
}

update_mining_data(intval($course->id));

$arffString = generate_arff();

$tecmidesWsdl = "http://127.0.0.1:9876/?wsdl";
$tecmidesClient = new SoapClient($tecmidesWsdl);

$rules = convert_json($tecmidesClient->generateRules($arffString));

var_dump($rules);

echo $OUTPUT->footer();

function convert_json($json) {
    $mining = json_decode($json);
    
    if(!is_array($mining)) {
        $prop = array_keys(get_object_vars($mining));
    
        foreach($prop as $p) {
            $mining->$p = convert_json($mining->$p);
        }
    }
    else
    {
        for($i = 0; $i < count($mining); $i++) {
            $mining[$i] = trim($mining[$i]);
        }
    }
    
    return $mining;
}

function generate_arff() {
    global $DB;

    $infoColumns = ["grade", "q_assign_view", "q_assign_submit", "q_forum_create", "q_forum_group_access", "q_forum_discussion_access", "q_resource_view"];
    $questionaryColumns = ["st_indiv_assign_ltsubmit", "st_group_assign_ltsubmit", "st_indiv_subject_diff", "rc_indiv_assign_ltsubmit", "rc_group_assign_ltsubmit", "rc_indiv_subject_keepup", "rc_indiv_subject_diff"];

    $columns = implode(",", $infoColumns) . "," . implode(",", $questionaryColumns);
    
    $miningInformation = $DB->get_records_sql("SELECT i.userid, {$columns} FROM " . INFO_TABLE . " i INNER JOIN " . QUESTIONARY_TABLE . " q ON i.courseid = q.courseid AND i.userid = q.userid");

    $data = "";
    
    foreach ($miningInformation as $user) {
        foreach($infoColumns as $info) {
            // Normaliza para que itens desconhecidos sejam identificados com '?'
            $data .= ($user->$info == "-" ? "?" : $user->$info) . ",";
        }
        
        foreach($questionaryColumns as $info) {
            $data .= "{$user->$info},";
        }
        
        $data = substr($data, 0, strlen($data) - 1);
        
        $data .= "\n";
    }
    
    $arff = file_get_contents(__DIR__ . "/template/template.arff");
        
    return $arff . $data;
}

function update_mining_data($courseid) {
    global $DB;

    $users = get_mining_information($courseid);

    foreach ($users as $userid => $user) {
        $infos = prepare_information($courseid, $userid, $user);

        $query = "";

        if ($DB->record_exists("report_tecmides_info", ["courseid" => $courseid, "userid" => $userid])) {
            $query = get_update_query(INFO_TABLE, $infos);
        } else {
            $query = get_insert_query(INFO_TABLE, $infos);
        }

        $DB->execute($query);
    }
}

function get_insert_query($table, $infos) {
    $query = "INSERT INTO {$table} (" . implode(", ", array_keys($infos)) . ") VALUES (" . implode(", ", array_values($infos)) . ")";

    return $query;
}

function get_update_query($table, $infos) {
    $query = "UPDATE {$table} SET ";

    $updates = [];

    foreach ($infos as $key => $info) {
        array_push($updates, "{$key} = {$info}");
    }

    return $query . implode(", ", $updates) . " WHERE courseid = {$infos["courseid"]} AND userid = {$infos["userid"]}";
}

function prepare_information($courseid, $userid, $user) {
    $columns = [
        "grade",
        "q_assign_view", "q_assign_submit", "q_forum_create",
        "q_forum_group_access", "q_forum_discussion_access", "q_resource_view"
    ];

    $insert = ["courseid" => "'{$courseid}'", "userid" => "'{$userid}'"];

    foreach ($columns as $column) {
        $insert = array_merge($insert, ["{$column}" => "'{$user[$column]}'"]);
    }

    $momento = time();

    return array_merge($insert, ["moment" => "'{$momento}'"]);
}

/**
 * Get thw whole mining information, ready to be saved in database
 * 
 * @global type $DB
 * @param int $courseid The course id
 * @return array Associative array, containing information by course and user
 */
function get_mining_information($courseid) {
    global $DB;

    $table = LOG_TABLE;

    // To obtain unique values when using this queries using Moodle's DB API, it's necessary to group userid and courseid
    $queries = [
        "assign_view" => "SELECT userid, COUNT(userid) as cnt FROM {$table} WHERE component = 'mod_assign' AND action = 'viewed' AND courseid='{$courseid}' GROUP BY userid",
        "assign_submit" => "SELECT userid, COUNT(userid) as cnt FROM {$table} WHERE component = 'mod_assign' AND action = 'submitted' AND courseid='{$courseid}' GROUP BY userid",
        "forum_create" => "SELECT userid, COUNT(userid) as cnt FROM {$table} WHERE component = 'mod_forum' AND action = 'created' AND courseid='{$courseid}' GROUP BY userid",
        "forum_group_access" => "SELECT userid, COUNT(userid) as cnt FROM {$table} WHERE component = 'mod_forum' AND action = 'viewed' AND target='course_module' AND courseid='{$courseid}' GROUP BY userid",
        "forum_discussion_access" => "SELECT userid, COUNT(userid) as cnt FROM {$table} WHERE component = 'mod_forum' AND action = 'viewed' AND target='discussion' AND courseid='{$courseid}' GROUP BY userid",
        "resource_view" => "SELECT userid, COUNT(userid) as cnt FROM {$table} WHERE component = 'mod_resource' AND action = 'viewed' AND courseid='{$courseid}' GROUP BY userid"
    ];

    $infos = array_keys($queries);

    $rawData = [];

    foreach ($queries as $info => $query) {
        $record = $DB->get_records_sql($query);

        foreach ($record as $row) {
            $rawData[$row->userid][$info] = intval($row->cnt);
        }
    }

    return populate_grades(generate_quartiles($rawData, $infos), $courseid);
}

/**
 * Given a set of informations containing in the array, generates the quartiles
 * 
 * @param array $users Data with giver information to be associaced with a quartile
 * @param array $infos Information containing in $data
 * @return array $data array populated with quartiles, having a 'q_' prefix
 */
function generate_quartiles($users, $infos) {
    $qdata = process_quartiles(normalize_log_data($users, $infos, 0), $infos);

    ksort($qdata);

    return $qdata;
}

/**
 * Get the maximum values of user's provided informations
 * 
 * @param array $users Array of users and respective $infos
 * @param array $infos Array with users infos
 * @return array Array with max values for $infos in $users
 */
function get_max_values($users, $infos) {
    $max = array();

    foreach ($infos as $info) {
        $max[$info] = 0;
    }

    foreach ($users as $user) {
        foreach ($infos as $info) {
            if (isset($user[$info]) && $user[$info] > $max[$info]) {
                $max[$info] = $user[$info];
            }
        }
    }

    return $max;
}

/**
 * For not setted informations of users, give a default value
 * 
 * @param array $users Array of users and respective $infos
 * @param array $infos Array with users infos
 * @param int $defaultValue Default value for empty properties
 * @return array Array with normalized user information
 */
function normalize_log_data($users, $infos, $defaultValue = 0) {
    foreach ($users as $userid => $user) {
        foreach ($infos as $info) {
            if (!isset($user[$info])) {
                $user[$info] = $defaultValue;
            }
        }

        $users[$userid] = $user;
    }

    return $users;
}

/**
 * Process the quartiles informations
 * 
 * @param array $users Array of users and respective $infos
 * @param array $infos Array with users infos
 * @return array Array containing the quartiles information, with the information and a 'q_' prefix
 */
function process_quartiles($users, $infos) {
    $maxes = get_max_values($users, $infos);

    foreach ($users as $userid => $user) {
        foreach ($infos as $info) {
            $user["q_{$info}"] = get_quartile($user[$info], $maxes[$info]);
            $users[$userid] = $user;
        }
    }

    return $users;
}

/**
 * Get the reference quartile from a giver falue according to a reference
 * 
 * @param int $value Value to be classified
 * @param int $reference Reference value
 * @return int Quartile level, from QUARTILE_LOW to QUARTILE_HIGH
 */
function get_quartile($value, $reference) {
    $q1 = round(($reference) * 0.25);
    $q2 = round(($reference) * 0.5);
    $q3 = round(($reference) * 0.75);

    if ($value <= $q1) {
        return QUARTILE_LOW;
    } else if ($value > $q1 && $value <= $q2) {
        return QUARTILE_MEDIUM;
    } else if ($value > $q2 && $value <= $q3) {
        return QUARTILE_MEDIUMHIGH;
    } else if ($value > $q3) {
        return QUARTILE_HIGH;
    }
}

/**
 * Populate the array with grades accordgin to combination courseid and userid
 * 
 * @param array $data Data with no grade info for courseid.userid
 * @return array $data Data with grade info for courseid.userid
 */
function populate_grades($data, $courseid) {
    $idUsers = array_keys($data);

    $courseGrades = grade_get_course_grades($courseid, $idUsers);

    foreach ($idUsers as $userid) {
        $grade = "-";

        if (isset($courseGrades->grades[$userid])) {
            $grade = preg_replace('/[\s0-9,\(\)]/', '', $courseGrades->grades[$userid]->str_grade);
        }
                
        $data[$userid]["grade"] = $grade[0];
    }

    return $data;
}
