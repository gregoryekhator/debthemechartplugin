<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/chartplugin/lib.php');

$userid = 2; // Your test user
$courseid = 2;

echo "Phase 2b: Migrating slow data to high-speed analytics table...\n";

// 1. Get the data that was slowing down the index page
$cohort_avg = local_chartplugin_get_cohort_average($courseid);
$trend_json = get_config('local_chartplugin', 'user_trend_snapshot_' . $userid);

// 2. Prepare the record for the new table
$record = new stdClass();
$record->userid = $userid;
$record->courseid = $courseid;
$record->monthly_trend = $trend_json;
$record->cohort_avg = $cohort_avg;
$record->timemodified = time();

// 3. Insert or Update (Upsert)
if ($existing = $DB->get_record('local_chartplugin_trends', ['userid' => $userid, 'courseid' => $courseid])) {
    $record->id = $existing->id;
    $DB->update_record('local_chartplugin_trends', $record);
    echo "Record updated for User $userid.\n";
} else {
    $DB->insert_record('local_chartplugin_trends', $record);
    echo "New record created for User $userid.\n";
}
