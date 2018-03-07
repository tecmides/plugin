<?php

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/lib.php");
require_once(__DIR__ . "/constants.php");
require_once(__DIR__ . "/classes/minerator/tecmideswebservice_minerator.php");
require_once(__DIR__ . "/classes/mining/rule/assign_rule_mining.php");
require_once(__DIR__ . "/classes/mining/rule/forum_rule_mining.php");
require_once(__DIR__ . "/classes/mining/rule/resource_rule_mining.php");
require_once(__DIR__ . '/classes/analysis_for_students.php');
require_once(__DIR__ . "/classes/domain/activity.php");
require_once(__DIR__ . "/classes/domain/profile.php");
require_once(__DIR__ . '/classes/output/chart.php');
require_once(__DIR__ . '/classes/output/itemlist.php');

use tecmides\mining\rule\assign_rule_mining;
use tecmides\mining\rule\forum_rule_mining;
use tecmides\mining\rule\resource_rule_mining;
use tecmides\domain\activity;
use tecmides\domain\profile;
use tecmides\output\chart;
use tecmides\output\itemlist;
use tecmides\minerator\tecmideswebservice_minerator;

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

activity::import($course->id);

$tecmidesMinerator = new tecmideswebservice_minerator();

$rules = array_merge(
    (new assign_rule_mining())->get_rules($tecmidesMinerator),
    (new forum_rule_mining())->get_rules($tecmidesMinerator),
    (new resource_rule_mining())->get_rules($tecmidesMinerator)
);

$students = get_matching_students($course->id, $rules);
$analysis_students = new analysis_for_students($students);

echo $output->render_dashboard(generateDiscouragedStudentsChart($analysis_students));
echo $output->render_dashboard(generateStudentsList($analysis_students));

function get_matching_students( $courseid, $rules )
{
    $students = get_students($courseid);

    foreach ( $students as $student )
    {
        $student->matches = [];
    }

    foreach ( $rules as $rule )
    {
        $matchingStudents = query_rule($rule, $courseid);

        foreach ( $matchingStudents as $userid )
        {
            $students[$userid]->matches[] = "{$rule}";
        }
    }

    return $students;

}

function get_students( $courseid )
{
    global $DB;

    $profiles = profile::find_all([ "courseid" => $courseid ]);

    $users = [];

    foreach ( $profiles as $profile )
    {
        $users[$profile->userid] = $DB->get_record("user", [ "id" => $profile->userid ]);
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

function generateDiscouragedStudentsChart( analysis_for_students $analysis_students )
{
    $data = [ $analysis_students->count_discouraged_students(), count($analysis_students->get_students()) - $analysis_students->count_discouraged_students() ];

    $chart = new chart("discouragedStudents", get_string("discouragedstudents", "report_tecmides"), get_string("discouragedstudentscomplete", "report_tecmides"));
    $chart->type = "pie";
    $chart->labels = [
        get_string("discouragedstudents", "report_tecmides"),
        get_string("notdiscouragedstudents", "report_tecmides")
    ];

    $chart->add_style("width", "60%");
    $chart->add_style("float", "left");

    $chart->add_dataset("# de alunos desanimados", $data, []);

    return $chart;

}

function generateStudentsList( analysis_for_students $analysis_students )
{
    $analysis_students->calculate_coeficient();
    $analysis_students->rank_by_coeficient();
    $rankedStudents = $analysis_students->get_students();

    $list = new itemlist("itemlist", get_string("discouragedstudentscomplete", "report_tecmides"));

    $list->add_column("name", get_string("name"), "text");
    $list->add_column("coeficient", get_string("coeficient", "report_tecmides"), "numeric");

    foreach ( $rankedStudents as $student )
    {
        $list->add_item([ "name" => $student->firstname . " " . $student->lastname, "coeficient" => sprintf("%.2f", $student->coeficient) ]);
    }

    $list->add_style("width", "40%");
    $list->add_style("float", "left");

    return $list;

}

?>

<script type="text/javascript" src="asset/chart.min.js"></script>
<script type="text/javascript" src="asset/palette.js"></script>

<?php

echo $output->footer();
