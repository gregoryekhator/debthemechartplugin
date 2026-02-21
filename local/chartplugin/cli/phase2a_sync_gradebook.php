<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/gradelib.php');

// Force-clear the component cache at the code level
core_php_time_limit::raise(600);
gc_collect_cycles();

$courseid = 2;
echo "Synchronizing Gradebook API for Users 1-10...\n";

$grade_item = grade_item::fetch(array('courseid' => $courseid, 'itemtype' => 'course'));

if ($grade_item) {
    for ($i = 1; $i <= 10; $i++) {
        // We set User 1 to 65% and randomize others to create an average
        $score = ($i === 1) ? 65.00 : rand(75, 95);
        $grade_item->update_final_grade($i, $score);
        echo "User $i: Synced to $score%\n";
    }
    // Final cache purge to ensure the browser sees the changes
    purge_all_caches();
    echo "Synchrony Complete. All caches purged.\n";
} else {
    echo "CRITICAL: Grade Item for Course $courseid not found.\n";
}
