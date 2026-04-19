<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/recovery.php
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $DB, $USER, $PAGE, $OUTPUT;

require_login();
$PAGE->set_url(new moodle_url('/local/chartplugin/recovery.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Performance Activation");

echo $OUTPUT->header();

// 1. Fetch record or create one for testing.
$record = $DB->get_record('local_chartplugin_payments', ['userid' => $USER->id]);

if (!$record) {
    $record = new stdClass();
    $record->userid = $USER->id;
    $record->credits = 3; // Giving you 3 free credits for testing!
    $record->status = 'active';
    $record->id = $DB->insert_record('local_chartplugin_payments', $record);
}

// 2. Logic Check.
if ($record->credits > 0) {
    // Burn 1.
    $record->credits--;
    $DB->update_record('local_chartplugin_payments', $record);
    
    // Upgrade.
    set_user_preference('local_chartplugin_license', 'enterprise', $USER->id);
    
    // Success UI.
    echo $OUTPUT->notification("Performance Boost Activated! 1 Credit Used.", 'notifysuccess');
    echo '<div class="text-center mt-5">
            <h2 class="font-weight-bold">Optimizing your Flight Deck...</h2>
            <div class="spinner-border text-primary mt-3" role="status"></div>
            <script>setTimeout(function(){ window.location.href = "index.php"; }, 2000);</script>
          </div>';
} else {
    echo $OUTPUT->notification("Insufficient Credits for a Performance Boost.", 'notifyproblem');
    echo '<div class="text-center mt-4"><a href="index.php" class="btn btn-primary rounded-pill">Buy More Credits</a></div>';
}

echo $OUTPUT->footer();