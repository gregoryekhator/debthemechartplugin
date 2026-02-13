<?php
// /var/www/html/moodle_test/local/chartplugin/index.php

require('../../config.php');
require_once($CFG->dirroot . '/local/chartplugin/lib.php');
require_login();

$context = context_system::instance();
$PAGE->set_context($context);

$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type]));

$PAGE->set_pagelayout('report');
$PAGE->set_title('Learning Analytics');

if (!isset($SESSION->history_stack)) {
    $SESSION->history_stack = ['synopsis', 'synopsis', 'synopsis'];
}
if ($type !== $SESSION->history_stack[0]) {
    array_unshift($SESSION->history_stack, $type);
    $SESSION->history_stack = array_slice($SESSION->history_stack, 0, 3);
}

$labels = [
    'synopsis' => 'Synopsis (to date)', 'best' => 'Best Courses', 
    'cohort' => 'Cohort Performance', 'plan' => 'Best Learning Plan',
    '30day' => '30 Day Synopsis', 'lowest' => 'Lowest Courses', 
    'freq' => 'Study Frequency', 'style' => 'Preferred Learning Style'
];

echo $OUTPUT->header();

// Fetch data for the logged-in user
$userid = $USER->id; 
$courseid = 2;
$analytics_record = $DB->get_record('local_chartplugin_trends', ['userid' => $userid]);

$cohort_avg = (float)($analytics_record->cohort_avg ?? 0.0);
$user_grade = local_chartplugin_get_user_grade($courseid, $userid);
$prediction = (float)($analytics_record->prediction ?? 0.0);

// AI Insight Logic
$diff = $user_grade - $cohort_avg;
$is_alert = false;

if ($user_grade == 0) {
    $ai_message = "Analysis pending. You haven't received a final grade for this course yet.";
} else if ($diff >= 0) {
    $ai_message = "Great work! You are performing " . number_format($diff, 2) . "% above the cohort average.";
} else {
    $ai_message = "Alert: You are " . number_format(abs($diff), 2) . "% below average. " .
                  "ML Forecast: Next month's predicted grade is " . round($prediction, 1) . "%.";
    $is_alert = true;
}

// PERSONALIZATION GATE: Only show recovery if a plan exists AND user is lagging or looking at risk data
$planid = 0;
try {
    $planid = $DB->get_field('competency_plan', 'id', ['userid' => $userid, 'status' => 1], IGNORE_MULTIPLE);
} catch (Exception $e) {
    // Fallback if competency table is missing or errors
    $planid = 0;
}

$correctional_tabs = ['plan', 'lowest', 'synopsis'];
$is_risk_context = in_array($type, $correctional_tabs);

// Logic: Button appears if (plan exists) AND (either we are alerted OR on a risk tab)
$show_recovery = ($planid && ($is_alert || $is_risk_context));

$plan_url_string = '';
if ($planid) {
    $plan_url = new moodle_url('/admin/tool/lp/plan.php', ['id' => $planid]);
    $plan_url_string = $plan_url->out(false);
}

echo $OUTPUT->render_from_template('local_chartplugin/analytics_page', [
    'chart_title' => $labels[$type],
    'main_chart' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_dynamic_chart($type, false)),
    'ai_hero_text' => $ai_message,
    'is_alert' => $is_alert,
    'show_recovery' => $show_recovery,
    'plan_url' => $plan_url_string,
    'buttons' => array_map(function($k, $v) use ($type) {
        return ['label' => $v, 'url' => new moodle_url('/local/chartplugin/index.php', ['type' => $k]), 'active' => ($type == $k)];
    }, array_keys($labels), $labels),
    'history_blocks' => [
        ['title' => 'Last Chart', 'subtitle' => $labels[$SESSION->history_stack[1]], 'content' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_trend_chart($userid))],
        ['title' => 'Previous Chart', 'subtitle' => $labels[$SESSION->history_stack[2]], 'content' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_trend_chart($userid))]
    ]
]);

echo $OUTPUT->footer();