<?php
/**
 * index.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Path: /var/www/html/moodle_test/local/chartplugin/index.php
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $PAGE, $USER, $OUTPUT, $SESSION, $DB;

require_login();

// --- 1. PARAMETERS ---
$type   = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$format = optional_param('format', 'bar', PARAM_ALPHANUMEXT);

// --- 2. ACCESS & CREDIT DETERMINATION ---
$access_status = local_chartplugin_get_access_status();
$is_enterprise = ($access_status === 'enterprise');

// Fetch live credit balance
$records = $DB->get_records('local_chartplugin_payments', ['userid' => $USER->id], 'id DESC', '*', 0, 1);
$record = reset($records);
$current_credits = $record ? $record->credits : 0;

// Date for the status bar
$today_label = userdate(time(), '%A, %d %B %Y');

// --- 3. PAGE SETUP ---
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report');
$PAGE->set_title("Learning Analytics Dashboard");
$renderer = $PAGE->get_renderer('local_chartplugin');

// --- 4. NAVIGATION LOGIC (Uses Updated synopsis.php with URLs) ---
$defs = \local_chartplugin\analytics\synopsis::get_button_definitions();
$nav_items = [];
foreach ($defs as $key => $details) {
    // Determine URL: Use specific URL from definition if it exists, else generate default
    $target_url = isset($details['url']) ? $details['url'] : new moodle_url('/local/chartplugin/index.php', ['type' => $key, 'format' => $details['default_render']]);
    
    $nav_items[] = [
        'name'    => $details['label'],
        'url'     => $target_url,
        'active'  => ($type === $key),
        'class'   => ($type === $key) ? 'deb-active-now' : 'deb-nav-btn',
        'tooltip' => $details['tooltip']
    ];
}

// --- 5. MAIN CHART ---
$chart_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $type);
$chart_class = "\\core\\chart_" . $format;
$main_chart_obj = new $chart_class();
$main_chart_obj->add_series($chart_data['series']);
$main_chart_obj->set_labels($chart_data['labels']);

// --- 6. HISTORY BLOCKS (Sidebar) ---
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
        
        // Use set_title instead of set_identifier to avoid the "Undefined method" error
        // Moodle uses this string to help uniquely identify the canvas element
        $h_chart->set_title($defs[$h_key]['label'] . " Snapshot"); 
        
        $block->title = $defs[$h_key]['label'];
        $block->html  = $renderer->render($h_chart);
    }
    $history_blocks[] = $block;
}

// --- 7. DATA ASSEMBLY ---
$template_data = [
    'sesskey'         => sesskey(),
    'is_enterprise'   => $is_enterprise,
    'current_credits' => $current_credits,
    'today_label'     => $today_label,
    'is_admin'        => has_capability('moodle/site:config', context_system::instance()),
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
    'prev_viewed'     => $history_blocks[1]
];

// --- 8. OUTPUT ---
echo $OUTPUT->header();
echo $renderer->render_from_template('local_chartplugin/custom_header', $template_data);
echo $renderer->render_from_template('local_chartplugin/analytics_page', $template_data);
echo $renderer->render_from_template('local_chartplugin/custom_footer', $template_data);
echo $OUTPUT->footer();