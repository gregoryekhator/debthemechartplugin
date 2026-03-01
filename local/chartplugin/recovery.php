<?php
/**
 * Path: /local/chartplugin/recovery.php
 */
require_once(__DIR__ . '/../../config.php');
require_login();

$confirm = optional_param('confirm', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/chartplugin/recovery.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("AI Recovery Plan");

// LOGIC: If user clicked "Pay Now", save to DB
if ($confirm && data_submitted()) {
    $record = new stdClass();
    $record->userid = $USER->id;
    $record->amount = 25.00;
    $record->currency = 'USD';
    $record->status = 'completed';
    $record->transactionid = 'MOCK_' . time();
    $record->timecreated = time();

    $DB->insert_record('local_chartplugin_payments', $record);
    
    // Redirect back to dashboard
    redirect(new moodle_url('/local/chartplugin/index.php', ['status' => 'paid_success']));
}

echo $OUTPUT->header();
echo $OUTPUT->heading("Activate Your AI Recovery Plan");

echo '<div class="card p-4 shadow-sm border-0" style="max-width: 600px; margin: auto; border-radius: 15px; background: #fff;">';
echo '    <p class="lead text-center">Your personalized AI-driven support path.</p>';
echo '    <hr>';

// Mock Form
echo '<form action="'.$PAGE->url.'" method="post">';
echo '    <input type="hidden" name="confirm" value="1">';
echo '    <button type="submit" class="btn btn-success btn-lg btn-block shadow-sm" style="border-radius: 25px;">';
echo '       Pay $25.00 Now (Simulated)';
echo '    </button>';
echo '</form>';

echo '</div>';
echo $OUTPUT->footer();