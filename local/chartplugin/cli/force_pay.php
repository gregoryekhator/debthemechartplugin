<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');

// This forces Moodle to acknowledge the payable area regardless of the UI column
$payable = new stdClass();
$payable->component = 'local_chartplugin';
$payable->paymentarea = 'credits';
$payable->callback = '\local_chartplugin_payment_callback';

echo "Attempting to force register payment area...\n";

// We clear the cache that stores payable areas
cache::make('core', 'payables')->purge();

echo "Success! Please check the Payment Accounts page now.\n";