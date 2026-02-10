<?php
// /var/www/html/moodle_test/local/chartplugin/lib.php

defined('MOODLE_INTERNAL') || die();

/**
 * Inject chart assets and container on dashboard only.
 */
function local_chartplugin_extend_navigation(global_navigation $nav) {
    global $PAGE;
    if ($PAGE->pagetype !== 'my-index') {
        return;
    }
}

/**
 * Calculates the average grade for all students in a given course.
 */
function local_chartplugin_get_cohort_average($courseid) {
    global $DB;

    $sql = "SELECT AVG(gg.finalgrade) 
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gi.courseid = :courseid 
              AND gi.itemtype = 'course'";

    $average = $DB->get_field_sql($sql, ['courseid' => $courseid]);

    return $average ? number_format((float)$average, 2) : "0.00";
}

/**
 * Fetches the grade for a specific user in a specific course.
 */
function local_chartplugin_get_user_grade($courseid, $userid) {
    global $DB;

    $sql = "SELECT gg.finalgrade 
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gi.courseid = :courseid 
              AND gi.itemtype = 'course'
              AND gg.userid = :userid";

    $grade = $DB->get_field_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);

    return $grade ? (float)$grade : 0.0;
}

/**
 * Fetches the three lowest course grades for the user.
 */
function local_chartplugin_get_lowest_courses($userid) {
    global $DB;

    $sql = "SELECT gi.itemname, gg.finalgrade 
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gg.userid = :userid 
              AND gi.itemtype = 'course'
            ORDER BY gg.finalgrade ASC
            LIMIT 3";

    return $DB->get_records_sql($sql, ['userid' => $userid]);
}