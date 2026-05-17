<?php
/**
 * phase1c_aggregate_grades.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Phase 1C-2: Aggregate Analytics Grades (Authoritative DML)

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');

global $DB;

echo "== Phase 1C-2: Aggregate Analytics Grades ==\n";

$sql = "
    SELECT
        c.id AS courseid,
        gi.id AS gradeitemid,
        COUNT(qa.id) AS attempts,
        AVG(qa.sumgrades) AS avggrade,
        MIN(qa.sumgrades) AS mingrade,
        MAX(qa.sumgrades) AS maxgrade
    FROM {quiz_attempts} qa
    JOIN {quiz} q ON q.id = qa.quiz
    JOIN {course} c ON c.id = q.course
    JOIN {grade_items} gi
         ON gi.itemmodule = 'quiz'
        AND gi.iteminstance = q.id
    WHERE qa.state = 'finished'
      AND q.name = 'Analytics Baseline Quiz'
    GROUP BY c.id, gi.id
";

$records = $DB->get_records_sql($sql);

if (!$records) {
    echo "No quiz attempt data found.\n";
    exit(0);
}

$now = time();

foreach ($records as $r) {
    echo "Processing course ID {$r->courseid}\n";

    $existing = $DB->get_record(
        'local_chartplugin_course_stats',
        ['courseid' => $r->courseid]
    );

    $data = new stdClass();
    $data->courseid     = $r->courseid;
    $data->gradeitemid  = $r->gradeitemid;
    $data->attempts     = (int)$r->attempts;
    $data->avggrade     = round($r->avggrade, 2);
    $data->mingrade     = round($r->mingrade, 2);
    $data->maxgrade     = round($r->maxgrade, 2);
    $data->timemodified = $now;

    if ($existing) {
        $data->id = $existing->id;
        $DB->update_record('local_chartplugin_course_stats', $data);
        echo "  Updated aggregate record\n";
    } else {
        $DB->insert_record('local_chartplugin_course_stats', $data);
        echo "  Inserted aggregate record\n";
    }
}

echo "Phase 1C-2 complete.\n";