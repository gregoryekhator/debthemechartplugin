<?php
/**
 * Path: /local/chartplugin/index.php
 */
require_once(__DIR__ . '/../../config.php');
global $PAGE, $USER, $DB, $OUTPUT, $SESSION;

require_login();

// 1. Parameters
$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$render_type = optional_param('render', '', PARAM_ALPHANUM);
$safemode = optional_param('safemode', 0, PARAM_INT); // Safe Mode Switch

// 2. Moodle Page Setup (Initialization Block)
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type]));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Debonair Training AI Dashboard");
$PAGE->set_pagelayout('report'); // Technical Debt: Standardizing to report layout
$renderer = $PAGE->get_renderer('local_chartplugin');

// 3. Emergency Safe Mode Bypass
if ($safemode) {
    echo $OUTPUT->header();
    $data = [
        'ai_hero_text' => 'SAFE MODE ACTIVE: Chart rendering bypassed for stability.',
        'user_level' => 'DEBUG',
        'is_safemode' => true,
        'main_chart' => '<div class="alert alert-warning">Charts are currently disabled in Safe Mode to prevent render loops.</div>'
    ];
    echo $OUTPUT->render_from_template('local_chartplugin/analytics_page', $data);
    echo $OUTPUT->footer();
    exit;
}

// 4. History Rotation Logic (SESSION-based)
if (!isset($SESSION->deb_history)) { $SESSION->deb_history = []; }
if (empty($SESSION->deb_history) || $SESSION->deb_history[0] !== $type) {
    array_unshift($SESSION->deb_history, $type);
    $SESSION->deb_history = array_slice($SESSION->deb_history, 0, 3);
}

// 5. Load Definitions & Determine Render Mode
$defs = \local_chartplugin\analytics\synopsis::get_button_definitions();
$current_def = $defs[$type] ?? $defs['synopsis'];
$selected_render = $render_type ?: $current_def['default_render'];

// 6. Build Main Chart
$chart_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $type);
if ($selected_render === 'line') {
    $chart = new core\chart_line();
} else if ($selected_render === 'pie') {
    $chart = new core\chart_pie();
} else {
    $chart = new core\chart_bar();
}
$chart->add_series($chart_data['series']);
$chart->set_labels($chart_data['labels']);

// 7. Sidebar History Logic (Associative Learning)
$history_blocks = [];
$h_labels = [1 => 'Last View', 2 => 'Previous View'];
foreach ($h_labels as $idx => $label) {
    $h_type = $SESSION->deb_history[$idx] ?? null;
    if ($h_type && isset($defs[$h_type])) {
        $h_def = $defs[$h_type];
        $h_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $h_type);
        $h_chart = new core\chart_line(); 
        $h_chart->add_series($h_data['series']);
        $h_chart->set_labels($h_data['labels']);
        
        $associative_prefix = ($idx == 2) ? "To boost your learning capacity; this comparison greatly boosts your associative learning skills. " : "";

        $history_blocks[] = [
            'title' => strtoupper($label) . ': ' . $h_def['label'],
            'subtitle' => $associative_prefix . $h_def['tooltip'],
            'content' => $renderer->render($h_chart)
        ];
    }
}

// 8. Assemble Template Data
$data = [
    'user_level' => 'ENTERPRISE', 
    'ai_hero_text' => \local_chartplugin\analytics\synopsis::get_ai_performance_delta($USER->id),
    'main_chart' => $renderer->render($chart),
    'chart_title' => $current_def['label'],
    'chart_subtitle' => $current_def['tooltip'],
    'history_blocks' => $history_blocks,
    'current_type' => $type,
    'is_bar' => ($selected_render == 'bar'),
    'is_line' => ($selected_render == 'line'),
    'is_pie' => ($selected_render == 'pie'),
    'buttons' => array_map(function($k, $v) use ($type) {
        return [
            'label' => $v['label'], 
            'url' => new moodle_url('/local/chartplugin/index.php', ['type' => $k]), 
            'active_class' => ($k == $type ? 'deb-active-now' : 'deb-inactive')
        ];
    }, array_keys($defs), $defs)
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_chartplugin/analytics_page', $data);
echo $OUTPUT->footer();