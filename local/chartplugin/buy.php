<?php
/**
 * buy.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Path: /local/chartplugin/buy.php
 * Purpose: Professional Credit Storefront (Fixed for Moodle 4.5 Modal)
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $PAGE, $OUTPUT, $USER, $DB;

require_login();

$PAGE->set_url(new moodle_url('/local/chartplugin/buy.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Top Up: Learning Credits");
$PAGE->set_heading("Boost Your Learning Dashboard");

$PAGE->requires->js_call_amd('core_payment/gateways_modal', 'init');

echo $OUTPUT->header();

$packages = [
    ['amount' => 10, 'price' => '5.00', 'name' => 'Starter Pack'],
    ['amount' => 50, 'price' => '20.00', 'name' => 'Pro Booster'],
    ['amount' => 100, 'price' => '35.00', 'name' => 'Enterprise Fuel']
];

echo '<div class="row justify-content-center mt-4">';
foreach ($packages as $pkg) {
    // This is the 4-parameter call that matches the 'service_provider_interface' implementation
    $params = \core_payment\helper::gateways_modal_link_params(
        'local_chartplugin', 
        'credits', 
        $pkg['amount'], 
        "Purchase {$pkg['amount']} Learning Credits"
    );

    $attributes = '';
    foreach ($params as $name => $value) {
        $attributes .= ' ' . $name . '="' . s($value) . '"';
    }

    echo "
    <div class='col-md-4 mb-4'>
        <div class='card shadow border-primary h-100'>
            <div class='card-body text-center d-flex flex-column'>
                <h4 class='card-title font-weight-bold'>{$pkg['name']}</h4>
                <h1 class='display-4 text-primary my-3'>{$pkg['amount']}</h1>
                <p class='text-muted mb-4'>Credits for Analytics</p>
                <div class='mt-auto'>
                    <h2 class='mb-3'>\${$pkg['price']}</h2>
                    <button class='btn btn-primary btn-lg btn-block' {$attributes}>
                        <i class='fa fa-credit-card'></i> Buy Now
                    </button>
                </div>
            </div>
        </div>
    </div>";
}
echo '</div>';
echo $OUTPUT->footer();