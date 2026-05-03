<?php
/**
 * Path: /local/chartplugin/recovery.php
 */
require_once(__DIR__ . '/../../config.php');
require_login();

$PAGE->set_url(new moodle_url('/local/chartplugin/recovery.php'));
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title("Recovery Plan");
$PAGE->set_heading("Your Personalized AI Recovery Plan");

echo $OUTPUT->header();

// 1. Get the LATEST plan for this user (Sorted by ID descending)
// Using 'competency_plan' as confirmed by your DB SHOW TABLES
$plans = $DB->get_records('competency_plan', ['userid' => $USER->id], 'id DESC', '*', 0, 1);
$plan = reset($plans); 

if ($plan) {
    echo $OUTPUT->box_start('generalbox mt-3');
    echo "<h3>Plan: {$plan->name}</h3>";
    echo "<p>{$plan->description}</p>";
    
    // Direct link to Moodle's native learning plan view
    $lp_url = new moodle_url('/admin/tool/lp/plan.php', ['id' => $plan->id]);
    echo html_writer::link($lp_url, "Open Official Learning Plan", ['class' => 'btn btn-primary']);
    echo $OUTPUT->box_end();
} else {
    // If no plan exists, trigger the prescription logic
    require_once(__DIR__ . '/lib.php');
    $newplan = local_chartplugin_prescribe_learning_plan($USER->id);
    
    if ($newplan) {
        // Redirect to the newly created plan ID specifically
        $new_url = new moodle_url('/admin/tool/lp/plan.php', ['id' => $newplan->get('id')]);
        redirect($new_url, "AI has generated a new recovery plan for you!", 2);
    } else {
        echo $OUTPUT->notification("No recovery plan required at this time.", 'info');
    }
}

echo html_writer::link(new moodle_url('/local/chartplugin/index.php'), "Return to Analytics", ['class' => 'btn btn-secondary mt-3']);
echo $OUTPUT->footer();