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

        $record = new \stdClass();
        $record->userid = $userid;
        $record->credits = $itemid; // Our package amount (10, 50, etc)
        $record->amount = $payment ? $payment->amount : 0.00;
        $record->currency = $payment ? $payment->currency : 'USD';
        $record->status = 'active';
        $record->transactionid = $paymentid;
        $record->timecreated = time();
        
        return (bool)$DB->insert_record('local_chartplugin_payments', $record);
    }
}