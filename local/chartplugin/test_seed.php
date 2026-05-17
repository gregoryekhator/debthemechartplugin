<?php
/**
 * test_seed.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/datalib.php');

$categoryid = 16; 
// Call your miner
$lessons = \local_chartplugin\analytics\miner::mine_lesson_content($categoryid);

echo "--- START MINING DATA ---\n";
foreach ($lessons as $lesson) {
    // DO NOT use strip_tags yet. Let Python handle the raw string 
    // or use html_to_text to keep formatting markers.
    $raw_html = $lesson->questiontext;
    
    // Clean only for visual logging, but pass the markers clearly
    echo "Found: " . $lesson->name . "\n";
    echo "Content: " . htmlspecialchars_decode($raw_html) . "\n";
    echo "--- END ITEM ---\n";
}