<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();
use core\chart_series;

class synopsis {

    /**
     * The Master Menu Definition
     */
    public static function get_button_definitions() {
        return [
            'synopsis' => 'Synopsis (to date)', 
            'best'     => 'Best Courses',
            'cohort'   => 'Cohort Performance', 
            'plan'     => 'Best Learning Plan',
            '30day'    => '30 Day Synopsis', 
            'lowest'   => 'Lowest Courses',
            'freq'     => 'Study Frequency', 
            'style'    => 'Preferred Learning Style'
        ];
    }

    public static function get_template_data($userid, $current_type, $render) {
        $buttons = [];
        foreach (self::get_button_definitions() as $type => $label) {
            $buttons[] = [
                'label' => $label,
                'url' => (new \moodle_url('/local/chartplugin/index.php', ['type' => $type, 'render' => $render]))->out(false),
                'active' => ($type === $current_type)
            ];
        }

        return [
            'buttons' => $buttons,
            'ai_hero_text' => 'Alert: You are 10.67% below average. ML Forecast: Next month\'s predicted grade is 67%.',
        ];
    }

    public static function build_dynamic_chart($type, $is_sidebar = false, $render_type = 'bar') {
        global $USER;
        
        // Factory Routing
        if ($type === 'plan') return self::build_learning_plan_chart($USER->id, $render_type);
        if ($type === 'style') return self::build_learning_style_chart($USER->id, $render_type);
        if ($type === 'cohort' && !$is_sidebar) return self::build_distribution_chart($render_type);
        if ($type === 'freq') return self::build_study_frequency_chart($USER->id, $render_type);

        $chart = self::get_chart_instance($render_type, $is_sidebar);
        
        // Mock Data for Synopsis/30day/Best/Lowest
        $labels = ['Course A', 'Course B', 'Course C'];
        $user_vals = ($type === 'lowest') ? [40, 45, 50] : [75, 82, 68];
        
        $s1 = new chart_series('My Performance (%)', $user_vals);
        $s1->set_color('#6f42c1'); 
        $chart->add_series($s1);
        
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

    public static function build_study_frequency_chart($userid, $render_type = 'pie') {
        $chart = self::get_chart_instance($render_type, false);
        $chart->add_series(new chart_series('Work Rate', [45, 30, 25]));
        $chart->set_labels(['Active', 'Reflective', 'Practical']);
        return $chart;
    }

    public static function build_distribution_chart($render_type = 'bar') {
        $chart = self::get_chart_instance($render_type, false);
        $dist = \local_chartplugin_get_grade_distribution(2);
        $chart->add_series(new chart_series('Students', array_values($dist)));
        $chart->set_labels(array_keys($dist));
        return $chart;
    }

    public static function build_learning_style_chart($userid, $render_type = 'line') {
        $chart = self::get_chart_instance($render_type, false);
        $series = new chart_series('Preference', [85, 40, 70, 55, 90]);
        if ($render_type === 'line') $series->set_fill(true);
        $chart->add_series($series);
        $chart->set_labels(['Visual', 'Auditory', 'Social', 'Reflective', 'Kinesthetic']);
        return $chart;
    }

    public static function build_learning_plan_chart($userid, $render_type = 'bar') {
        $chart = self::get_chart_instance($render_type, false);
        $chart->add_series(new chart_series('ML Forecast', [67]));
        $chart->add_series(new chart_series('Target', [85]));
        $chart->set_labels(['Current Plan Mastery']);
        return $chart;
    }
}