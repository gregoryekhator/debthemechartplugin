<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();
use core\chart_series;

class synopsis {

    public static function get_button_definitions() {
        return [
            'synopsis' => ['label' => 'Synopsis (to date)', 'title' => "Your overall performance"],
            'best'     => ['label' => 'Best Courses', 'title' => 'Your best courses'],
            'cohort'   => ['label' => 'Cohort Performance', 'title' => 'Grade distribution'],
            'plan'     => ['label' => 'Best Learning Plan', 'title' => 'ML Forecast Performance'],
            '30day'    => ['label' => '30 Day Synopsis', 'title' => 'Performance over 30 days'],
            'lowest'   => ['label' => 'Lowest Courses', 'title' => 'Your lowest courses'],
            'freq'     => ['label' => 'Study Frequency', 'title' => 'Work rate relative to class'],
            'style'    => ['label' => 'Preferred Learning Style', 'title' => 'Learning Analytics']
        ];
    }

    public static function get_template_data($userid, $current_type, $render) {
        $buttons = [];
        $current_type = trim(strval($current_type));

        foreach (self::get_button_definitions() as $type => $data) {
            $is_active = (strval($type) === $current_type);
            $buttons[] = [
                'label' => $data['label'],
                'url' => (new \moodle_url('/local/chartplugin/index.php', ['type' => $type, 'render' => $render]))->out(false),
                'active_class' => $is_active ? 'deb-active-now' : ''
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
        $chart = $is_sidebar ? new \core\chart_line() : self::get_chart_instance($render_type, $is_sidebar);
        $chart->set_labels(['Course A', 'Course B', 'Course C']);
        $series = new chart_series('Performance %', [75, 82, 68]);
        $series->set_color('#6f42c1'); 
        $chart->add_series($series);
        return $chart;
    }

    public static function get_history_blocks($userid) {
        global $DB, $OUTPUT;
        // Fetch 2 previous views, skipping the current one
        $records = $DB->get_records('local_chartplugin_history', ['userid' => $userid], 'timecreated DESC', '*', 1, 2);
        
        $blocks = [];
        $defs = self::get_button_definitions();
        $i = 0;

        foreach ($records as $rec) {
            $type = (string)$rec->chart_type;
            $title_label = ($i === 0) ? "Last View" : "Previous View";
            
            $blocks[] = [
                'title' => $title_label . ": " . ($defs[$type]['label'] ?? 'Archive'),
                'content' => $OUTPUT->render(self::build_dynamic_chart($type, true, 'line'))
            ];
            $i++;
        }
        return $blocks;
    }

    public static function save_view_history($userid, $type, $render) {
        global $DB;
        $record = (object)[
            'userid' => $userid,
            'chart_type' => (string)$type,
            'render_type' => $render,
            'data_snapshot' => json_encode(['ts' => time()]),
            'timecreated' => time()
        ];
        return $DB->insert_record('local_chartplugin_history', $record);
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