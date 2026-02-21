<?php
// This script creates a controlled set of analytics courses.
// Run as: php local/chartplugin/cli/phase1b_create_courses.php

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');

cli_heading('Phase 1B-1: Analytics Course Creation');

// Define courses
$courses = [
    [
        'fullname'  => 'Analytics Maths 101',
        'shortname' => 'ANALYTICS_MATHS',
    ],
    [
        'fullname'  => 'Analytics English 101',
        'shortname' => 'ANALYTICS_ENGLISH',
    ],
    [
        'fullname'  => 'Analytics Science 101',
        'shortname' => 'ANALYTICS_SCIENCE',
    ],
];

// Use default category
$categoryid = 1;

foreach ($courses as $coursedata) {
    if ($DB->record_exists('course', ['shortname' => $coursedata['shortname']])) {
        cli_writeln("Course already exists: {$coursedata['shortname']} (skipped)");
        continue;
    }

    $course = new stdClass();
    $course->fullname   = $coursedata['fullname'];
    $course->shortname  = $coursedata['shortname'];
    $course->category   = $categoryid;
    $course->visible    = 1;
    $course->format     = 'topics';
    $course->numsections = 3;
    $course->summary    = 'Analytics test course for learning data generation';
    $course->summaryformat = FORMAT_HTML;

    $created = create_course($course);

    cli_writeln("Created course: {$created->fullname} (id={$created->id})");
}

cli_writeln('Phase 1B-1 complete.');
