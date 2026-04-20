<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/recovery.php
 * Final Day 9 Polish: Admin Testing + Learning Dashboard Branding
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $DB, $USER, $PAGE, $OUTPUT;

require_login();
$PAGE->set_url(new moodle_url('/local/chartplugin/recovery.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Boost Performance");

echo $OUTPUT->header();

// 1. Fetch latest record safely
$records = $DB->get_records('local_chartplugin_payments', ['userid' => $USER->id], 'id DESC', '*', 0, 1);
$record = reset($records);

// 2. Auto-initialize record if missing
if (!$record) {
    $record = new stdClass();
    $record->userid = $USER->id;
    $record->credits = 3; 
    $record->status = 'active';
    $record->valid_until = 0;
    $record->id = $DB->insert_record('local_chartplugin_payments', $record);
}

$return_url = new moodle_url('/local/chartplugin/index.php');

// 3. Admin Logic Bypass
// Admins are allowed to proceed even if they have Enterprise status to test deductions.
$status = local_chartplugin_get_access_status();
if ($status === 'enterprise' && !is_siteadmin()) {
    echo $OUTPUT->notification("You already have active Enterprise access.", 'notifynotice');
    echo $OUTPUT->continue_button($return_url);
    echo $OUTPUT->footer();
    exit;
}

// 4. Credit Deduction Logic
if ($record->credits > 0) {
    $record->credits -= 1;
    $record->valid_until = time() + (24 * 60 * 60); 
    $DB->update_record('local_chartplugin_payments', $record);
    
    $message = "<strong>Success:</strong> 1 credit deducted. " .
               "Enterprise access granted for 24 hours. " .
               "<a href='{$return_url}' class='btn btn-dark btn-sm ml-2'>Return to Learning Dashboard</a>";

    echo $OUTPUT->notification($message, 'notifysuccess');
} else {
    // Insufficient credits UI
    echo $OUTPUT->notification("Insufficient credits to activate boost.", 'notifyproblem');
    echo '<div class="mt-3"><a href="index.php" class="btn btn-secondary">Back to Learning Dashboard</a></div>';
}

echo $OUTPUT->footer();