<?php
/**
 * backup.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('../../config.php');
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php'));
$PAGE->set_title('Learning Analytics Dashboard');
$PAGE->set_heading('Learning Analytics Dashboard');
$PAGE->set_pagelayout('report'); 

$type = optional_param('type', 'default', PARAM_ALPHANUMEXT);

// 1. SESSION TRACKING (Store the TYPE, not the HTML)
if (!isset($SESSION->last_chart_type)) {
    $SESSION->last_chart_type = 'default';
    $SESSION->prev_chart_type = 'default';
}

// 2. BUILD THE OBJECTS (Reconstructing for JS ID Unique Generation)
// Main Chart
$mainchart_obj = \local_chartplugin\analytics\synopsis::build_dynamic_chart($type);
$rendered_main = $OUTPUT->render($mainchart_obj);

// Sidebar: Last Chart
$last_type = $SESSION->last_chart_type;
$last_chart_obj = \local_chartplugin\analytics\synopsis::build_dynamic_chart($last_type);
$rendered_last = $OUTPUT->render($last_chart_obj);

// Sidebar: Previous Chart
$prev_type = $SESSION->prev_chart_type;
$prev_chart_obj = \local_chartplugin\analytics\synopsis::build_dynamic_chart($prev_type);
$rendered_prev = $OUTPUT->render($prev_chart_obj);

// 3. UPDATE SESSION FOR NEXT CLICK
if ($type !== 'default') {
    $SESSION->prev_chart_type = $SESSION->last_chart_type;
    $SESSION->last_chart_type = $type;
}

// 4. AI & BUTTONS
$ai_payload = "Select a category to begin analysis.";
try {
    $ai_payload = \local_chartplugin\analytics\analyst::get_ai_summary($type);
} catch (\Exception $e) { $ai_payload = "AI standby."; }

$buttons = [
    ['label' => 'Synopsis (to date)', 'url' => '?type=default', 'active' => ($type === 'default')],
    ['label' => 'Best Courses', 'url' => '?type=best', 'active' => ($type === 'best')],
    ['label' => 'Cohort Performance', 'url' => '?type=cohort', 'active' => ($type === 'cohort')],
    ['label' => 'Best Learning Plan', 'url' => '?type=plan', 'active' => ($type === 'plan')],
    ['label' => '30-Day Synopsis', 'url' => '?type=30day', 'active' => ($type === '30day')],
    ['label' => 'Lowest Courses', 'url' => '?type=lowest', 'active' => ($type === 'lowest')],
    ['label' => 'Study Frequency', 'url' => '?type=freq', 'active' => ($type === 'freq')],
    ['label' => 'Preferred Learning Style', 'url' => '?type=style', 'active' => ($type === 'style')]
];

echo $OUTPUT->header();

// 5. RENDER TEMPLATE
echo $OUTPUT->render_from_template('local_chartplugin/analytics_page', [
    'chart_title'        => ($type === 'default') ? 'Synopsis (to date)' : ucfirst($type),
    'synopsischart'      => $rendered_main,
    'last_chart_html'    => $rendered_last,
    'last_chart_title'   => ($last_type === 'default') ? 'Synopsis (to date)' : ucfirst($last_type),
    'prev_chart_html'    => $rendered_prev,
    'prev_chart_title'   => ($prev_type === 'default') ? 'Synopsis (to date)' : ucfirst($prev_type),
    'ai_context'         => $ai_payload,
    'buttons'            => $buttons
]);

echo $OUTPUT->footer();