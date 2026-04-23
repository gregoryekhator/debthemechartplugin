<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');

global $DB;

echo "--- Moodle Payment Area Verification ---\n";

// Check if core recognizes the payable area
$payables = \core_payment\helper::get_payable_areas();
$found = false;

foreach ($payables as $id => $payable) {
    if ($payable['component'] === 'local_chartplugin') {
        echo "[FOUND] Area ID: $id | Component: {$payable['component']} | Callback: {$payable['callback']}\n";
        $found = true;
    }
}

if (!$found) {
    echo "[ERROR] No payable areas found for 'local_chartplugin'.\n";
    echo "Check if db/payables.php is correctly formatted.\n";
}

echo "----------------------------------------\n";