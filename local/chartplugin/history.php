    <?php
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
    echo "<table class='table'>";
    echo "<thead><tr><th>Date</th><th>Item</th><th>Amount</th><th>Status</th></tr></thead><tbody>";
    foreach ($history as $row) {
        $date = userdate($row->timecreated);
        echo "<tr>
                <td>{$date}</td>
                <td>{$row->itemid} Credits</td>
                <td>{$row->currency} {$row->amount}</td>
                <td><span class='badge badge-success'>Completed</span></td>
              </tr>";
    }
    echo "</tbody></table>";
}

echo html_writer::link(new moodle_url('/local/chartplugin/index.php'), "Back to Dashboard", ['class' => 'btn btn-secondary']);
echo $OUTPUT->footer();