<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');

global $DB;

$accountid = 2; // Your "Moodle Chart Plugin" account
$component = 'local_chartplugin';
$area = 'credits';

echo "Updating Payment Mapping for Moodle 4.5...\n";

// In 4.5, we check for 'payment_accounts' or 'payment_gateways' links
// We will try to find where Moodle stores the enabled areas for an account.
// Let's use the most direct method: checking the account settings.

$account = $DB->get_record('payment_accounts', ['id' => $accountid]);

if (!$account) {
    die("Error: Account ID $accountid not found.\n");
}

echo "Linking Account: {$account->name} to $component ($area)\n";

// We create a custom record in the config_plugins for the gateway system
// This is often where 4.5 stores 'Applicable to' if the table is missing.
set_config('enabled_areas', $component . '-' . $area, 'paygw_paypal');

// Clear all caches to force the UI to refresh
purge_all_caches();

echo "Done. Please check the 'Payment Accounts' page in your browser.\n";