<?php
/**
 * service_provider.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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

        return new payable(
            (string)$amount,
            'USD',
            "Purchase {$itemid} Learning Credits",
            $itemid
        );
    }

    /**
     * REQUIRED: Delivers the credits after successful payment.
     */
    public static function deliver_order(string $paymentarea, int $itemid, int $paymentid, int $userid): bool {
        global $DB;

        if ($paymentarea !== 'credits') {
            return false;
        }

        $userfield = $DB->get_record('user_info_field', ['shortname' => 'credits']);
        if (!$userfield) {
            return false;
        }

        $current = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $userfield->id]);
        $newbalance = ($current ? (int)$current->data : 0) + (int)$itemid;

        if ($current) {
            $DB->set_field('user_info_data', 'data', (string)$newbalance, ['id' => $current->id]);
        } else {
            $DB->insert_record('user_info_data', [
                'userid' => $userid, 
                'fieldid' => $userfield->id, 
                'data' => (string)$newbalance
            ]);
        }

        return true;
    }
}