<?php
/**
 * setup_test_lp.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../../config.php'); // Note the extra ../ for cli folder

global $DB, $USER;

$USER = $DB->get_record('user', ['id' => 2]); // Force User 2 context

$existing = $DB->get_record('competency_plan', ['userid' => 2]);

if (!$existing) {
    $plan = new \stdClass();
    $plan->name = 'AI Recovery Plan (Test)';
    $plan->userid = 2;
    $plan->description = 'Static test plan for development.';
    $plan->status = 1; // Active
    $plan->timecreated = time();
    $plan->timemodified = time();
    $plan->usermodified = 2;
    
    $planid = $DB->insert_record('competency_plan', $plan);
    echo "Test Plan Created! ID: " . $planid . "\n";
} else {
    echo "Plan already exists for User 2. ID: " . $existing->id . "\n";
}