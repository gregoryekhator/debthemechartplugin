<?php
/**
 * create_diagnostic_quiz.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// CLI only.
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');

// Required Moodle libs.
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/modinfolib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php'); // <-- CRITICAL

global $DB, $USER;

// ----------------------------
// CONFIG
// ----------------------------
$courseid = 2;
$quizname = 'Diagnostic Quiz';

// ----------------------------
// SAFETY CHECKS
// ----------------------------
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// Ensure admin context.
$USER = get_admin();

// Do not recreate if quiz already exists.
if ($DB->record_exists('quiz', ['course' => $courseid, 'name' => $quizname])) {
    mtrace("Quiz '{$quizname}' already exists. Skipping.");
    exit(0);
}

// ----------------------------
// DETERMINE TARGET SECTION
// ----------------------------
$modinfo = get_fast_modinfo($course);
$sections = $modinfo->get_section_info_all();

$sectionnum = 0; // Default to top section.
foreach ($sections as $section) {
    if ($section->visible) {
        $sectionnum = $section->section;
        break;
    }
}

// ----------------------------
// MODULE DATA
// ----------------------------
$moduleinfo = new stdClass();

$moduleinfo->modulename = 'quiz';
$moduleinfo->module     = $DB->get_field('modules', 'id', ['name' => 'quiz'], MUST_EXIST);
$moduleinfo->course     = $courseid;
$moduleinfo->section    = $sectionnum;
$moduleinfo->visible    = 1;

// Core fields.
$moduleinfo->name        = $quizname;
$moduleinfo->intro       = 'Baseline diagnostic quiz for analytics testing.';
$moduleinfo->introformat = FORMAT_HTML;

// Quiz behaviour.
$moduleinfo->preferredbehaviour = 'deferredfeedback';
$moduleinfo->attempts           = 2;
$moduleinfo->grademethod        = QUIZ_GRADEHIGHEST;
$moduleinfo->grade              = 100;
$moduleinfo->sumgrades          = 100;

// Timing.
$moduleinfo->timeopen   = 0;
$moduleinfo->timeclose  = 0;
$moduleinfo->timelimit  = 0;

// Required in Moodle 4.5 (NOT NULL).
$moduleinfo->password = '';

// Review options — use numeric masks explicitly (stable & safe).
$moduleinfo->reviewattempt          = 0x10000;
$moduleinfo->reviewcorrectness      = 0x10000;
$moduleinfo->reviewmarks            = 0x10000;
$moduleinfo->reviewspecificfeedback = 0x10000;
$moduleinfo->reviewgeneralfeedback  = 0x10000;
$moduleinfo->reviewrightanswer      = 0x10000;
$moduleinfo->reviewoverallfeedback  = 0x10000;

// Completion explicitly disabled.
$moduleinfo->completion = COMPLETION_TRACKING_NONE;

// ----------------------------
// CREATE MODULE
// ----------------------------
$cm = add_moduleinfo($moduleinfo, $course);

// ----------------------------
// FINAL VERIFICATION
// ----------------------------
mtrace("SUCCESS");
mtrace("Quiz created:");
mtrace(" - Name: {$quizname}");
mtrace(" - Course ID: {$courseid}");
mtrace(" - Course module ID: {$cm->id}");
mtrace(" - Section: {$sectionnum}");
mtrace(" - Visible: YES");