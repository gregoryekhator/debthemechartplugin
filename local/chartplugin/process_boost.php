<?php
/**
 * process_boost.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/chartplugin/lib.php');

require_login();
require_sesskey(); // Security check

$context = context_system::instance();
$PAGE->set_context($context);

$access = local_chartplugin_get_access_status();
$is_admin = has_capability('moodle/site:config', context_system::instance());

// 1. Admin/Site Config holders get a free pass
if ($is_admin) {
    redirect(new moodle_url('/local/chartplugin/index.php'), "Admin Bypass: Optimization complete (No credits used).", 2);
}

if ($access === 'freemium') {
    redirect(new moodle_url('/local/chartplugin/buy.php'), 'Subscription required.', 3);
}

// 2. Deduction Logic for regular users
global $DB, $USER;
$records = $DB->get_records('local_chartplugin_payments', ['userid' => $USER->id], 'id DESC', '*', 0, 1);
$record = reset($records);

// Only deduct if they aren't on an active time-based enterprise sub
$is_time_enterprise = ($record && !empty($record->valid_until) && $record->valid_until > time());

if (!$is_time_enterprise && $record && $record->credits > 0) {
    $record->credits -= 1;
    $DB->update_record('local_chartplugin_payments', $record);
    $message = "Optimization complete! 1 credit deducted.";
} else {
    $message = "Enterprise Optimization Active!";
}

redirect(new moodle_url('/local/chartplugin/index.php'), $message, 2);