<?php
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/gradelib.php');

echo "== Phase 1C-1: Simulated Quiz Grades ==\n";

$quizzes = $DB->get_records_sql("
    SELECT q.*, cm.id AS cmid, c.id AS courseid
      FROM {quiz} q
      JOIN {course_modules} cm ON cm.instance = q.id
      JOIN {modules} m ON m.id = cm.module AND m.name = 'quiz'
      JOIN {course} c ON c.id = q.course
     WHERE q.name LIKE 'Analytics Baseline Quiz%'
");

if (!$quizzes) {
    echo "No Analytics Baseline quizzes found.\n";
    exit(0);
}

foreach ($quizzes as $quiz) {
    echo "Processing quiz: {$quiz->name}\n";

    $context = context_course::instance($quiz->courseid);
    $users = get_enrolled_users($context, 'mod/quiz:attempt');

    foreach ($users as $user) {
        // Example: deterministic but varied baseline score
        $score = rand(55, 95);

        grade_update(
            'mod/quiz',
            $quiz->courseid,
            'mod',
            'quiz',
            $quiz->id,
            0,
            [
                $user->id => [
                    'userid' => $user->id,
                    'rawgrade' => $score
                ]
            ]
        );

        echo "  Assigned grade {$score} to user {$user->id}\n";
    }
}

echo "Phase 1C-1 complete.\n";
