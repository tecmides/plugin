<?php

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/lib.php");
require_once(__DIR__ . "/constants.php");
require_once(__DIR__ . '/classes/domain/profile.php');
require_once(__DIR__ . '/classes/output/question.php');

use tecmides\domain\profile;
use tecmides\output\question;

global $DB;

$params = [ 'id' => required_param('id', PARAM_INT) ];

// General data aquiring
$course = $DB->get_record('course', $params, '*', MUST_EXIST);

require_login($course);
require_course_login($course);

$context = context_course::instance($course->id);

// Page setup
$url = new moodle_url("/report/tecmides/questionary.php", $params);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('questionaryname', 'report_tecmides'));
$PAGE->set_title(get_string('questionaryname', 'report_tecmides'));
$PAGE->set_url($url->out());
$PAGE->set_pagelayout('report');

$output = $PAGE->get_renderer('report_tecmides');

$currentUser = $USER;

echo $output->header();

$isStudent = !has_capability ('moodle/course:update', $context);

if($isStudent) {
    if (isset($_POST["questionary"])) {
        $answers = $_POST["questionary"];

        $profile = profile::find_one(["userid" => $currentUser->id]);

        $profile = is_null($profile) ? new profile() : $profile;

        $profile->courseid = $course->id;
        $profile->userid = $currentUser->id;

        foreach ($answers as $id => $answer) {
            $profile->$id = $answer;
        }

        if ($profile->save()) {
            \core\notification::success(get_string("message_questionarysaved", "report_tecmides"));
        } else {
            \core\notification::error(get_string("message_questionaryerror", "report_tecmides"));
        }
    }

    $labels = profile::get_labels();
    $attrs = array_values(array_intersect(profile::get_attributes(), array_keys($labels)));
    $rc_options = [];

    for ($i = 0; $i < 5; $i++) {
        $rc_options[] = [
            "value" => $i,
            "label" => get_string("recurrence{$i}", "report_tecmides"),
        ];
    }

    echo "<form name=\"questionary\" method=\"post\">";
    foreach ($attrs as $i => $attr) {
        $id = $i + 1;

        echo $output->render_dashboard(
            new question(
                "q{$id}",
                strpos($attr, question::ST_QUESTION) === 0 ? question::ST_QUESTION : question::RC_QUESTION,
                get_string("questionheader", "report_tecmides") . " {$id}",
                $labels[$attr],
                "questionary[{$attr}]",
                $rc_options
            )
        );
    }

    echo "<div><input id=\"accept\" name=\"accept\" type=\"checkbox\" required/>&nbsp;<label for=\"accept\">" . get_string("acceptterms", "report_tecmides") . "</label></div>";

    echo "<button type=\"submit\" class=\"btn btn-primary center\">" . get_string("submit") . "</button>";

    echo "</form>";

}
else {
    \core\notification::error(get_string("message_needtobestudent", "report_tecmides"));
}

?>

<script type="text/javascript" src="asset/gew.js"></script>
<script type="text/javascript">
    window.addEventListener("load", function () {
        Gew.init(true);
    }, false);

</script>

<?php

echo $OUTPUT->footer();
