<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/lib.php
 * Library for Debonair Learning Flight Deck (Phase 4 Production Build)
 */

defined('MOODLE_INTERNAL') || die();

/**
 * 1. NAVIGATION API HOOKS
 * Adds the Flight Deck to the Moodle main drawer and user menu.
 */

function local_chartplugin_extend_navigation(global_navigation $nav) {
    if (isloggedin()) {
        $node = $nav->add(
            'My Analytics', 
            new moodle_url('/local/chartplugin/index.php'), 
            navigation_node::TYPE_CUSTOM, 
            null, 
            'deb_analytics'
        );
        $node->showinflatnavigation = true;
        $node->icon = new pix_icon('i/report', '');
    }
}

function local_chartplugin_extend_navigation_user(navigation_node $navnode, $user, $context, $course, $abspath) {
    $navnode->add(
        'Learning Flight Deck', 
        new moodle_url('/local/chartplugin/index.php'), 
        navigation_node::TYPE_SETTING, 
        null, 
        'flightdeck', 
        new pix_icon('i/charts', '')
    );
}

/**
 * 2. GRADEBOOK API HELPERS
 * Functions to pull live data for our dynamic charts.
 */

function local_chartplugin_get_user_grade($userid, $courseid) {
    global $DB;
    $sql = "SELECT gg.finalgrade 
            FROM {grade_grades} gg 
            JOIN {grade_items} gi ON gg.itemid = gi.id 
            WHERE gg.userid = :userid AND gi.courseid = :courseid AND gi.itemtype = 'course'";
    return (float)$DB->get_field_sql($sql, ['userid' => $userid, 'courseid' => $courseid]) ?: 0.0;
}

function local_chartplugin_get_start_date($userid) {
    global $DB;
    return $DB->get_field('user', 'timecreated', ['id' => $userid]) ?: time();
}

function local_chartplugin_get_top_courses($userid) {
    global $DB;
    $sql = "SELECT gi.id, gi.courseid, gi.itemname, gg.finalgrade 
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id 
            WHERE gg.userid = :userid AND gi.itemtype = 'course'
            ORDER BY gg.finalgrade DESC LIMIT 3";
    return $DB->get_records_sql($sql, ['userid' => $userid]);
}

function local_chartplugin_get_lowest_courses($userid) {
    global $DB;
    $sql = "SELECT gi.id, gi.courseid, gi.itemname, gg.finalgrade 
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id 
            WHERE gg.userid = :userid AND gi.itemtype = 'course'
            ORDER BY gg.finalgrade ASC LIMIT 3";
    return $DB->get_records_sql($sql, ['userid' => $userid]);
}

function local_chartplugin_get_cohort_average($courseid) {
    global $DB;
    $sql = "SELECT AVG(gg.finalgrade) FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gi.courseid = :courseid AND gi.itemtype = 'course' AND gg.finalgrade IS NOT NULL";
    return (float)$DB->get_field_sql($sql, ['courseid' => $courseid]) ?: 75.0;
}

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

/**
 * 3. FILE API (PDF GENERATION STUB)
 * Prepared for Sprint 3 - Premium PDF Downloads
 */
function local_chartplugin_get_report_filename($userid) {
    return "Flight_Log_User_" . $userid . "_" . date('Y-m-d') . ".pdf";
}

/**
 * Saves current chart data into the Moodle Session to drive the Cockpit UI.
 */
function local_chartplugin_save_history($data) {
    global $SESSION;
    if (!isset($SESSION->chart_history)) {
        $SESSION->chart_history = [];
    }
    // Don't save if it's the same as the last one
    if (!empty($SESSION->chart_history) && $SESSION->chart_history[0] === $data) {
        return;
    }
    array_unshift($SESSION->chart_history, $data);
    // Keep only the last 3 views
    if (count($SESSION->chart_history) > 3) {
        array_pop($SESSION->chart_history);
    }
}

/**
 * Core access check for Enterprise features.
 * Priority: Session Toggle -> Trial Check -> Subscription Expiry -> Credit Balance.
 */
function local_chartplugin_get_access_status() {
    global $USER, $DB, $SESSION;

    // 1. Manual Session Toggle for Admin Testing.
    // Use index.php?test_lock=on to force a locked state.
    if (!empty($SESSION->local_chartplugin_force_lock)) {
        return 'freemium';
    }

    // 2. Trial Period Check (7 Days from account creation).
    $trial_duration = 7 * 24 * 60 * 60;
    if (($USER->timecreated + $trial_duration) > time()) {
        return 'enterprise';
    }

    // 3. Subscription & Credit Check.
    $record = $DB->get_record('local_chartplugin_payments', ['userid' => $USER->id]);
    if ($record) {
        // Check if subscription timestamp is still in the future.
        if (!empty($record->valid_until) && $record->valid_until > time()) {
            return 'enterprise';
        }
        // Check if they have unused credits.
        if ($record->credits > 0) {
            // Note: We don't return enterprise here automatically so they 
            // have to "click" the Boost button to activate the 30-day window.
            if (get_user_preference('local_chartplugin_license', 'freemium', $USER->id) === 'enterprise') {
                return 'enterprise';
            }
        }
    }

    return 'freemium';
}