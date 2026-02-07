<?php
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/classes/quiz_settings.php');

use mod_quiz\quiz_settings;

global $DB;

echo "== Phase 1C-2a: Rebuild Quiz Grades into Gradebook ==\n";

$quizzes = $DB->get_records_sql("
    SELECT q.id
      FROM {quiz} q
     WHERE q.name = 'Analytics Baseline Quiz'
");

if (!$quizzes) {
    echo "No Analytics Baseline quizzes found.\n";
    exit(0);
}

foreach ($quizzes as $qrec) {
    // Load quiz through settings (this hydrates required properties)
    $quizobj = quiz_settings::create($qrec->id);

    $quiz = $quizobj->get_quiz();
    $course = $quizobj->get_course();

    echo "Processing quiz in course: {$course->shortname}\n";

    // Correct API usage
    quiz_update_grades($quiz, 0);

    echo "  Grades rebuilt for quiz ID {$quiz->id}\n";
}

echo "Phase 1C-2a complete.\n";
