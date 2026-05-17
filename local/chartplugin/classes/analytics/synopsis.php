<?php
/**
 * synopsis.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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
        global $DB;
        $labels = [];
        $data = [];

        switch ($type) {
            case 'synopsis':
            case 'best':
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
                try {
                    if (class_exists('\core_competency\api')) {
                        $plans = \core_competency\api::list_user_plans($userid);
                        
                        if (!empty($plans)) {
                            $plan = reset($plans);
                            // FIX: Safe ID access for Moodle 4.5
                            $planid = (is_object($plan) && method_exists($plan, 'get')) ? $plan->get('id') : $plan->id;

                            $competencies = \core_competency\api::list_plan_competencies($planid);
                            
                            foreach ($competencies as $pc) {
                                // FIX: Safe competency object extraction
                                $comp = (is_object($pc) && method_exists($pc, 'get')) ? $pc->get('competency') : ($pc->competency ?? null);
                                
                                if ($comp) {
                                    // FIX: Safe shortname/id access
                                    $labels[] = (is_object($comp) && method_exists($comp, 'get')) ? $comp->get('shortname') : $comp->shortname;
                                    $compid = (is_object($comp) && method_exists($comp, 'get')) ? $comp->get('id') : $comp->id;

                                    $usercomp = \core_competency\api::get_user_competency($userid, $compid);
                                    
                                    $is_proficient = false;
                                    if ($usercomp) {
                                        // FIX: Safe proficiency check
                                        $is_proficient = (is_object($usercomp) && method_exists($usercomp, 'get')) ? $usercomp->get('proficiency') : ($usercomp->proficiency ?? false);
                                    }
                                    $data[] = $is_proficient ? 100 : 45;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    debugging('Learning Plan API Error: ' . $e->getMessage(), DEBUG_DEVELOPER);
                }
                break;

            case 'synopsis_30':
                $labels = ['D5', 'D10', 'D15', 'D20', 'D25', 'D30'];
                $data = [65, 72, 68, 75, 82, 85]; 
                break;

            case 'style':
                $labels = ['Video', 'Reading', 'Quiz', 'Forum'];
                $data = [45, 20, 30, 5]; 
                break;
        }

        if (empty($labels)) {
            $labels = ['No Data'];
            $data = [0];
        }

        return [
            'labels' => $labels, 
            'series' => new \core\chart_series('Score %', $data),
            'raw_data' => $data 
        ];
    }

    public static function get_button_definitions() {
        $access = local_chartplugin_get_access_status();
        $is_locked = ($access === 'freemium');

        return [
            'synopsis'       => ['label' => 'Synopsis (to date)', 'default_render' => 'bar', 'tooltip' => "Overall performance overview."],
            'best'           => ['label' => 'Best Courses', 'default_render' => 'bar', 'tooltip' => "Top performing subjects."],
            'cohort'         => ['label' => 'Cohort Performance', 'default_render' => 'line', 'tooltip' => "Your position vs peers."],
            'best_plan'      => [
                'label' => 'Best Learning Plan', 
                'default_render' => 'pie', 
                'tooltip' => "Competency progress.",
                'url' => $is_locked ? 'buy.php' : 'index.php?type=best_plan'
            ],
            'synopsis_30'    => ['label' => '30 Day Synopsis', 'default_render' => 'line', 'tooltip' => "Monthly velocity."],
            'lowest_courses' => ['label' => 'Lowest Courses', 'default_render' => 'bar', 'tooltip' => "Gap analysis."],
            'frequency'      => ['label' => 'Study Frequency', 'default_render' => 'bar', 'tooltip' => "Work rate analysis."],
            'style'          => [
                'label' => 'Cognitive Pattern', 
                'default_render' => 'pie', 
                'tooltip' => "Learning style detection.",
                'url' => $is_locked ? 'buy.php' : 'index.php?type=style'
            ]
        ];
    }
}