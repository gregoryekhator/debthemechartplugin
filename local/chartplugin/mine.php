<?php
/**
 * mine.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Path: /local/chartplugin/mine.php
 */
require_once(__DIR__ . '/../../config.php');
// No require_once needed for classes in the classes folder!
require_login();

$userid = $USER->id;

// Moodle Autoloader will find this class automatically
try {
    $file_path = \local_chartplugin\analytics\miner::export_to_csv($userid);
    echo "<h3>ML Data Mining Complete</h3>";
    echo "File generated for User ID: " . $userid . "<br>";
    echo "Location: " . $file_path . "<br><br>";
    echo "Next step: Pass this CSV to the Python model.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}