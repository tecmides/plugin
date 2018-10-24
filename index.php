<?php

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/lib.php");
require_once(__DIR__ . "/constants.php");
require_once(__DIR__ . "/classes/domain/activity.php");
require_once(__DIR__ . "/classes/domain/profile.php");
require_once(__DIR__ . '/classes/output/chart.php');
require_once(__DIR__ . '/classes/output/itemlist.php');

require_once(__DIR__ . "/classes/servers/tecmides_server.php");
require_once(__DIR__ . "/classes/models/rule_tecmides_model.php");
require_once(__DIR__ . "/classes/models/tree_tecmides_model.php");
require_once(__DIR__ . '/classes/models/analysis_rule_tecmides_model.php');
require_once(__DIR__ . '/classes/models/analysis_tree_tecmides_model.php');

use tecmides\domain\activity;
use tecmides\domain\profile;
use tecmides\output\chart;
use tecmides\output\itemlist;

use tecmides\servers\tecmides_server;
use tecmides\models\tree_tecmides_model;
use tecmides\models\rule_tecmides_model;
use tecmides\models\analysis_rule_tecmides_model;
use tecmides\models\analysis_tree_tecmides_model;

global $CFG;
global $DB;

$params = [ 'id' => required_param('id', PARAM_INT) ];

// General data aquiring
$course = $DB->get_record('course', $params, '*', MUST_EXIST);

require_login($course);
require_course_login($course);

$context = context_course::instance($course->id);

// Page setup
$url = new moodle_url("/report/tecmides/index.php", $params);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('pluginname', 'report_tecmides'));
$PAGE->set_title(get_string('pluginname', 'report_tecmides'));
$PAGE->set_url($url->out());
$PAGE->set_pagelayout('report');

$output = $PAGE->get_renderer('report_tecmides');

echo $output->header();

$hasPermissions = has_capability ('moodle/course:update', $context);

if($hasPermissions) {
    $server = new tecmides_server();
    
    if($server->test())
    {
        activity::import($course->id);
        
        $rule_analysis_students = new analysis_rule_tecmides_model(get_students($course->id), get_rule_model_classification_data($server));
        $tree_analysis_students = new analysis_tree_tecmides_model(get_students($course->id), get_tree_model_classification_data($server));

        echo "<div class='main-container'>";

        $discouraged_students_chart = generate_dicouraged_students_chart($rule_analysis_students);
        $discouraged_students_list = generate_dicouraged_students_list($rule_analysis_students);
        $approved_students_chart = generate_approved_students_chart($tree_analysis_students);

        $discouraged_students_chart->add_style("width", "60%");
        $discouraged_students_chart->add_style("margin-right", "1%");
        $discouraged_students_chart->add_style("margin-bottom", "1%");
        $discouraged_students_list->add_style("width", "39%");
        $approved_students_chart->add_style("width", "100%");

        render_dashboards($output, [$discouraged_students_chart, $discouraged_students_list, $approved_students_chart]);

        echo "</div>";
    }
    else {
        \core\notification::error(get_string("message_servernotfound", "report_tecmides"));
    }
}
else {
    \core\notification::error(get_string("message_needtobeteacher", "report_tecmides"));
}

function get_students( $courseid )
{
    global $DB;

    $profiles = profile::find_all([ "courseid" => $courseid ]);

    $users = [];
    
    foreach ( $profiles as $profile )
    {
        $users[$profile->userid] = $DB->get_record_sql('SELECT ? as courseid, id as userid, firstname, lastname FROM {user} WHERE id = ?', array($courseid, $profile->userid));
    }

    return $users;

}

function query_rule( $rule, $courseid )
{
    global $DB;

    $antecedents = [];
    $consequents = [];

    foreach ( $rule->antecedent as $operand )
    {
        $antecedents[] = "{$operand->name}='{$operand->value}'";
    }

    foreach ( $rule->consequent as $operand )
    {
        $consequents[] = "{$operand->name}='{$operand->value}'";
    }

    $where = implode(" AND ", $antecedents) . " AND " . implode(" AND ", $consequents);

    $sql = sprintf("SELECT i.userid FROM %s as i INNER JOIN %s as q ON i.courseid = q.courseid AND i.userid = q.userid WHERE i.courseid=? AND %s", ACTIVITY_TABLE, PROFILE_TABLE, $where);

    return array_keys($DB->get_records_sql($sql, [ $courseid ]));

}

function generate_dicouraged_students_chart( $analysis_students )
{
    $data = [ $analysis_students->count_discouraged_students(), $analysis_students->count_not_discouraged_students() ];

    $chart = new chart("discouragedStudents", get_string("discouragedstudents", "report_tecmides"), get_string("discouragedstudentscomplete", "report_tecmides"));
    $chart->type = "pie";
    $chart->labels = [
        get_string("discouragedstudents", "report_tecmides"),
        get_string("notdiscouragedstudents", "report_tecmides")
    ];
    
    $chart->add_dataset("# de alunos desanimados", $data, ["#f44336", "#a5d6a7"]);

    return $chart;

}

function generate_dicouraged_students_list( $analysis_students )
{
    $rankedStudents = $analysis_students->get_students_ranked_by_coeficient();

    $list = new itemlist("itemlist", get_string("discouragedstudentscomplete", "report_tecmides"));

    $list->add_column("name", get_string("name"), "text");
    $list->add_column("coeficient", get_string("coeficient", "report_tecmides"), "numeric");

    foreach ( $rankedStudents as $student )
    {
        $list->add_item([ "name" => $student->firstname . " " . $student->lastname, "coeficient" => sprintf("%.2f", $student->coeficient) ]);
    }

    return $list;

}

function generate_approved_students_chart( $analysis_students )
{
    $data = [ $analysis_students->count_not_approved_students(), $analysis_students->count_approved_students() ];

    $chart = new chart("approvedStudents", get_string("approvedstudentscharttitle", "report_tecmides"), get_string("approvedstudentschartdescription", "report_tecmides"));
    $chart->type = "pie";
    $chart->labels = [
        get_string("notapprovedstudents", "report_tecmides"),
        get_string("approvedstudents", "report_tecmides")
    ];

    $chart->add_dataset("# de alunos não aprovados", $data, ["#f44336", "#a5d6a7"]);

    return $chart;

}

function get_rule_model_classification_data($server)
{
    $model = new rule_tecmides_model();
    $data = $server->classify($model, $model->get_model_instances());
    
    return $data;
}

function get_tree_model_classification_data($server)
{
    $model = new tree_tecmides_model();
    $data = $server->classify($model, $model->get_model_instances());
    
    return $data;
}

function render_dashboards($output, $dashboards)
{
    foreach($dashboards as $dashboard)
    {
        echo $output->render_dashboard($dashboard);
    }
}

?>

<script type="text/javascript" src="asset/chart.min.js"></script>

<?php

echo $output->footer();
