<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();
use core\chart_series;

class synopsis {

    public static function get_button_definitions() {
        return [
            'synopsis' => [
                'label' => 'Synopsis (to date)', 
                'title' => "Your overall performance since 'sign-up/restart'",
                'tooltip' => "Cumulative performance snapshot. Note: Adopt learning strategies congruent with your plan to improve results."
            ],
            'best' => [
                'label' => 'Best Courses', 
                'title' => 'Your best courses by scores / performance vs the class performance',
                'tooltip' => 'Modules where you have demonstrated mastery. Click to see what you did right here!'
            ],
            'cohort' => [
                'label' => 'Cohort Performance', 
                'title' => 'Grade distribution curve in your cohort',
                'tooltip' => "Grade distribution curve in your cohort. Mouse over for more information to improve."
            ],
            'plan' => [
                'label' => 'Best Learning Plan', 
                'title' => 'Best competency / learning plan performance over the next 30 days',
                'tooltip' => "The best competency / learning plan should result in this performance over the next 30 days."
            ],
            '30day' => [
                'label' => '30 Day Synopsis', 
                'title' => 'Your performance over the last month vs the class performance',
                'tooltip' => 'Your performance over the last month vs the class performance. Turning up the dial starts here!'
            ],
            'lowest' => [
                'label' => 'Lowest Courses', 
                'title' => 'Your lowest courses by score / performance vs the class',
                'tooltip' => 'Your lowest courses by score / performance vs the class. The best recovery plan through gap analysis and tips.'
            ],
            'freq' => [
                'label' => 'Study Frequency', 
                'title' => 'Your work rate relative to your class',
                'tooltip' => 'This is your work rate relative to your class. Commitment starts with turning up the dial.'
            ],
            'style' => [
                'label' => 'Preferred Learning Style', 
                'title' => 'Your preferred learning style as revealed by our learning analytics',
                'tooltip' => "Your preferred learning style as revealed by our analytics. Adopt strategies congruent with your plan."
            ]
        ];
    }

    public static function save_view_history($userid, $type, $render) {
        global $DB;
        $record = new \stdClass();
        $record->userid = $userid;
        $record->chart_type = (string)$type; 
        $record->render_type = $render;
        $record->data_snapshot = json_encode(['timestamp' => time(), 'status' => 'active']);
        $record->timecreated = time();
        return $DB->insert_record('local_chartplugin_history', $record);
    }

    public static function get_history_blocks($userid) {
        global $DB, $OUTPUT;
        $records = $DB->get_records('local_chartplugin_history', ['userid' => $userid], 'timecreated DESC', '*', 1, 2);
        
        $blocks = [];
        $i = 0;
        $defs = self::get_button_definitions();

        foreach ($records as $rec) {
            $type = $rec->chart_type;
            $title_label = ($i === 0) ? "Last View" : "Previous View";
            $desc = ($i === 0) ? "Your last chart view revealed this performance:" : "Before the last view, you viewed this:";

            $blocks[] = [
                'title' => $title_label . ": " . ($defs[$type]['label'] ?? 'Archive'),
                'description' => $desc,
                'content' => $OUTPUT->render(self::build_dynamic_chart($type, true, 'line'))
            ];
            $i++;
        }
        return $blocks;
    }

    public static function get_template_data($userid, $current_type, $render) {
        $buttons = [];
        $defs = self::get_button_definitions();
        foreach ($defs as $type => $data) {
            $buttons[] = [
                'label' => $data['label'],
                'url' => (new \moodle_url('/local/chartplugin/index.php', ['type' => $type, 'render' => $render]))->out(false),
                'active' => ((string)$type === (string)$current_type)
            ];
        }

        return [
            'buttons' => $buttons,
            'ai_hero_text' => 'Alert: You are 10.67% below average. ML Forecast: Next month\'s predicted grade is 67%.',
            'is_bar' => ($render === 'bar'),
            'is_line' => ($render === 'line'),
            'is_pie' => ($render === 'pie'),
            'bar_url' => (new \moodle_url('/local/chartplugin/index.php', ['type' => $current_type, 'render' => 'bar']))->out(false),
            'line_url' => (new \moodle_url('/local/chartplugin/index.php', ['type' => $current_type, 'render' => 'line']))->out(false),
            'pie_url' => (new \moodle_url('/local/chartplugin/index.php', ['type' => $current_type, 'render' => 'pie']))->out(false),
        ];
    }

    public static function build_dynamic_chart($type, $is_sidebar = false, $render_type = 'bar') {
        global $USER;
        $defs = self::get_button_definitions();
        $meta = $defs[$type] ?? ['title' => 'Activity Analysis', 'tooltip' => ''];

        $chart = ($is_sidebar) ? new \core\chart_line() : self::get_chart_instance($render_type, false);
        
        if (!$is_sidebar) {
            $chart->set_title($meta['title']);
        }

        $labels = ['Course A', 'Course B', 'Course C'];
        $user_vals = ($type === 'lowest') ? [40, 45, 50] : [75, 82, 68];
        
        $series = new chart_series($is_sidebar ? 'Historical %' : 'Performance %', $user_vals);
        $series->set_color($is_sidebar ? '#dc3545' : '#6f42c1'); 
        $chart->add_series($series);
        $chart->set_labels($labels);
        
        return $chart;
    }

    private static function get_chart_instance($render_type, $is_sidebar) {
        if ($is_sidebar) return new \core\chart_line();
        switch ($render_type) {
            case 'line': return new \core\chart_line();
            case 'pie':  return new \core\chart_pie();
            default:     return new \core\chart_bar();
        }
    }
}