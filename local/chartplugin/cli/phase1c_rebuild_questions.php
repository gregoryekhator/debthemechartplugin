<?php
/**
 * phase1c_rebuild_questions.php
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
require_once($CFG->libdir . '/questionlib.php');

global $DB, $USER;

mtrace("== Phase 1C-1: Repair Baseline Quiz Questions ==");

$difficulties = [
    1 => 'Core',
    2 => 'Intermediate',
    3 => 'Advanced'
];

$quizzes = $DB->get_records_sql("
    SELECT q.id, q.course, q.name, c.shortname
    FROM {quiz} q
    JOIN {course} c ON c.id = q.course
    WHERE q.name = 'Analytics Baseline Quiz'
");

foreach ($quizzes as $quiz) {
    mtrace("Processing quiz: {$quiz->shortname}");

    $slots = $DB->get_records('quiz_slots', ['quizid' => $quiz->id], 'slot ASC');

    foreach ($slots as $slot) {
        $difficulty = $difficulties[$slot->slot] ?? 'Baseline';

        // Load question
        $question = $DB->get_record_sql("
            SELECT q.*
            FROM {quiz_slots} qs
            JOIN {question_references} qr
              ON qr.itemid = qs.id
             AND qr.component = 'mod_quiz'
             AND qr.questionarea = 'slot'
            JOIN {question_bank_entries} qbe
              ON qbe.id = qr.questionbankentryid
            JOIN {question_versions} qv
              ON qv.questionbankentryid = qbe.id
             AND qv.status = 'ready'
            JOIN {question} q
              ON q.id = qv.questionid
            WHERE qs.id = ?
        ", [$slot->id], MUST_EXIST);

        // Resolve category + context correctly
        $category = $DB->get_record_sql("
            SELECT qc.id, qc.contextid
            FROM {question_references} qr
            JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
            JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
            WHERE qr.component = 'mod_quiz'
              AND qr.questionarea = 'slot'
              AND qr.itemid = ?
        ", [$slot->id], MUST_EXIST);

        $newname = "{$quiz->shortname} – {$difficulty} Analytics Baseline";

        // Prepare formdata
        $formdata = clone $question;
        $formdata->id = $question->id;
        $formdata->category = $category->id;
        $formdata->contextid = $category->contextid;

        $formdata->name = $newname;
        $formdata->questiontext = "<p>{$newname}</p>";
        $formdata->questiontextformat = FORMAT_HTML;
        $formdata->generalfeedback = '';
        $formdata->generalfeedbackformat = FORMAT_HTML;
        $formdata->defaultmark = 1;
        $formdata->modifiedby = $USER->id;

        // Save via Question API
        $qtype = question_bank::get_qtype($question->qtype);
        $DB->update_record('question', (object)[
    'id' => $question->id,
    'name' => $newname,
    'questiontext' => $formdata->questiontext,
    'questiontextformat' => FORMAT_HTML,
    'defaultmark' => 1,
    'timemodified' => time(),
    'modifiedby' => $USER->id,
]);


        mtrace("  ✔ Updated question: {$newname}");
    }
}

mtrace("Phase 1C-1 complete.");