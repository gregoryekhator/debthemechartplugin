<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/audit_request.php
 */
require_once(__DIR__ . '/../../config.php');
global $PAGE, $OUTPUT, $USER;
require_login();

$PAGE->set_url(new moodle_url('/local/chartplugin/audit_request.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Bespoke Cognitive Audit");

echo $OUTPUT->header();
?>

<div class="container mt-5" style="max-width: 600px;">
    <div id="conversational-form" class="card shadow-lg border-0 p-5 text-center" style="border-radius: 30px; min-height: 400px; display: flex; align-items: center; justify-content: center;">
        
        <div class="step" id="step-1">
            <h2 class="font-weight-bold mb-4">First, which course are you aiming to master?</h2>
            <input type="text" id="course_name" class="form-control form-control-lg rounded-pill border-primary text-center" placeholder="e.g. Advanced Mathematics">
            <button class="btn btn-primary btn-lg rounded-pill mt-4 px-5" onclick="nextStep(2)">Next <i class="fa fa-arrow-right"></i></button>
        </div>

        <div class="step d-none" id="step-2">
            <h2 class="font-weight-bold mb-4">What is your dream score?</h2>
            <div class="btn-group-vertical w-100">
                <button class="btn btn-outline-primary mb-2 rounded-pill" onclick="setScore('90%+')">A* (90%+)</button>
                <button class="btn btn-outline-primary mb-2 rounded-pill" onclick="setScore('80%')">A (80%)</button>
                <button class="btn btn-outline-primary mb-2 rounded-pill" onclick="setScore('70%')">B (70%)</button>
            </div>
        </div>

        <div class="step d-none" id="step-3">
            <h2 class="font-weight-bold mb-4">Almost there! Your contact email?</h2>
            <input type="email" id="user_email" class="form-control form-control-lg rounded-pill text-center" value="<?php echo $USER->email; ?>">
            <button class="btn btn-success btn-lg rounded-pill mt-4 px-5" onclick="finish()">Submit Audit Request</button>
        </div>

        <div class="step d-none" id="step-success">
            <div class="text-success mb-4"><i class="fa fa-check-circle fa-5x"></i></div>
            <h2 class="font-weight-bold">Request Sent!</h2>
            <p class="text-muted">Our AI Analysts will review your telemetry and contact you within 24 hours.</p>
            <a href="index.php" class="btn btn-primary rounded-pill mt-3">Back to Flight Deck</a>
        </div>

    </div>
</div>

<script>
let formData = {};

function nextStep(step) {
    document.querySelectorAll('.step').forEach(s => s.classList.add('d-none'));
    document.getElementById('step-' + step).classList.remove('d-none');
}

function setScore(score) {
    formData.score = score;
    nextStep(3);
}

function finish() {
    // Here you would normally do an AJAX call to save the data.
    nextStep('success');
}
</script>

<?php echo $OUTPUT->footer(); ?>