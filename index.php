<?php

require(__DIR__ . '/../../config.php');
require(__DIR__ . '/lib.php');
require(__DIR__ . '/constants.php');

require(__DIR__ . "/classes/minerator/TecmidesWebserviceMinerator.class.php");
require(__DIR__ . '/classes/mining/RuleMining.class.php');
require(__DIR__ . '/classes/presentation/Chart.class.php');

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

echo $OUTPUT->header();

Activity::import($course->id);

$charts = [];
$discouragedStudentsList = [];

try
{
    $tecmidesMinerator = new TecmidesWebserviceMinerator();
}
catch ( Exception $ex )
{
    \core\notification::error(get_string("soaperror", "report_tecmides"));
}

if ( $tecmidesMinerator )
{
    $mining = new RuleMining();
    $students = $mining->getMatchingStudents($course->id, $tecmidesMinerator);

    $charts[] = generateDiscouragedStudentsChart($students);

    $discouragedStudentsList["title"] = get_string("discouragedstudentscomplete", "report_tecmides");
    $discouragedStudentsList["items"] = getDiscouragedStudentsItems($students);
    $discouragedStudentsList["emptyMessage"] = get_string("emptydiscouragedlistmessage", "report_tecmides");
}

function generateDiscouragedStudentsChart( $students )
{
    $discouragedStudents = new Chart("discouragedStudents", get_string("discouragedstudents", "report_tecmides"), get_string("discouragedstudentscomplete", "report_tecmides"));
    $discouragedStudents->setType("pie");
    $discouragedStudents->setLabels([
        get_string("discouragedstudents", "report_tecmides"),
        get_string("notdiscouragedstudents", "report_tecmides")
    ]);
    $discouragedStudents->setStyle([
        "display" => "block",
        "width" => "90%",
        "height" => "auto",
        "margin" => "0 auto",
        "maxWidth" => "800px"
    ]);

    $datasets = [];

    $datasets[] = Chart::generateDataset("# de alunos desanimados", countDiscouragedStudents($students), [ "rgba(255,87,87,1)", "rgba(87,87,255,1)" ]);

    $discouragedStudents->setDatasets($datasets);

    return $discouragedStudents;

}

function countDiscouragedStudents( $students )
{
    $data = [ 0, 0 ];

    foreach ( $students as $student )
    {
        if ( count($student->matches) > 0 )
        {
            $data[0] ++;
        }
        else
        {
            $data[1] ++;
        }
    }

    return $data;

}

function getDiscouragedStudentsItems( $students )
{
    $items = [];

    $max = 0;

    foreach ( $students as $student )
    {
        $max = count($student->matches) > $max ? count($student->matches) : $max;
    }

    if ( $max > 0 )
    {
        foreach ( $students as $student )
        {
            $coeficient = ((count($student->matches) / $max) * 100);

            $items[] = [
                "name" => $student->firstname . " " . $student->lastname,
                "rules" => $student->matches,
                "coeficient" => sprintf("%.2f %%", $coeficient),
            ];
        }

        usort($items, "cmpStudentCoeficient");
    }

    return $items;

}

function cmpStudentCoeficient( $a, $b )
{
    return $b["coeficient"] - $a["coeficient"];

}

?>

<section id="tecmides-container"></section>

<script type="text/javascript" src="asset/Chart.min.js"></script>
<script type="text/javascript" src="asset/Tecmides.js"></script>

<script type="text/javascript">
    Tecmides.run(
            document.getElementById("tecmides-container"),
            <?= json_encode($charts) ?>,
            <?= json_encode($discouragedStudentsList) ?>
    );
</script>

<?php

echo $OUTPUT->footer();
