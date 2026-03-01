<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

class synopsis {
    public static function get_ai_performance_delta($userid) {
        return "Your engagement is optimal. Meeting 88% of ML-predicted success markers.";
    }

    public static function get_chart_data($userid, $type = 'synopsis') {
        // Keeps your working data logic
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
        // Row 1: Primary Metrics
        'synopsis'       => ['label' => 'Synopsis (to date)', 'default_render' => 'bar', 'tooltip' => "Your overall performance since sign-up or restart. Tip: Consistency is key to long-term retention!"],
        'best'           => ['label' => 'Best Courses', 'default_render' => 'bar', 'tooltip' => "Your best courses by scores / performance vs the class performance."],
        'cohort'         => ['label' => 'Cohort Performance', 'default_render' => 'line', 'tooltip' => "Grade distribution curve in your cohort. Mouse over for more information to improve."],
        'best_plan'      => ['label' => 'Best Learning Plan', 'default_render' => 'pie', 'tooltip' => "Performance on your current competency plan."],
        
        // Row 2: Deep Insights
        'synopsis_30'    => ['label' => '30 Day Synopsis', 'default_render' => 'line', 'tooltip' => "Your performance over the last month vs the class performance."],
        'lowest_courses' => ['label' => 'Lowest Courses', 'default_render' => 'bar', 'tooltip' => "The best recovery plan through gap analysis and tips."],
        'frequency'      => ['label' => 'Study Frequency', 'default_render' => 'bar', 'tooltip' => "This is your work rate relative to your class."],
        'style'          => ['label' => 'Learning Style', 'default_render' => 'pie', 'tooltip' => "Focus on your strength: Visual, Audio, Reflective or Kinesthetic."]
    ];
}
}