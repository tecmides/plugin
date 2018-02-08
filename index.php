<?php

require(__DIR__ . '/../../config.php');
require(__DIR__ . '/lib.php');
require(__DIR__ . '/constants.php');

require(__DIR__ . "/classes/minerator/TecmidesWebserviceMinerator.class.php");
require(__DIR__ . '/classes/mining/RuleMining.class.php');

global $DB;

$params = ['id' => required_param('id', PARAM_INT)];

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

$tecmidesMinerator = new TecmidesWebserviceMinerator();

$mining = new RuleMining();
$students = $mining->getMatchingStudents($course->id, $tecmidesMinerator);

var_dump($students);

?>

<section id="tecmides-container"></section>
<script type="text/javascript" src="asset/Tecmides.js"></script>

<?php
echo $OUTPUT->footer();