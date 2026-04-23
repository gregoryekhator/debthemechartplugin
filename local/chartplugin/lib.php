<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/lib.php
 * Library for Debonair Learning Flight Deck (Phase 4 Production Build)
 */

defined('MOODLE_INTERNAL') || die();

/**
 * 1. NAVIGATION API HOOKS
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
 * 3. HISTORY & SESSION API
 */
function local_chartplugin_save_history($data) {
    global $SESSION;
    if (!isset($SESSION->chart_history)) {
        $SESSION->chart_history = [];
    }
    if (!empty($SESSION->chart_history) && $SESSION->chart_history[0] === $data) {
        return;
    }
    array_unshift($SESSION->chart_history, $data);
    if (count($SESSION->chart_history) > 3) {
        array_pop($SESSION->chart_history);
    }
}

/**
 * 4. CORE ACCESS CHECK (Day 9 Polish)
 */
function local_chartplugin_get_access_status() {
    global $USER, $DB;

    // 1. Fetch the DB record first to see if we have manual overrides or credits
    $records = $DB->get_records('local_chartplugin_payments', ['userid' => $USER->id], 'id DESC', '*', 0, 1);
    $record = reset($records);

    // 2. Priority: If a manual "valid_until" exists and is in the future, it's Enterprise
    if ($record && !empty($record->valid_until) && $record->valid_until > time()) {
        return 'enterprise';
    }

    // 3. Trial Period Check
    // ADMIN FIX: We ignore the trial for Admins so you can test the "Boost" button logic
    if (!is_siteadmin()) {
        $trial_duration = 7 * 24 * 60 * 60;
        if (isset($USER->timecreated) && ($USER->timecreated + $trial_duration) > time()) {
            return 'enterprise';
        }
    }

    return 'freemium';
}

/**
 * 5. PAYMENT CALLBACK (Standard Moodle Style)
 */
class local_chartplugin_payment_callback {

    /**
     * Provides the cost to the Payment Gateway.
     * $itemid in our case is the number of credits the user selected (10, 50, or 100).
     */
    public static function get_amount(string $paymentarea, int $itemid): \core_payment\amount {
        // Map the itemid (credits) to a price
        $prices = [
            10  => 5.00,
            50  => 20.00,
            100 => 35.00
        ];

        $price = $prices[$itemid] ?? 5.00; // Default to $5 if something goes wrong
        return new \core_payment\amount($price, 'USD');
    }

    /**
     * This is called automatically by Moodle AFTER the PayPal transaction is successful.
     */
    public static function deliver_order(int $paymentid, int $userid, float $amount, string $currency, int $itemid): bool {
        global $DB;
        
        // Use $itemid (the number of credits purchased) rather than the dollar amount
        $credits_to_add = $itemid; 
        
        $record = $DB->get_record('local_chartplugin_payments', ['userid' => $userid]);
        
        if ($record) {
            // Update existing balance
            $record->credits += $credits_to_add;
            $DB->update_record('local_chartplugin_payments', $record);
        } else {
            // Create new record for first-time buyers
            $newrecord = new stdClass();
            $newrecord->userid = $userid;
            $newrecord->credits = $credits_to_add;
            $newrecord->status = 'active';
            $newrecord->valid_until = 0;
            $DB->insert_record('local_chartplugin_payments', $newrecord);
        }

        return true;
    }
}