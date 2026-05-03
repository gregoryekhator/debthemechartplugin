<?php
/**
 * Path: /local/chartplugin/process_boost.php
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php'); // Ensure the prescription function is loaded
require_login();

$sesskey = optional_param('sesskey', '', PARAM_ALPHANUM);
if (!confirm_sesskey($sesskey)) {
    print_error('invalidsesskey', 'error');
}

// 1. Get the latest payment record for the user
$payments = $DB->get_records('local_chartplugin_payments', ['userid' => $USER->id], 'id DESC', '*', 0, 1);
$record = reset($payments);

if ($record && $record->credits > 0) {
    // A. Deduct the credit
    $record->credits -= 1;
    $DB->update_record('local_chartplugin_payments', $record);

    // B. Create the History Record (Locale/Currency Aware)
    $history = new \stdClass();
    $history->userid = $USER->id;
    $history->itemid = 1; 
    $history->type = 'spent';
    $history->amount = 0.00;
    // Fallback to GBP if no plugin setting is found
    $currency = get_config('local_chartplugin', 'currency') ?: 'GBP';
    $history->currency = $currency;
    $history->paymentid = $record->id;
    $history->timecreated = time();
    $DB->insert_record('local_chartplugin_history', $history);

    // C. Log the Event
    \local_chartplugin\event\credit_burned::create([
        'context' => \context_user::instance($USER->id),
        'userid' => $USER->id
    ])->trigger();

    // D. TRIGGER THE COMPETENCY ENGINE
    try {
        // This calls the updated function in lib.php
        $newplan = local_chartplugin_prescribe_learning_plan($USER->id);
        
        // Redirect to the SPECIFIC ID of the plan just created (Fixes "0 of 0" view)
        $url = new moodle_url('/admin/tool/lp/plan.php', ['id' => $newplan->get('id')]);
        redirect($url, "New recovery plan generated!", 2);

    } catch (Exception $e) {
        // Log the error if the API fails
        debugging('Competency Error: ' . $e->getMessage());
        redirect(new moodle_url('/local/chartplugin/index.php'), "Plan creation failed: " . $e->getMessage(), 5);
    }

} else {
    // Handle case where user has 0 credits
    redirect(new moodle_url('/local/chartplugin/index.php'), "Insufficient credits to boost performance.", 5, \core\output\notification::NOTIFY_ERROR);
} // <--- This was the missing brace causing the line 18 area error