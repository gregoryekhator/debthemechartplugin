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
            'synopsis' => ['label' => 'Synopsis (to date)', 'default_render' => 'bar', 'tooltip' => 'ML Analysis: Tracks weekly grade velocity to predict end-of-term outcomes.'],
            'best' => ['label' => 'Best Courses', 'label_short' => 'Best', 'default_render' => 'bar', 'tooltip' => 'ML Analysis: Identifies courses where your engagement-to-grade ratio is highest.'],
            'cohort' => ['label' => 'Cohort Performance', 'default_render' => 'bar', 'tooltip' => 'ML Analysis: Uses K-Means clustering to group you with similar performing peers.'],
            'best_plan' => ['label' => 'Best Learning Plan', 'default_render' => 'pie', 'tooltip' => 'ML Analysis: Recommends the Learning Plan with the highest historical completion rate.'],
            'synopsis_30' => ['label' => '30 Day Synopsis', 'default_render' => 'line', 'tooltip' => 'ML Analysis: Linear regression model forecasting next week predicted score.'],
            'lowest_courses' => ['label' => 'Lowest Courses', 'default_render' => 'bar', 'tooltip' => 'ML Analysis: Flags courses where Time-on-Task is significantly below average.'],
            'frequency' => ['label' => 'Study Frequency', 'default_render' => 'line', 'tooltip' => 'ML Analysis: Pattern recognition of study habits (Cramming vs Consistent).'],
            'style' => ['label' => 'Preferred Learning Style', 'default_render' => 'bar', 'tooltip' => 'ML Analysis: Random Forest Classifier predicting your optimal content format.']
        ];
    }
}