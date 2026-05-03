<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/lib.php
 * Library for Debonair Learning Analytics Dashboard (Phase 4 Production Build)
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
        'Learning Analytics Dashboard', 
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
    public static function deliver_order($paymentid, $userid, $amount, $currency, $areaid) {
        global $DB;
        $credits_to_add = (int)$amount; 
        
        $record = $DB->get_record('local_chartplugin_payments', ['userid' => $userid]);
        if ($record) {
            $record->credits += $credits_to_add;
            $DB->update_record('local_chartplugin_payments', $record);
        } else {
            $newrecord = new stdClass();
            $newrecord->userid = $userid;
            $newrecord->credits = $credits_to_add;
            $newrecord->status = 'active';
            $DB->insert_record('local_chartplugin_payments', $newrecord);
        }
        return true;
    }
}

/**
 * Creates a Learning Plan and populates it with a competency for a specific user.
 */
function local_chartplugin_prescribe_learning_plan($userid) {
    global $DB, $USER;

    // 1. Fetch the scale.
    $scale = $DB->get_record_sql("SELECT id, scale FROM {scale} WHERE scale IS NOT NULL", [], IGNORE_MULTIPLE);
    if (!$scale) {
        throw new \moodle_exception('noscale', 'local_chartplugin');
    }

// 2. Build the configuration with STRICT integer types.
$items = explode(',', $scale->scale);
$config = [];
$itemcount = count($items);

foreach ($items as $index => $name) {
    // We use a simple array structure. 
    // Explicitly casting to (int) removes the quotes you see in your debug logs.
    $config[] = [
        'id' => (int)($index + 1),
        'scaledefault' => ($index === 0) ? 1 : 0,
        'proficient' => ($index === $itemcount - 1) ? 1 : 0
    ];
}

// 3. Prepare the Framework data object.
$frameworkdata = new \stdClass();
$frameworkdata->shortname = 'AI Recovery Framework ' . time();
$frameworkdata->idnumber = 'AI_REC_' . $userid . '_' . time();
$frameworkdata->description = 'Automated recovery path.';
$frameworkdata->descriptionformat = FORMAT_HTML;
$frameworkdata->visible = 1;
$frameworkdata->scaleid = (int)$scale->id;
$frameworkdata->scaleconfiguration = json_encode($config); // Should now result in: [{"id":1,"scaledefault":1...}]
$frameworkdata->contextid = \context_system::instance()->id;
$frameworkdata->usermodified = $USER->id;
$frameworkdata->timecreated = time();
$frameworkdata->timemodified = time();

    // 4. Force Create the Framework via DB (Bypassing the persistent validator)
$frameworkid = $DB->insert_record('competency_framework', $frameworkdata);

// Now load the framework object from the ID so the rest of the code works
$framework = new \core_competency\competency_framework($frameworkid);

    // 5. Create the Competency.
    $competency = new \core_competency\competency(0, (object)[
        'shortname' => 'Targeted Skill Recovery',
        'idnumber' => 'SKILL_' . time(),
        'description' => 'Focus area based on AI analytics.',
        'descriptionformat' => FORMAT_HTML,
        'competencyframeworkid' => $framework->get('id'),
        'parentid' => 0,
        'path' => '/',
        'sortorder' => 0,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ]);
    $competency->create();

    // 6. Create the Learning Plan.
    $plan = new \core_competency\plan(0, (object)[
        'name' => 'Personalized Recovery Plan for User ' . $userid,
        'description' => 'AI generated recovery path.',
        'descriptionformat' => FORMAT_HTML,
        'userid' => $userid,
        'status' => \core_competency\plan::STATUS_ACTIVE,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ]);
    $plan->create();

    // 7. Link Competency to Plan.
    $lpc = new \core_competency\plan_competency(0, (object)[
        'planid' => $plan->get('id'),
        'competencyid' => $competency->get('id'),
        'sortorder' => 0,
        'usermodified' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ]);
    $lpc->create();

    return $plan;
}