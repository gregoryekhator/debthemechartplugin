<?php
/**
 * compute_analytics_task.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

namespace local_chartplugin\task;

defined('MOODLE_INTERNAL') || die();

class compute_analytics_task extends \core\task\scheduled_task {

    public function get_name() {
        return "Compute ML analytics for all students";
    }

    public function execute() {
        global $DB;
        require_once(__DIR__ . '/../../lib.php');

        mtrace("Starting ML Analytics refresh for all users...");

        // 1. Get all students enrolled in active courses
        $sql = "SELECT DISTINCT userid FROM {role_assignments} WHERE roleid = 5"; // 5 is usually 'student'
        $students = $DB->get_records_sql($sql);

        foreach ($students as $student) {
            $userid = $student->userid;
            $courseid = 2; // In a real system, you'd loop through all their courses

            // A. Get Data (Cohort and History)
            $cohort_avg = local_chartplugin_get_cohort_average($courseid);
            $trend_json = get_config('local_chartplugin', 'user_trend_snapshot_' . $userid);
            
            if (!$trend_json) continue;

            // B. ML Prediction (Linear Regression Logic)
            $data = json_decode($trend_json, true);
            $scores = array_values($data);
            $n = count($scores);
            if ($n < 2) continue; // Need at least 2 points to predict

            $sumX = 0; $sumY = 0; $sumXY = 0; $sumXX = 0;
            foreach ($scores as $x => $y) {
                $sumX += $x; $sumY += $y; $sumXY += ($x * $y); $sumXX += ($x * $x);
            }
            $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
            $intercept = ($sumY - $slope * $sumX) / $n;
            $prediction = ($slope * $n) + $intercept;

            // C. Save to High-Speed Table
            $record = new \stdClass();
            $record->userid = $userid;
            $record->courseid = $courseid;
            $record->monthly_trend = $trend_json;
            $record->cohort_avg = $cohort_avg;
            $record->prediction = $prediction;
            $record->timemodified = time();

            if ($existing = $DB->get_record('local_chartplugin_trends', ['userid' => $userid, 'courseid' => $courseid])) {
                $record->id = $existing->id;
                $DB->update_record('local_chartplugin_trends', $record);
            } else {
                $DB->insert_record('local_chartplugin_trends', $record);
            }
            
            mtrace("Processed User $userid: Forecast " . round($prediction, 2) . "%");
        }
        
        mtrace("ML Analytics refresh complete.");
    }
}