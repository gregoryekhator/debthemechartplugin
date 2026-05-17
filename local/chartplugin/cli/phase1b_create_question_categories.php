<?php
/**
 * phase1b_create_question_categories.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Phase 1B-3.1: Create question categories for analytics courses.

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

cli_heading('Phase 1B-3.1: Question Category Creation');

global $DB;

// Target analytics courses.
$courses = $DB->get_records_select(
    'course',
    "shortname LIKE 'ANALYTICS_%'",
    [],
    'shortname ASC'
);

if (!$courses) {
    cli_error('No analytics courses found.');
}

foreach ($courses as $course) {
    cli_writeln("Processing course: {$course->shortname}");

    // --- API: context_course::instance ---
    // Type: Official Moodle API (Context API)
    $context = context_course::instance($course->id);

    // Parent category: "Analytics Baseline"
    $parentname = 'Analytics Baseline';

    // --- API: $DB->get_record ---
    // Type: Official Moodle DML API
    $parent = $DB->get_record('question_categories', [
        'contextid' => $context->id,
        'name'      => $parentname
    ]);

    if (!$parent) {
        $parent = (object)[
            'name'           => $parentname,
            'contextid'      => $context->id,
            'parent'         => 0,
            'sortorder'      => 999,
            'info'           => 'Baseline analytics question category',
            'infoformat'     => FORMAT_HTML,
            'stamp'          => make_unique_id_code(),
            'idnumber'       => null
        ];

        // --- API: $DB->insert_record ---
        // Type: Official Moodle DML API
        $parent->id = $DB->insert_record('question_categories', $parent);
        cli_writeln("  Created parent category: {$parentname}");
    } else {
        cli_writeln("  Parent category exists: {$parentname}");
    }

    // Child categories
    $children = [
        'Core Concepts',
        'Intermediate Concepts',
        'Advanced Concepts'
    ];

    foreach ($children as $childname) {
        $child = $DB->get_record('question_categories', [
            'contextid' => $context->id,
            'parent'    => $parent->id,
            'name'      => $childname
        ]);

        if ($child) {
            cli_writeln("    Category exists: {$childname}");
            continue;
        }

        $child = (object)[
            'name'       => $childname,
            'contextid'  => $context->id,
            'parent'     => $parent->id,
            'sortorder'  => 999,
            'info'       => "{$childname} for analytics",
            'infoformat' => FORMAT_HTML,
            'stamp'      => make_unique_id_code(),
            'idnumber'   => null
        ];

        $DB->insert_record('question_categories', $child);
        cli_writeln("    Created category: {$childname}");
    }
}

cli_writeln('Phase 1B-3.1 complete.');