<?php
/**
 * payment.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

defined('MOODLE_INTERNAL') || die();

$areas = [
    'credits' => [
        'component' => 'local_chartplugin',
        'paymentarea' => 'credits',
        'callback' => '\local_chartplugin\payment\processor::process_payment',
    ],
];