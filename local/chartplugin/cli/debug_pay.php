<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');

$areas = \core_payment\helper::get_payable_areas();

echo "--- Moodle 4.5 Registered Payment Areas ---\n";
foreach ($areas as $areaid => $details) {
    echo "ID: $areaid | Component: {$details['component']} | Area: {$details['paymentarea']}\n";
}

if (!array_key_exists('local_chartplugin-credits', $areas)) {
    echo "\n[ERROR] local_chartplugin-credits is NOT registered.\n";
} else {
    echo "\n[SUCCESS] Area is found! The issue is purely account mapping.\n";
}