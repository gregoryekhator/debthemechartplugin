<?php
/**
 * Path: /local/chartplugin/index.php
 * Day 7: Stable Beauty Restoration - Merging Feb Architecture with Sprint 3 Logic
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
global $PAGE, $USER, $OUTPUT, $SESSION;

require_login();

// 1. Setup Parameters
$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$format = optional_param('format', 'bar', PARAM_ALPHANUMEXT);

// 2. Moodle Page Setup (The "Island" Configuration)
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report'); // The February Secret Sauce
$renderer = $PAGE->get_renderer('local_chartplugin');

// 3. History Rotation (Sprint 3 Logic)
if (!isset($SESSION->deb_history)) { $SESSION->deb_history = []; }
if (empty($SESSION->deb_history) || $SESSION->deb_history[0] !== $type) {
    array_unshift($SESSION->deb_history, $type);
    $SESSION->deb_history = array_slice($SESSION->deb_history, 0, 3);
}

// 4. Data Loading
$defs = \local_chartplugin\analytics\synopsis::get_button_definitions();
$current_def = $defs[$type] ?? $defs['synopsis'];
$chart_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $type);

// 5. Main Chart Construction
$chart_class = "\\core\\chart_" . $format;
$chart = class_exists($chart_class) ? new $chart_class() : new \core\chart_bar();
$chart->add_series($chart_data['series']);
$chart->set_labels($chart_data['labels']);

// 6. Associative History Blocks (Sprint 3 Logic)
$history_blocks = [];
foreach ([1, 2] as $idx) {
    $h_type = $SESSION->deb_history[$idx] ?? null;
    if ($h_type && isset($defs[$h_type])) {
        $h_def = $defs[$h_type];
        $h_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $h_type);
        $h_chart = new core\chart_line(); 
        $h_chart->add_series($h_data['series']);
        $h_chart->set_labels($h_data['labels']);
        
        $history_blocks[] = [
            'title' => ($idx == 1 ? "LAST VIEW: " : "PREVIOUS: ") . $h_def['label'],
            'subtitle' => ($idx == 2 ? "Associative learning boost: " : "") . $h_def['tooltip'],
            'content' => $renderer->render($h_chart)
        ];
    }
}

$nav_items = [];
    foreach ($defs as $key => $details) {
        // Determine if the item should be locked (Logic from image 2ab763.png)
        $is_locked = ($key === 'best_plan' || $key === 'style') && empty($SESSION->is_enterprise_user);
        
        $nav_items[] = [
            'name'   => $details['label'],
             'url'    => $is_locked ? '#' : new moodle_url('/local/chartplugin/index.php', ['type' => $key]),
            'active' => ($type === $key),
            'locked' => $is_locked
        ];
    }

// 7. Data Assembly for Template (February Naming + Sprint 3 Data)
$data = [
    'user_level' => !empty($SESSION->is_enterprise_user) ? 'ENTERPRISE' : 'FREEMIUM',
    'ai_hero_text' => \local_chartplugin\analytics\synopsis::get_ai_performance_delta($USER->id),
    'main_chart' => $renderer->render($chart),
    'chart_title' => $current_def['label'],
    'nav_items'     => $nav_items, // Matches the new template loop
    'chart_subtitle' => $current_def['tooltip'],
    'history_blocks' => $history_blocks,
    'brandorganization_footer' => 'Debonair Training Limited',
    'brandwebsite_footer' => 'www.debonairtraining.com',
    'brandemail_footer' => 'info@debonairtraining.com',
    'brandphone_footer' => '+44 (0) 20 7946 0000',
    'year' => date('Y'),
    'buttons' => array_map(function($k, $v) use ($type) {
        return [
            'label' => $v['label'], 
            'url' => new moodle_url('/local/chartplugin/index.php', ['type' => $k]), 
            'active_class' => ($k == $type ? 'deb-active-now' : 'btn-light')
        ];
    }, array_keys($defs), $defs)
];

echo $OUTPUT->header();
echo $renderer->render_from_template('local_chartplugin/custom_header', $data);
echo $renderer->render_from_template('local_chartplugin/analytics_page', $data);
echo $renderer->render_from_template('local_chartplugin/custom_footer', $data);
echo $OUTPUT->footer();