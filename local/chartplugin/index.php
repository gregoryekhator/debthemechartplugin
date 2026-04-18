<?php
/**
 * Path: /local/chartplugin/index.php
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
global $PAGE, $USER, $OUTPUT, $SESSION;

require_login();

$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$format = optional_param('format', 'bar', PARAM_ALPHANUMEXT);

// 1. GLOBAL STATUS CHECK
$idnumber = $USER->idnumber ?? '';
$is_enterprise = (strpos($idnumber, 'ENT-') === 0);
$is_trial = (strpos($idnumber, 'TRIAL-') === 0);
$has_access = ($is_enterprise || $is_trial);

$trial_expiry = 0;
if ($is_trial) {
    $trial_expiry = (int)str_replace('TRIAL-', '', $idnumber);
}

$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type, 'format' => $format]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report');

$renderer = $PAGE->get_renderer('local_chartplugin');
$defs = \local_chartplugin\analytics\synopsis::get_button_definitions();

// 2. Navigation & Locking Logic
$nav_items = [];
foreach ($defs as $key => $def) {
    $is_locked = (!$has_access && ($key === 'learning_plan' || $key === 'style_insights'));
    $nav_items[] = [
        'name' => $def['label'],
        'url'  => new moodle_url('/local/chartplugin/index.php', ['type' => $key, 'format' => $format]),
        'active' => ($type === $key),
        'locked' => $is_locked,
        'show_upgrade_pill' => $is_locked
    ];
}

// 3. AI Descriptions
$descriptions = [
    'synopsis' => "Your overall performance since sign-up. Keep pushing to exceed the cohort average!",
    'monthly' => "Your performance over the last month vs the class performance.",
    'best_courses' => "Your best courses by scores vs the class performance. Leverage these strengths!",
    'lowest_courses' => "Your lowest courses. The best recovery plan through gap analysis starts here.",
    'grade_dist' => "Grade distribution curve in your cohort. Mouse over for more information to improve.",
    'work_rate' => "This is your work rate relative to your class.",
    'learning_plan' => "Performance on your current competency plan. Meet the template standard to level up.",
    'style_insights' => "Your learning style based on EdTech insights. Turn up the dial on your sensory strengths!"
];

// 4. Main Chart
$chart_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $type);
$chart_data['title'] = $defs[$type]['label']; 
local_chartplugin_save_history($chart_data);

$chart_class = "\\core\\chart_" . $format;
$chart = class_exists($chart_class) ? new $chart_class() : new \core\chart_bar();
$chart->add_series($chart_data['series']);
$chart->set_labels($chart_data['labels']);
$chart->get_yaxis(0, true)->set_min(0);

// 5. Cockpit History (Last and Previous)
$last_viewed = null;
if (!empty($SESSION->chart_history[1])) {
    $h = $SESSION->chart_history[1];
    $c = new core\chart_line(); $c->add_series($h['series']); $c->set_labels($h['labels']);
    $c->get_yaxis(0, true)->set_min(0);
    $last_viewed = (object)['html' => $renderer->render($c), 'title' => $h['title']];
}

$prev_viewed = null;
if (!empty($SESSION->chart_history[2])) {
    $h = $SESSION->chart_history[2];
    $c = new core\chart_line(); $c->add_series($h['series']); $c->set_labels($h['labels']);
    $c->get_yaxis(0, true)->set_min(0);
    $prev_viewed = (object)['html' => $renderer->render($c), 'title' => $h['title']];
}

$data = [
    'is_enterprise' => $is_enterprise,
    'is_trial' => $is_trial,
    'is_freemium' => !$has_access,
    'has_access' => $has_access,
    'trial_expiry_timestamp' => $trial_expiry,
    'nav_items' => $nav_items,
    'chart_title' => $defs[$type]['label'],
    'chart_tooltip' => $descriptions[$type] ?? "Analyze metrics to boost associative learning.",
    'main_chart_html' => $renderer->render($chart),
    'last_viewed' => $last_viewed,
    'prev_viewed' => $prev_viewed,
    'current_type' => $type,
    'is_bar' => ($format === 'bar'),
    'is_line' => ($format === 'line'),
    'current_year' => date('Y')
];

echo $OUTPUT->header();
echo $renderer->render_from_template('local_chartplugin/custom_header', $data);
echo $renderer->render_from_template('local_chartplugin/analytics_page', $data);
echo $renderer->render_from_template('local_chartplugin/custom_footer', $data);
echo $OUTPUT->footer();