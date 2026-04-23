<?php
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
// REPLACEMENT: Changed "Flight Deck" to "Learning Dashboard"
$PAGE->set_title("Top Up: Learning Credits");
$PAGE->set_heading("Boost Your Learning Dashboard");

// IMPORTANT: This JS requirement is what makes the modal work.
$PAGE->requires->js_call_amd('core_payment/gateways_modal', 'init');

echo $OUTPUT->header();

$packages = [
    ['amount' => 10, 'price' => '5.00', 'name' => 'Starter Pack'],
    ['amount' => 50, 'price' => '20.00', 'name' => 'Pro Booster'],
    ['amount' => 100, 'price' => '35.00', 'name' => 'Enterprise Fuel']
];

echo '<div class="row justify-content-center mt-4">';
foreach ($packages as $pkg) {
    // FIX: Generate parameters for the Modal instead of a direct URL
    $params = \core_payment\helper::gateways_modal_link_params(
        'local_chartplugin', 
        'credits', 
        $pkg['amount'], 
        "Purchase {$pkg['amount']} Learning Credits"
    );

    // Convert array params into HTML attributes
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
                    <button class='btn btn-primary btn-lg btn-block shadow-sm' {$attributes}>
                        <i class='fa fa-paypal'></i> Buy Now
                    </button>
                </div>
            </div>
        </div>
    </div>";
}
echo '</div>';

echo "<div class='text-center mt-5'><a href='index.php' class='btn btn-outline-secondary'><i class='fa fa-arrow-left'></i> Back to Dashboard</a></div>";
echo $OUTPUT->footer();