<?php
// Phase 1B-3.2: Create baseline questions correctly (Moodle 4.x compliant).

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/question/editlib.php');

cli_heading('Phase 1B-3.2: Baseline Question Creation (Final)');

global $DB;

$qtype = question_bank::get_qtype('multichoice');

$courses = $DB->get_records_select(
    'course',
    "shortname LIKE 'ANALYTICS_%'",
    [],
    'shortname ASC'
);

foreach ($courses as $course) {
    cli_writeln("Processing course: {$course->shortname}");

    $coursecontext = context_course::instance($course->id);

    $parent = $DB->get_record('question_categories', [
        'contextid' => $coursecontext->id,
        'name'      => 'Analytics Baseline'
    ], '*', MUST_EXIST);

    $categories = $DB->get_records('question_categories', [
        'parent' => $parent->id
    ]);

    foreach ($categories as $category) {

        $questionname = "{$course->shortname} – {$category->name} – Baseline";

        // Idempotency check (Question Bank aware)
        $exists = $DB->record_exists_sql(
            "SELECT 1
               FROM {question} q
               JOIN {question_versions} qv ON qv.questionid = q.id
               JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
              WHERE q.name = :name
                AND qbe.questioncategoryid = :categoryid
                AND qv.status = 'ready'",
            [
                'name'       => $questionname,
                'categoryid' => $category->id
            ]
        );

        if ($exists) {
            cli_writeln("  Question exists: {$questionname}");
            continue;
        }

        // ---- Question base ----
        $question = new stdClass();
        $question->name = $questionname;
        $question->questiontext = "Baseline question for {$category->name}.";
        $question->questiontextformat = FORMAT_HTML;
        $question->generalfeedback = '';
        $question->generalfeedbackformat = FORMAT_HTML;
        $question->defaultmark = 1;
        $question->penalty = 0;
        $question->qtype = 'multichoice';
        $question->length = 1;
        $question->stamp = make_unique_id_code();
        $question->version = 1;
        $question->hidden = 0;

        // CRITICAL: category must include context
        $question->category = "{$category->id},{$coursecontext->id}";

        // ---- Form-style data (THIS FIXES THE ERROR) ----
        $formdata = clone $question;

        $formdata->single = 1;
        $formdata->shuffleanswers = 1;
        $formdata->answernumbering = 'abc';

        $formdata->answer = [
            0 => ['text' => 'Correct answer', 'format' => FORMAT_HTML],
            1 => ['text' => 'Incorrect answer', 'format' => FORMAT_HTML],
        ];

        $formdata->fraction = [
            0 => 1.0,
            1 => 0.0,
        ];

        $formdata->feedback = [
            0 => ['text' => '', 'format' => FORMAT_HTML],
            1 => ['text' => '', 'format' => FORMAT_HTML],
        ];

// Required multichoice-level feedback (even if empty)
$formdata->correctfeedback = [
    'text' => '',
    'format' => FORMAT_HTML
];

$formdata->partiallycorrectfeedback = [
    'text' => '',
    'format' => FORMAT_HTML
];

$formdata->incorrectfeedback = [
    'text' => '',
    'format' => FORMAT_HTML
];

        // Save via official Question API
        $qtype->save_question($question, $formdata);

        cli_writeln("  Created question: {$questionname}");
    }
}

cli_writeln('Phase 1B-3.2 complete.');
