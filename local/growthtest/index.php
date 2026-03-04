<?php
require_once(__DIR__ . '/../../config.php');
require_login();

$PAGE->set_url(new moodle_url('/local/growthtest/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report');

echo $OUTPUT->header();

try {
    // Force Moodle to grab the 'global' renderer we just simplified
    $renderer = $PAGE->get_renderer('local_growthtest', 'global');
    echo $renderer->display_growth_chart(); 
} catch (Exception $e) {
    echo "<h3>System still blocked</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo $OUTPUT->footer();