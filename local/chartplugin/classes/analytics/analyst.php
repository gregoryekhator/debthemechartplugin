<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

class analyst {
    public static function get_dynamic_chart($type = 'synopsis') {
        $chart = new \core\chart_bar();
        
        switch ($type) {
            case 'best':
                $data = [95, 88, 72, 60, 55, 40];
                $labels = ['Excel Pro', 'Leadership', 'Safety 101', 'Fire Safety', 'First Aid', 'Ethics'];
                $color = '#28a745'; // Green for 'Best'
                break;
            case '30day':
                $data = [12, 45, 67, 23, 89, 34];
                $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'];
                $color = '#ffc107'; // Yellow for '30 Day'
                break;
            case 'worst':
                $data = [5, 12, 18, 22, 25, 30];
                $labels = ['Physics', 'Advanced Calc', 'Quantum', 'Bio-Chem', 'Logic', 'Statics'];
                $color = '#dc3545'; // Red for 'Worst'
                break;
            default:
                $data = [100, 2, 3, 1, 1, 1];
                $labels = ['testcourse_3', 'testcourse_1', 'testcourse_2', 'ENGLISH', 'MATHS', 'SCIENCE'];
                $color = '#8e5ea2'; // Purple
                break;
        }

        $series = new \core\chart_series('Performance', $data);
        $series->set_color($color);
        $chart->add_series($series);
        $chart->set_labels($labels);
        return $chart;
    }
}
