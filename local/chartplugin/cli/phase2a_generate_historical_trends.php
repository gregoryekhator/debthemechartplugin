<?php
/**
 * phase2a_generate_historical_trends.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');

// Objective: Populate a mock dataset for User 2 showing a 6-month trend.
$userid = 2; 
$months = ['Sept', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb'];
$scores = [78, 82, 85, 80, 72, 65]; // Notice the recent dip for our "Alert" logic

echo "Phase 2a: Generating Longitudinal Data for User $userid...\n";

// For now, we will store this in a temporary Moodle config variable 
// to avoid creating a new DB table until we are ready for the ML Model.
$trend_data = array_combine($months, $scores);
set_config('user_trend_snapshot_' . $userid, json_encode($trend_data), 'local_chartplugin');

echo "Success: 6-month performance trend stored in system config.\n";
echo "Ready to render Line Chart in Sidebar.\n";