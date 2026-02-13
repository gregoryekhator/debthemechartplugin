<?php
// /var/www/html/moodle_test/local/chartplugin/lib.php
defined('MOODLE_INTERNAL') || die();

/**
 * Optimized Grade Fetcher with Static Caching to kill the 4s lag.
 */
function local_chartplugin_get_user_grade($courseid, $userid) {
    global $DB;
    static $grade_cache = []; 

    $cache_key = "{$courseid}_{$userid}";
    if (isset($grade_cache[$cache_key])) {
        return $grade_cache[$cache_key];
    }

    $sql = "SELECT gg.finalgrade 
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gi.courseid = :courseid 
              AND gi.itemtype = 'course'
              AND gg.userid = :userid";

    $grade = $DB->get_field_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);
    $result = $grade ? (float)$grade : 0.0;
    
    $grade_cache[$cache_key] = $result;
    return $result;
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