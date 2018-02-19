<?php

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @global stdClass $CFG
 * @global core_renderer $OUTPUT
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass        $course     The course to object for the report
 * @param context         $context    The context of the course
 */
function report_tecmides_extend_navigation_course( $reportnav, $course, $context )
{
    if ( has_capability('report/log:view', $context) )
    {
        $url = new moodle_url('/report/tecmides/index.php', array( 'id' => $course->id ));
        $reportnav->add(get_string('pluginname', 'report_tecmides'), $url);
    }

}
