<?php
/**
 * Path: /local/chartplugin/index.php
 */
require_once(__DIR__ . '/../../config.php');
require_login();

$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$render = optional_param('render', '', PARAM_ALPHANUM);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php'), ['type' => $type]);
$PAGE->set_title("AI Analytics Dashboard");
$PAGE->set_pagelayout('report');

$class = '\local_chartplugin\analytics\synopsis';

if (!class_exists($class)) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification("Updating UI...", "notifysuccess");
    echo $OUTPUT->footer();
    exit;
}

$defs = $class::get_button_definitions();
if (empty($render)) {
    $render = $defs[$type]['default_render'] ?? 'bar';
}

$class::save_view_history($USER->id, $type, $render);

$buttons = [];
foreach ($defs as $key => $opt) {
    $buttons[] = [
        'label' => $opt['label'],
        'url' => new moodle_url($PAGE->url, ['type' => $key, 'render' => $opt['default_render']]),
        'active_class' => ($type == $key ? 'deb-active-now' : '')
    ];
}

$template_data = [
    'ai_hero_text' => $class::get_ai_performance_delta($USER->id),
    'current_chart_label' => $defs[$type]['label'] ?? 'Analytics',
    'main_chart' => $OUTPUT->render($class::build_dynamic_chart($type, false, $render)),
    'history_blocks' => $class::get_history_blocks($USER->id),
    'buttons' => $buttons,
    'recovery_url' => (new moodle_url('/admin/tool/lp/plans.php', ['userid' => $USER->id]))->out(false),
    'is_bar'  => ($render === 'bar'),
    'is_line' => ($render === 'line'),
    'is_pie'  => ($render === 'pie'),
    'bar_url'  => new moodle_url($PAGE->url, ['type' => $type, 'render' => 'bar']),
    'line_url' => new moodle_url($PAGE->url, ['type' => $type, 'render' => 'line']),
    'pie_url'  => new moodle_url($PAGE->url, ['type' => $type, 'render' => 'pie']),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_chartplugin/analytics_page', $template_data);
echo $OUTPUT->footer();