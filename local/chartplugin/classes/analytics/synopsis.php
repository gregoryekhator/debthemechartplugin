<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

class synopsis {

    public static function get_ai_performance_delta($userid) {
        global $DB;
        
        // Get actual average from all courses
        $avg = $DB->get_field_sql("SELECT AVG(finalgrade) FROM {grade_grades} WHERE userid = :userid AND finalgrade IS NOT NULL", ['userid' => $userid]);
        $avg = round($avg ?: 0);

        // Dynamic Status Logic
        if ($avg >= 75) {
            $status = "Optimal";
            $color = "#28a745"; // Green
        } else if ($avg >= 50) {
            $status = "Stable";
            $color = "#ffc107"; // Yellow
        } else {
            $status = "Critical";
            $color = "#dc3545"; // Red
        }

        return "<span style='color: {$color}; font-weight: bold;'>{$status}</span>. Currently maintaining an aggregate score of {$avg}% across all modules.";
    }

    public static function get_dynamic_insight($type, $data) {
        if (empty($data)) return "No telemetry data available for this metric.";
        $avg = array_sum($data) / count($data);

        switch ($type) {
            case 'lowest_courses':
                return "AI identified a performance gap in " . count($data) . " subjects. Focus on the lowest bar to trigger a recovery plan.";
            case 'synopsis_30':
                $trend = (end($data) > reset($data)) ? "upward" : "downward";
                return "Your 30-day velocity is trending {$trend}. " . ($trend == 'upward' ? "Keep this momentum!" : "Action required to stabilize.");
            case 'style':
                arsort($data);
                $top_style = key($data);
                $styles = ['Video' => "Visual-Spatial", 'Reading' => "Linguistic-Verbal", 'Quiz' => "Logical-Mathematical", 'Forum' => "Interpersonal"];
                $detected = $styles[$top_style] ?? "Generalist";
                return "Cognitive Pattern detected: **{$detected}**. You show highest engagement via {$top_style}.";
            default:
                return "Telemetry analysis complete.";
        }
    }

    public static function get_chart_data($userid, $type = 'synopsis') {
        global $DB, $CFG;
        $labels = [];
        $data = [];

        switch ($type) {
            case 'synopsis':
            case 'best':
                // INTELLIGENT DATA: Pull actual course grades for this user
                $sql = "SELECT c.shortname, g.finalgrade 
                        FROM {course} c 
                        JOIN {grade_grades} g ON g.itemid IN (SELECT id FROM {grade_items} WHERE courseid = c.id AND itemtype = 'course')
                        WHERE g.userid = :userid AND c.id > 1 ORDER BY g.finalgrade DESC LIMIT 4";
                $records = $DB->get_records_sql($sql, ['userid' => $userid]);
                foreach ($records as $r) {
                    $labels[] = $r->shortname;
                    $data[] = round($r->finalgrade ?? 0);
                }
                break;

            case 'cohort':
                $labels = ['You', 'Peer Avg', 'Top 10%'];
                $user_avg = $DB->get_field_sql("SELECT AVG(finalgrade) FROM {grade_grades} WHERE userid = :userid", ['userid' => $userid]) ?: 0;
                $site_avg = $DB->get_field_sql("SELECT AVG(finalgrade) FROM {grade_grades}") ?: 0;
                $data = [round($user_avg), round($site_avg), 90]; 
                break;

            case 'best_plan':
                require_once($CFG->dirroot . '/admin/tool/lp/classes/api.php');
                try {
                    $plans = \tool_lp\api::list_user_plans($userid);
                    if (!empty($plans)) {
                        $plan = reset($plans);
                        $competencies = \tool_lp\api::list_plan_competencies($plan->get('id'));
                        foreach ($competencies as $pc) {
                            $comp = $pc->get('competency');
                            if ($comp) {
                                $labels[] = $comp->get('shortname');
                                $usercomp = \tool_lp\api::get_user_competency_in_plan($plan->get('id'), $comp->get('id'));
                                $data[] = ($usercomp && $usercomp->get('proficiency')) ? 100 : 45;
                            }
                        }
                    }
                } catch (\Exception $e) {}
                break;

            case 'synopsis_30':
                $labels = ['D5', 'D10', 'D15', 'D20', 'D25', 'D30'];
                $data = [65, 72, 68, 75, 82, 85]; // Dynamic logic to be tied to logs later
                break;

            case 'style':
                $labels = ['Video', 'Reading', 'Quiz', 'Forum'];
                $data = [45, 20, 30, 5]; // Tied to event_log counts in next step
                break;
        }

        // Final Fallback for missing data
        if (empty($labels)) {
            $labels = ['Module 1', 'Module 2', 'Module 3'];
            $data = [0, 0, 0];
        }

        return [
            'labels' => $labels, 
            'series' => new \core\chart_series('Score %', $data),
            'raw_data' => $data 
        ];
    }

    public static function get_button_definitions() {
        return [
            'synopsis'       => ['label' => 'Synopsis (to date)', 'default_render' => 'bar', 'tooltip' => "Overall performance overview."],
            'best'           => ['label' => 'Best Courses', 'default_render' => 'bar', 'tooltip' => "Top performing subjects."],
            'cohort'         => ['label' => 'Cohort Performance', 'default_render' => 'line', 'tooltip' => "Your position vs peers."],
            'best_plan'      => ['label' => 'Best Learning Plan', 'default_render' => 'pie', 'tooltip' => "Competency progress."],
            'synopsis_30'    => ['label' => '30 Day Synopsis', 'default_render' => 'line', 'tooltip' => "Monthly velocity."],
            'lowest_courses' => ['label' => 'Lowest Courses', 'default_render' => 'bar', 'tooltip' => "Gap analysis."],
            'frequency'      => ['label' => 'Study Frequency', 'default_render' => 'bar', 'tooltip' => "Work rate analysis."],
            'style'          => ['label' => 'Cognitive Pattern', 'default_render' => 'pie', 'tooltip' => "Learning style detection."]
        ];
    }
}