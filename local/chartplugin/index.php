<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/index.php
 * Updated: Restored Balance Display and Admin Control Positioning
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $PAGE, $USER, $OUTPUT, $SESSION, $DB, $CFG;

require_login();

// --- 1. PARAMETERS & TESTING OVERRIDES ---
$type   = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$format = optional_param('format', 'bar', PARAM_ALPHANUMEXT);

// --- 2. ACCESS & CREDIT DETERMINATION ---
$access_status = local_chartplugin_get_access_status();
$is_enterprise = ($access_status === 'enterprise');

// Fetch the most recent record to get the live credit balance
$records = $DB->get_records('local_chartplugin_payments', ['userid' => $USER->id], 'id DESC', '*', 0, 1);
$record = reset($records);
$current_credits = $record ? $record->credits : 0;

// Set the date label for the status bar
$today_label = userdate(time(), '%A, %d %B %Y, %I:%M %p');

// --- 3. PAGE SETUP ---
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report');
$PAGE->set_title("Learning Analytics Dashboard");
$renderer = $PAGE->get_renderer('local_chartplugin');

// --- 4. NAVIGATION & LOCK LOGIC ---
$defs = \local_chartplugin\analytics\synopsis::get_button_definitions();
$nav_items = [];
foreach ($defs as $key => $details) {
    $is_locked = ($key === 'style' || $key === 'best_plan') && !$is_enterprise;
    $nav_items[] = [
        'name'    => $details['label'],
        'url'     => $is_locked ? '#' : new moodle_url('/local/chartplugin/index.php', ['type' => $key, 'format' => $details['default_render']]),
        'active'  => ($type === $key),
        'locked'  => $is_locked,
        'class'   => ($type === $key) ? 'deb-active-now' : 'deb-nav-btn',
        'tooltip' => $details['tooltip']
    ];
}

// --- 5. CHART DATA ---
$chart_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $type);
$chart_class = "\\core\\chart_" . $format;
$main_chart_obj = new $chart_class();
$main_chart_obj->add_series($chart_data['series']);
$main_chart_obj->set_labels($chart_data['labels']);

// --- 6. HISTORY BLOCKS ---
if (!isset($SESSION->chart_history)) { $SESSION->chart_history = []; }
if (empty($SESSION->chart_history) || $SESSION->chart_history[0] !== $type) {
    array_unshift($SESSION->chart_history, $type);
    $SESSION->chart_history = array_slice($SESSION->chart_history, 0, 3);
}
$history_blocks = [];
for ($i = 1; $i <= 2; $i++) {
    $h_key = $SESSION->chart_history[$i] ?? null;
    $block = new stdClass();
    $block->title = "Telemetry Pending";
    $block->html  = '<div class="text-center mt-5 text-muted small"><i class="fa fa-refresh fa-spin fa-2x mb-2"></i><br>Syncing...</div>';
    if ($h_key && isset($defs[$h_key])) {
        $h_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $h_key);
        $h_chart = new \core\chart_line();
        $h_chart->add_series($h_data['series']);
        $h_chart->set_labels($h_data['labels']);
        $block->title = $defs[$h_key]['label'];
        $block->html  = $renderer->render($h_chart);
    }
    $history_blocks[] = $block;
}

// --- 7. DATA ASSEMBLY ---
$template_data = [
    'is_enterprise'   => $is_enterprise,
    'ai_hero_text'    => \local_chartplugin\analytics\synopsis::get_ai_performance_delta($USER->id),
    'chart_title'     => $defs[$type]['label'],
    'chart_tooltip'   => $defs[$type]['tooltip'],
    'current_type'    => $type,
    'is_bar'          => ($format === 'bar'),
    'is_line'         => ($format === 'line'),
    'is_pie'          => ($format === 'pie'),
    'main_chart_html' => $renderer->render($main_chart_obj),
    'nav_items'       => $nav_items,
    'last_viewed'     => $history_blocks[0],
    'prev_viewed'     => $history_blocks[1],
    'brandorganization_footer' => 'Debonair Training Systems'
];

// --- 8. OUTPUT ---
echo $OUTPUT->header();

// --- RESTORED INFO BAR (Balance + Date + Control Center) ---
echo '<div class="alert alert-info d-flex justify-content-between align-items-center shadow-sm mb-4" style="border-left: 5px solid #007bff;">';
    echo '<div>';
        echo '<span class="mr-3"><strong>Credits:</strong> <span class="badge badge-pill badge-primary">' . $current_credits . '</span></span>';
        echo '<span><strong>Status:</strong> <small class="text-muted ml-1">' . $today_label . '</small></span>';
    echo '</div>';
    
    echo '<div>';
        if (has_capability('moodle/site:config', context_system::instance())) {
            echo '<a href="manage.php" class="btn btn-light btn-sm font-weight-bold shadow-sm">
                    <i class="fa fa-cog"></i> Control Center
                  </a>';
        }
        if (!$is_enterprise && $current_credits > 0) {
            echo '<a href="recovery.php" class="btn btn-success btn-sm shadow-sm ml-2"></a>';
        }
    echo '</div>';
echo '</div>';

echo $renderer->render_from_template('local_chartplugin/custom_header', $template_data);
echo $renderer->render_from_template('local_chartplugin/analytics_page', $template_data);
echo $renderer->render_from_template('local_chartplugin/custom_footer', $template_data);
echo $OUTPUT->footer();