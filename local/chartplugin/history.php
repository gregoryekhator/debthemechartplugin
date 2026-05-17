<?php
/**
 * history.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Path: /local/chartplugin/history.php
 */
require_once(__DIR__ . '/../../config.php');
require_login();

$PAGE->set_url(new moodle_url('/local/chartplugin/history.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Payment History");
$PAGE->set_heading("Your Transaction History");

echo $OUTPUT->header();

$history = $DB->get_records('local_chartplugin_history', ['userid' => $USER->id], 'timecreated DESC');

if (!$history) {
    echo $OUTPUT->notification("No transactions found.", 'info');
} else {
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead><tbody>";
    
    foreach ($history as $row) {
        $date = userdate($row->timecreated);
        $receipturl = new moodle_url('/local/chartplugin/receipt.php', ['id' => $row->id]);
        
        // Logical Labeling
        $item_label = ($row->type === 'spent') ? "AI Performance Boost" : "Learning Credit Purchase";
        $amount_display = ($row->type === 'spent') ? "-1 Credit" : "{$row->currency} {$row->amount}";
        $status_badge = ($row->type === 'spent') ? "badge-info" : "badge-success";

        echo "<tr>
                <td>{$date}</td>
                <td><strong>{$item_label}</strong></td>
                <td>{$amount_display}</td>
                <td><span class='badge {$status_badge}'>" . ucfirst($row->type) . "</span></td>
                <td><a href='{$receipturl}' class='btn btn-sm btn-primary'><i class='fa fa-file-text-o'></i> View Receipt</a></td>
              </tr>";
    }
    echo "</tbody></table>";
}

echo html_writer::link(new moodle_url('/local/chartplugin/index.php'), "Back to Dashboard", ['class' => 'btn btn-secondary']);
echo $OUTPUT->footer();