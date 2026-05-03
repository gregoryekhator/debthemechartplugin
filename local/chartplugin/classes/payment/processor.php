<?php
namespace local_chartplugin\payment;

defined('MOODLE_INTERNAL') || die();

class processor {
    /**
     * The final logic for adding credits to the database.
     */
    public static function deliver_order_final(string $paymentarea, int $itemid, int $paymentid, int $userid): bool {
        global $DB;

        // Fetch the payment record to get the amount/currency logged by core
        $payment = $DB->get_record('payments', ['id' => $paymentid]);

        // 1. Update the BALANCE (Current credits)
        $record = new \stdClass();
        $record->userid = $userid;
        $record->credits = $itemid;
        $record->amount = $payment ? $payment->amount : 0.00;
        $record->currency = $payment ? $payment->currency : 'GBP';
        $record->status = 'active';
        $record->transactionid = $paymentid;
        $record->timecreated = time();
        $DB->insert_record('local_chartplugin_payments', $record);

        // 2. NEW: Write to the HISTORY (Receipt log)
        $history = new \stdClass();
        $history->userid = $userid;
        $history->itemid = $itemid; // e.g. 10
        $history->amount = $payment ? $payment->amount : 0.00;
        $history->currency = $payment ? $payment->currency : 'GBP';
        $history->paymentid = $paymentid;
        $history->type = 'purchase';
        $history->timecreated = time();
        $DB->insert_record('local_chartplugin_history', $history);
        
        return true;
    }
}