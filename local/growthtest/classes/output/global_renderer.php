<?php
namespace local_growthtest\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use core\{chart_line, chart_series};

class global_renderer extends plugin_renderer_base {

    /**
     * The only method we need to test if charts draw.
     */
    public function display_growth_chart() {
        $chart = new chart_line();
        $chart->set_title('Lab Test: Moodle 4.5.7 Core Chart');
        
        $series = new chart_series('Test Data', [10, 45, 20, 80, 35]);
        $chart->add_series($series);
        $chart->set_labels(['Mon', 'Tue', 'Wed', 'Thu', 'Fri']);
        
        return $this->output->render($chart);
    }
}