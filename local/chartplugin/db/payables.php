<?php
/**
 * payables.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

defined('MOODLE_INTERNAL') || die();

$payables = [
    'credits' => [
        'component' => 'local_chartplugin',
        'callback'  => '\local_chartplugin_payment_callback', // Added leading backslash
    ]
];