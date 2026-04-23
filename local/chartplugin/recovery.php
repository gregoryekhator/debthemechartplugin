<?php
/**
 * Path: /local/chartplugin/recovery.php
 * Purpose: Spend credits or redirect to store
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $DB, $USER, $PAGE, $OUTPUT;

require_login();
$PAGE->set_url(new moodle_url('/local/chartplugin/recovery.php'));
$PAGE->set_context(context_system::instance());

// 1. Check current balance
$record = $DB->get_record('local_chartplugin_payments', ['userid' => $USER->id]);
$current_credits = $record ? $record->credits : 0;

echo $OUTPUT->header();

if ($current_credits <= 0) {
    // THIS IS THE FIX: Redirect the user to the store if they have 0 credits
    echo $OUTPUT->heading("Out of Credits", 3);
    echo '<div class="alert alert-warning shadow-sm mt-4 text-center">';
    echo '<p class="mb-3">You need at least 1 credit to boost performance telemetry.</p>';
    echo '<a href="buy.php" class="btn btn-primary btn-lg shadow">
            <i class="fa fa-shopping-cart"></i> Go to Credit Store
          </a>';
    echo '</div>';
} else {
    // Logic for users who HAVE credits
    echo $OUTPUT->heading("Boost Performance", 3);
    echo '<div class="card shadow-sm mx-auto" style="max-width: 500px;">';
    echo '<div class="card-body text-center">';
    echo '<p>Spend 1 credit to refresh your AI performance delta?</p>';
    echo '<form method="POST" action="process_boost.php">';
    echo '<button type="submit" class="btn btn-success btn-lg">Confirm Boost (-1 Credit)</button>';
    echo '</form>';
    echo '</div></div>';
}

echo '<div class="text-center mt-3"><a href="index.php">Return to Dashboard</a></div>';
echo $OUTPUT->footer();