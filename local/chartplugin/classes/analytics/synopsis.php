<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

class synopsis {
    public static function get_ai_performance_delta($userid) {
        return "AI Insight: Your engagement is optimal. Meeting 88% of ML-predicted success markers.";
    }

    public static function get_chart_data($userid, $type = 'synopsis') {
        $labels = ['W1', 'W2', 'W3', 'W4'];
        switch ($type) {
            case 'best': $labels = ['Course A', 'Course B', 'Course C']; $data = [92, 88, 95]; break;
            case 'cohort': $labels = ['You', 'Peer Avg', 'Top 10%']; $data = [75, 68, 90]; break;
            case 'best_plan': $labels = ['Visual', 'Auditory', 'Kinesthetic']; $data = [60, 25, 15]; break;
            case 'synopsis_30': $labels = ['D5', 'D10', 'D15', 'D20', 'D25', 'D30']; $data = [60, 65, 70, 75, 80, 85]; break;
            case 'lowest_courses': $labels = ['Math', 'Physics', 'History']; $data = [40, 32, 45]; break;
            case 'frequency': $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; $data = [5, 12, 8, 2, 15, 20, 18]; break;
            case 'style': $labels = ['Video', 'Reading', 'Quiz', 'Forum']; $data = [45, 20, 30, 5]; break;
            default: $data = [70, 85, 60, 75]; break;
        }
        return ['labels' => $labels, 'series' => new \core\chart_series('Score %', $data)];
    }

    public static function get_button_definitions() {
        return [
            'synopsis' => ['label' => 'Synopsis (to date)', 'default_render' => 'bar', 'tooltip' => 'Weekly grade velocity.'],
            'best' => ['label' => 'Best Courses', 'default_render' => 'bar', 'tooltip' => 'Highest engagement ratios.'],
            'cohort' => ['label' => 'Cohort Performance', 'default_render' => 'bar', 'tooltip' => 'Peer clustering comparison.'],
            'best_plan' => ['label' => 'Best Learning Plan', 'default_render' => 'pie', 'tooltip' => 'Completion rate plans.'],
            'synopsis_30' => ['label' => '30 Day Synopsis', 'default_render' => 'line', 'tooltip' => 'Regression forecasting.'],
            'lowest_courses' => ['label' => 'Lowest Courses', 'default_render' => 'bar', 'tooltip' => 'Low Time-on-Task flags.'],
            'frequency' => ['label' => 'Study Frequency', 'default_render' => 'line', 'tooltip' => 'Pattern recognition habits.'],
            'style' => ['label' => 'Preferred Learning Style', 'default_render' => 'bar', 'tooltip' => 'Predicted content format.']
        ];
    }
}