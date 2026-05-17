<?php
/**
 * receipt.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Path: /local/chartplugin/receipt.php
 */
require_once(__DIR__ . '/../../config.php');

global $DB, $USER, $OUTPUT, $PAGE;

$id = required_param('id', PARAM_INT);
require_login();

$record = $DB->get_record('local_chartplugin_history', ['id' => $id, 'userid' => $USER->id], '*', MUST_EXIST);

$PAGE->set_url(new moodle_url('/local/chartplugin/receipt.php', ['id' => $id]));
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title("Transaction Receipt");
$PAGE->set_pagelayout('embedded'); // Minimalist layout for printing

echo $OUTPUT->header();
?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h4 class="mb-0">Official Receipt</h4>
            <span># echo $record->id; ?></span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="mb-3">From:</h6>
                    <div><strong>Debonair Training Ltd</strong></div>
                    <div>info@debonairtraining.com</div>
                    <div>www.debonairtraining.com</div>
                </div>
                <div class="col-sm-6">
                    <h6 class="mb-3">To:</h6>
                    <div><strong> echo fullname($USER); ?></strong></div>
                    <div> echo $USER->email; ?></div>
                </div>
            </div>

            <div class="table-responsive-sm">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="right">Type</th>
                            <th class="center">Date</th>
                            <th class="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Learning Credits / AI Performance Boost</td>
                            <td class="right"> echo ucfirst($record->type); ?></td>
                            <td class="center"> echo userdate($record->timecreated); ?></td>
                            <td class="right"><strong> echo $record->amount . ' ' . $record->currency; ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-4">
                <button onclick="window.print();" class="btn btn-secondary btn-sm">
                    <i class="fa fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>


echo $OUTPUT->footer();