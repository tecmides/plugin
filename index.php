<?php

require(__DIR__ . "/../../config.php");
require(__DIR__ . "/lib.php");
require(__DIR__ . "/constants.php");
require(__DIR__ . "/classes/domain_activity.php");
require(__DIR__ . "/classes/minerator_tecmideswebservice.php");
require(__DIR__ . "/classes/mining_rule.php");
require(__DIR__ . '/classes/analysis_for_students.php');
require(__DIR__ . '/classes/output/chart.php');
require(__DIR__ . '/classes/output/itemlist.php');

use \tecmides\output\chart;
use \tecmides\output\itemlist;

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

domain_activity::import($course->id);

$tecmidesMinerator = new minerator_tecmideswebservice();

$mining = new mining_rule();
$rules = $mining->get_rules($tecmidesMinerator);
$students = $mining->get_matching_students($course->id, $rules);
$analysis_students = new analysis_for_students($students);

echo $output->render_dashboard(generateDiscouragedStudentsChart($analysis_students));
echo $output->render_dashboard(generateStudentsList($analysis_students));
echo $output->render_dashboard(generateParameterImpactChart($rules));

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

function generateParameterImpactChart( $rules )
{
    $columns = mining_rule::get_mining_attributes();
    $labels = [];
    $data = [];

    foreach ( $columns as $column )
    {
        $data[] = countParameter($rules, $column);
        $labels[] = get_string($column, "report_tecmides");
    }

    $chart = new chart("parameterImpact", "Impacto dos parâmetros", "Parâmetros utilizado para geração das previsões");
    $chart->type = "horizontalBar";
    $chart->labels = $labels;
    $chart->add_dataset("# de regras em que está presente", $data, []);
    $chart->add_style("width", "70%");
    $chart->add_style("margin", "0 auto");

    return $chart;

}

function countParameter( $rules, $parameter )
{
    $count = 0;

    foreach ( $rules as $rule )
    {
        foreach ( $rule->antecedent as $antecedent )
        {
            if ( strcmp($antecedent->name, $parameter) === 0 )
            {
                $count ++;
            }
        }

        foreach ( $rule->consequent as $consequent )
        {
            if ( strcmp($consequent->name, $parameter) === 0 )
            {
                $count ++;
            }
        }
    }

    return $count;

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
