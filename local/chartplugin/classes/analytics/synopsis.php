<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

class synopsis {

    public static function get_button_definitions() {
        return [
            'synopsis'       => ['label' => 'Synopsis (to date)', 'default_render' => 'bar'],
            'best'           => ['label' => 'Best Courses', 'default_render' => 'bar'],
            'cohort'         => ['label' => 'Cohort Performance', 'default_render' => 'bar'],
            'best_plan'      => ['label' => 'Best Learning Plan', 'default_render' => 'pie'],
            'synopsis_30'    => ['label' => '30 Day Synopsis', 'default_render' => 'line'],
            'lowest_courses' => ['label' => 'Lowest Courses', 'default_render' => 'bar'],
            'frequency'      => ['label' => 'Study Frequency', 'default_render' => 'line'],
            'style'          => ['label' => 'Preferred Learning Style', 'default_render' => 'bar'],
        ];
    }

    public static function build_dynamic_chart($type, $is_sidebar = false, $render_type = 'bar') {
        // Force sidebar to be line, otherwise use the requested type.
        $actual_render = $is_sidebar ? 'line' : $render_type;

        $chart = match($actual_render) {
            'pie'   => new \core\chart_pie(),
            'line'  => new \core\chart_line(),
            default => new \core\chart_bar(),
        };

        // Standardized data to ensure rendering across all types.
        $datasets = [
            'style'          => ['Visual' => 75, 'Auditory' => 82, 'Reading' => 65, 'Kinesthetic' => 90],
            'best_plan'      => ['Alpha' => 88, 'Beta' => 76, 'Gamma' => 92],
            'synopsis'       => ['W1' => 70, 'W2' => 85, 'W3' => 60],
            'best'           => ['Python' => 95, 'Moodle' => 88, 'PHP' => 72],
            'cohort'         => ['C1' => 66, 'C2' => 78, 'C3' => 82],
            'frequency'      => ['Mon' => 20, 'Wed' => 45, 'Fri' => 30],
            'lowest_courses' => ['Math' => 40, 'Hist' => 35],
            'synopsis_30'    => ['D10' => 50, 'D20' => 70, 'D30' => 80]
        ];

        $data = $datasets[$type] ?? ['A' => 50, 'B' => 60];
        $series = new \core\chart_series('Score %', array_values($data));
        
        if ($is_sidebar) {
            $series->set_color('#dc3545'); // Red Sidebar
        } else {
            $series->set_color('#007bff'); // Blue Main
        }

        $chart->add_series($series);
        $chart->set_labels(array_keys($data));
        
        $defs = self::get_button_definitions();
        $chart->set_title($is_sidebar ? "" : ($defs[$type]['label'] ?? 'Analytics'));

        return $chart;
    }

    public static function get_history_blocks($userid) {
        global $DB, $OUTPUT;
        $history = $DB->get_records('local_chartplugin_history', ['userid' => $userid], 'timecreated DESC', '*', 0, 10);
        $blocks = [];
        $seen = [];
        $current_type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);

        foreach ($history as $h) {
            if (count($blocks) >= 2) break;
            if ($h->chart_type == $current_type || in_array($h->chart_type, $seen)) continue;
            
            $seen[] = $h->chart_type;
            // Sidebar is strictly LINE for stability.
            $chart = self::build_dynamic_chart($h->chart_type, true, 'line');
            
            $blocks[] = [
                'title' => (count($blocks) == 0 ? "Last View: " : "Previous View: ") . (self::get_button_definitions()[$h->chart_type]['label'] ?? 'History'),
                'content' => $OUTPUT->render($chart)
            ];
        }
        return $blocks;
    }

    public static function save_view_history($userid, $type, $render) {
        global $DB;
        $record = (object)['userid' => $userid, 'chart_type' => $type, 'render_type' => $render, 'timecreated' => time(), 'data_snapshot' => ''];
        return $DB->insert_record('local_chartplugin_history', $record);
    }

    public static function get_ai_performance_delta($userid) {
        global $DB;
        $u = $DB->get_field_sql("SELECT AVG(COALESCE(finalgrade, rawgrade)) FROM {grade_grades} WHERE userid = ?", [$userid]);
        $s = $DB->get_field_sql("SELECT AVG(COALESCE(finalgrade, rawgrade)) FROM {grade_grades}");
        if (!$u || !$s) return "AI Insight: Synchronizing data...";
        $diff = (($u - $s) / $s) * 100;
        return ($diff < 0) ? "AI Alert: You are ".number_format(abs($diff), 2)."% below site average. Suggested: Engage Recovery Plan." : "AI Insight: You are ".number_format($diff, 2)."% above average.";
    }
}