<?php
// Creates one analytics-grade quiz per analytics course.
// Run as: php local/chartplugin/cli/phase1b_create_quizzes.php

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

cli_heading('Phase 1B-2: Analytics Quiz Creation');

// Resolve quiz module ID (CRITICAL in CLI)
$quizmoduleid = $DB->get_field('modules', 'id', ['name' => 'quiz'], MUST_EXIST);

// Fetch analytics courses
$courses = $DB->get_records_sql(
    "SELECT *
       FROM {course}
      WHERE shortname LIKE 'ANALYTICS_%'"
);

if (!$courses) {
    cli_error('No analytics courses found. Run Phase 1B-1 first.');
}

foreach ($courses as $course) {

    // Check if quiz already exists
    $exists = $DB->record_exists_sql(
        "SELECT 1
           FROM {quiz} q
           JOIN {course_modules} cm ON cm.instance = q.id
          WHERE cm.course = ?
            AND q.name = ?",
        [$course->id, 'Analytics Baseline Quiz']
    );

    if ($exists) {
        cli_writeln("Quiz already exists in course: {$course->shortname} (skipped)");
        continue;
    }

    $moduleinfo = new stdClass();

    // Required identifiers
    $moduleinfo->modulename = 'quiz';
    $moduleinfo->module     = $quizmoduleid;
    $moduleinfo->course     = $course->id;

    // Presentation
    $moduleinfo->name = 'Analytics Baseline Quiz';
    $moduleinfo->intro = 'Baseline quiz for learning analytics and performance analysis.';
    $moduleinfo->introformat = FORMAT_HTML;

    // Quiz configuration
    $moduleinfo->attempts    = 2;
    $moduleinfo->grademethod = QUIZ_GRADEHIGHEST;
    $moduleinfo->timelimit   = 15 * MINSECS;
    $moduleinfo->grade       = 100;
    $moduleinfo->quizpassword = '';

    // Completion tracking
    $moduleinfo->completion = COMPLETION_TRACKING_AUTOMATIC;
    $moduleinfo->completionattemptsexhausted = 1;

    // Placement
    $moduleinfo->section = 1;
    $moduleinfo->visible = 1;

// Availability
$moduleinfo->timeopen  = 0;
$moduleinfo->timeclose = 0;

// Display / grading
$moduleinfo->questiondecimalpoints = -1;

// Course module metadata
$moduleinfo->cmidnumber = '';
    add_moduleinfo($moduleinfo, $course);

    cli_writeln("Created quiz in course: {$course->shortname}");
}

cli_writeln('Phase 1B-2 complete.');
