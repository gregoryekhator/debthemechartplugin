<?php
// /var/www/html/moodle_test/local/chartplugin/lib.php
defined('MOODLE_INTERNAL') || die();

function local_chartplugin_get_user_grade($courseid, $userid) {
    global $DB;
    $sql = "SELECT gg.finalgrade FROM {grade_grades} gg 
            JOIN {grade_items} gi ON gg.itemid = gi.id 
            WHERE gi.courseid = :courseid AND gi.itemtype = 'course' AND gg.userid = :userid";
    return (float)$DB->get_field_sql($sql, ['courseid' => $courseid, 'userid' => $userid]) ?: 0.0;
}

function local_chartplugin_get_cohort_average($courseid) {
    global $DB;
    $sql = "SELECT AVG(gg.finalgrade) FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gi.courseid = :courseid AND gi.itemtype = 'course' AND gg.finalgrade IS NOT NULL";
    return (float)$DB->get_field_sql($sql, ['courseid' => $courseid]) ?: 75.0;
}

/**
 * NEW: Powers the Enterprise Grade Distribution (Bell Curve) View.
 */
function local_chartplugin_get_grade_distribution($courseid) {
    global $DB;
    $brackets = ['0-40' => 0, '41-60' => 0, '61-80' => 0, '81-100' => 0];
    $sql = "SELECT gg.finalgrade FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gi.courseid = :courseid AND gi.itemtype = 'course' AND gg.finalgrade IS NOT NULL";
    $grades = $DB->get_records_sql($sql, ['courseid' => $courseid]);
    
    foreach ($grades as $g) {
        $v = $g->finalgrade;
        if ($v <= 40) $brackets['0-40']++;
        else if ($v <= 60) $brackets['41-60']++;
        else if ($v <= 80) $brackets['61-80']++;
        else $brackets['81-100']++;
    }
    return $brackets;
}

function local_chartplugin_get_lowest_courses($userid) {
    global $DB;
    $sql = "SELECT gi.courseid, gi.itemname, gg.finalgrade 
            FROM {grade_grades} gg JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gg.userid = :userid AND gi.itemtype = 'course' ORDER BY gg.finalgrade ASC LIMIT 3";
    return $DB->get_records_sql($sql, ['userid' => $userid]);
}