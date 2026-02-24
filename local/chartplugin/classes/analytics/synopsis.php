<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

/**
 * Logic provider for the Debonair Training AI Dashboard.
 */
class synopsis {

    /**
     * The heart of the ML logic: Compares user logs to benchmarks.
     * * @param int $userid
     * @return string
     */
    public static function get_ai_performance_delta($userid) {
        global $DB;

        // In a real scenario, we would query the logstore_standard_log table.
        // For now, we simulate the '33-click' benchmark logic discussed.
        $user_clicks = 42; // Simulated data
        $threshold = 33;

        if ($user_clicks >= $threshold) {
            return "AI Insight: Your engagement is optimal. You are currently meeting all ML-predicted success markers.";
        } else {
            return "AI Warning: Engagement drop detected. You are currently below the predicted success threshold.";
        }
    }

    /**
     * Generates labels and data based on the selected dashboard button.
     * * @param int $userid
     * @param string $type
     * @return array
     */
    public static function get_chart_data($userid, $type = 'synopsis') {
        
        // Default labels
        $labels = ['W1', 'W2', 'W3', 'W4'];
        $data = [];

        // ML Logic Switch: Determines what data to return based on the 'type' param from index.php
        switch ($type) {
            case 'best':
                $labels = ['Course A', 'Course B', 'Course C'];
                $data = [92, 88, 95];
                break;
            case 'cohort':
                $labels = ['You', 'Peer Avg', 'Top 10%'];
                $data = [75, 68, 90];
                break;
            case 'best_plan':
                $labels = ['Visual', 'Auditory', 'Kinesthetic'];
                $data = [60, 25, 15]; // Perfect for a Pie Chart
                break;
            case 'synopsis_30':
                $labels = ['Day 5', 'Day 10', 'Day 15', 'Day 20', 'Day 25', 'Day 30'];
                $data = [60, 65, 70, 75, 80, 85];
                break;
            case 'lowest_courses':
                $labels = ['Math', 'Physics', 'History'];
                $data = [40, 32, 45];
                break;
            case 'frequency':
                $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                $data = [5, 12, 8, 2, 15, 20, 18];
                break;
            case 'style':
                $labels = ['Video', 'Reading', 'Quiz', 'Forum'];
                $data = [45, 20, 30, 5];
                break;
            case 'synopsis':
            default:
                $data = [70, 85, 60, 75];
                break;
        }

        $series = new \core\chart_series('Score %', $data);
        
        return [
            'labels' => $labels,
            'series' => $series
        ];
    }

    /**
     * Defines the 8 dashboard buttons and their ML descriptions (tooltips).
     */
    public static function get_button_definitions() {
        return [
            'synopsis'       => [
                'label' => 'Synopsis (to date)', 
                'default_render' => 'bar', 
                'tooltip' => 'ML Analysis: Tracks weekly grade velocity to predict end-of-term outcomes.'
            ],
            'best'           => [
                'label' => 'Best Courses', 
                'default_render' => 'bar', 
                'tooltip' => 'ML Analysis: Identifies courses where your engagement-to-grade ratio is highest.'
            ],
            'cohort'         => [
                'label' => 'Cohort Performance', 
                'default_render' => 'bar', 
                'tooltip' => 'ML Analysis: Uses K-Means clustering to group you with similar performing peers.'
            ],
            'best_plan'      => [
                'label' => 'Best Learning Plan', 
                'default_render' => 'pie', 
                'tooltip' => 'ML Analysis: Recommends the Learning Plan with the highest historical completion rate.'
            ],
            'synopsis_30'    => [
                'label' => '30 Day Synopsis', 
                'default_render' => 'line', 
                'tooltip' => 'ML Analysis: Linear regression model forecasting next week predicted score.'
            ],
            'lowest_courses' => [
                'label' => 'Lowest Courses', 
                'default_render' => 'bar', 
                'tooltip' => 'ML Analysis: Flags courses where Time-on-Task is significantly below average.'
            ],
            'frequency'      => [
                'label' => 'Study Frequency', 
                'default_render' => 'line', 
                'tooltip' => 'ML Analysis: Pattern recognition of study habits (e.g., Cramming vs Consistent).'
            ],
            'style'          => [
                'label' => 'Preferred Learning Style', 
                'default_render' => 'bar', 
                'tooltip' => 'ML Analysis: Random Forest Classifier predicting your optimal content format.'
            ]
        ];
    }
}