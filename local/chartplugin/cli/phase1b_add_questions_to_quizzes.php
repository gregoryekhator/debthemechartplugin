<?php
/**
 * phase1b_add_questions_to_quizzes.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Phase 1B-3.3: Add baseline questions to analytics quizzes
// Canonical, schema-safe version.

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

cli_heading('Phase 1B-3.3: Add Baseline Questions to Quizzes');

global $DB;

$courses = $DB->get_records_select(
    'course',
    "shortname LIKE 'ANALYTICS_%'",
    [],
    'shortname ASC'
);

foreach ($courses as $course) {
    cli_writeln("Processing course: {$course->shortname}");

    // ---------------------------------------------------------------------
    // Resolve quiz
    // ---------------------------------------------------------------------
    $quiz = $DB->get_record('quiz', [
        'course' => $course->id,
        'name'   => 'Analytics Baseline Quiz'
    ], '*', IGNORE_MISSING);

    if (!$quiz) {
        cli_writeln("  Quiz not found (skipped)");
        continue;
    }

    // ---------------------------------------------------------------------
    // Resolve question categories
    // ---------------------------------------------------------------------
    $context = context_course::instance($course->id);

    $parent = $DB->get_record('question_categories', [
        'contextid' => $context->id,
        'name'      => 'Analytics Baseline'
    ], '*', MUST_EXIST);

    $categories = $DB->get_records('question_categories', [
        'parent' => $parent->id
    ]);

    foreach ($categories as $category) {

        $questionname = "{$course->shortname} – {$category->name} – Baseline";

        // -----------------------------------------------------------------
        // Resolve question ID (latest ready version)
        // -----------------------------------------------------------------
        $question = $DB->get_record_sql(
            "SELECT q.id
               FROM {question} q
               JOIN {question_versions} qv ON qv.questionid = q.id
               JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
              WHERE q.name = :name
                AND qv.status = :status",
            [
                'name'   => $questionname,
                'status' => 'ready'
            ],
            IGNORE_MISSING
        );

        if (!$question) {
            cli_writeln("  Missing question: {$questionname} (skipped)");
            continue;
        }

        // -----------------------------------------------------------------
        // OFFICIAL MOODLE QUIZ API — NO ID CHECKS
        // -----------------------------------------------------------------
        quiz_add_quiz_question($question->id, $quiz);

        cli_writeln("  Added question to quiz: {$questionname}");
    }
}

cli_writeln('Phase 1B-3.3 complete.');