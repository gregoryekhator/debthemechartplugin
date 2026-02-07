<?php
// Phase 1B-3.4: Normalise quiz grading so attempts are allowed.

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

cli_heading('Phase 1B-3.4: Normalise Quiz Grades');

global $DB;

$quizzes = $DB->get_records_sql("
    SELECT q.*, c.shortname
      FROM {quiz} q
      JOIN {course} c ON c.id = q.course
     WHERE c.shortname LIKE 'ANALYTICS_%'
");

foreach ($quizzes as $quiz) {
    cli_writeln("Processing quiz: {$quiz->name} ({$quiz->shortname})");

    $slots = $DB->get_records('quiz_slots', ['quizid' => $quiz->id], 'slot ASC');

    if (!$slots) {
        cli_writeln('  No slots found — skipped');
        continue;
    }

    $markperslot = $quiz->grade / count($slots);
    $sumgrades = 0;

    foreach ($slots as $slot) {
        if ((float)$slot->maxmark !== (float)$markperslot) {
            $slot->maxmark = $markperslot;
            $DB->update_record('quiz_slots', $slot);
        }
        $sumgrades += $markperslot;
    }

    if ((float)$quiz->sumgrades !== (float)$sumgrades) {
        $quiz->sumgrades = $sumgrades;
        $DB->update_record('quiz', $quiz);
    }

    cli_writeln("  Set {$markperslot} marks per question (sum={$sumgrades})");
}

cli_writeln('Phase 1B-3.4 complete.');
