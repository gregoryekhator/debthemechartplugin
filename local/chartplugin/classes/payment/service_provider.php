<?php
/**
 * Path: /local/chartplugin/classes/payment/service_provider.php
 * Purpose: Fulfills all Moodle 4.5 Interface requirements
 */

namespace local_chartplugin\payment;

defined('MOODLE_INTERNAL') || die();

use core_payment\local\callback\service_provider as service_provider_interface;
use core_payment\local\entities\payable;
use moodle_url;

class service_provider implements service_provider_interface {

    /**
     * REQUIRED: Tells Moodle the Cost, Currency, and Account ID.
     */
    public static function get_payable(string $paymentarea, int $itemid): payable {
        // Find the price based on the itemid (credits)
        $prices = [
            10  => 5.00,
            50  => 20.00,
            100 => 35.00
        ];
        
        $amount = $prices[$itemid] ?? 0.00;
        $accountid = 2; // Your "Moodle Chart Plugin" account ID

        return new payable($amount, 'USD', $accountid);
    }

    /**
     * REQUIRED: Where to send the user after a successful purchase.
     */
    public static function get_success_url(string $paymentarea, int $itemid): moodle_url {
        // Send them back to the Learning Dashboard
        return new moodle_url('/local/chartplugin/index.php', ['status' => 'success']);
    }

    /**
     * REQUIRED: What to do when the money is confirmed.
     */
    public static function deliver_order($paymentid, $userid, $amount, $currency, $itemid) {
    global $DB;

    // 1. Update the User's live balance in the main users table
    $user_record = $DB->get_record('local_chartplugin_users', ['userid' => $userid]);
    if ($user_record) {
        // Increment credits based on the item purchased
        $user_record->credits += 10; 
        $DB->update_record('local_chartplugin_users', $user_record);
    }

    // 2. Create the 'Receipt' record for the History view
    $history = new \stdClass();
    $history->userid = $userid;
    $history->paymentid = $paymentid;
    $history->amount = $amount;
    $history->currency = $currency;
    $history->itemid = $itemid;
    $history->status = 'completed'; // Validates the purchase in history.php
    $history->timecreated = time();
    
    return $DB->insert_record('local_chartplugin_payments', $history);
}