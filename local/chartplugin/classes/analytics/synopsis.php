<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

use core\chart_base;
use core\chart_series;

class synopsis {

    /**
     * Builds the main chart using real database values.
     */
    public static function build_dynamic_chart($type, $is_sidebar = false) {
        global $USER;
        
        $userid = $USER->id;
        
        // Route to the new innovative view if the 'plan' button is clicked.
        if ($type === 'plan') {
            return self::build_learning_plan_chart($userid);
        }

        // Use a line chart for sidebars to maintain "Stable Beauty" aesthetic.
        $chart = $is_sidebar ? new \core\chart_line() : new \core\chart_bar();
        
        if ($type === 'lowest') {
            // FIX: Added backslash to call global function from lib.php
            $data_records = \local_chartplugin_get_lowest_courses($userid);
        } else {
            $data_records = self::get_all_user_grades($userid);
        }

        $labels = [];
        $values = [];

        foreach ($data_records as $record) {
            $labels[] = $record->itemname ?: 'Course Total';
            $values[] = (float)$record->finalgrade;
        }

        $series = new chart_series('Performance (%)', $values);
        
        // Visual polish for sidebars
        if ($is_sidebar) {
            $series->set_color('#6f42c1'); // Distinct purple for history
            if ($chart instanceof \core\chart_line) {
                $series->set_smooth(true);
            }
        }

        $chart->add_series($series);
        $chart->set_labels($labels);

        return $chart;
    }

    /**
     * Innovative Personalization: Gap Analysis for Learning Plans.
     */
    public static function build_learning_plan_chart($userid = 2) {
        global $DB;
        
        $plan = $DB->get_record('competency_plan', ['userid' => $userid, 'status' => 1], '*', IGNORE_MULTIPLE);
        $chart = new \core\chart_bar();
        
        if (!$plan) {
            $chart->set_title('Status: All Competencies On Track');
            $series = new \core\chart_series('No Plan Needed', [100]);
            $chart->add_series($series);
            $chart->set_labels(['Current Standing']);
        } else {
            $prediction = $DB->get_field('local_chartplugin_trends', 'prediction', ['userid' => $userid]) ?: 0;
            
            $chart->set_title('AI Gap Analysis: Mastery vs. Goal');
            
            $series1 = new \core\chart_series('ML Forecasted Mastery', [(float)$prediction]);
            $series1->set_color('#dc3545'); 
            
            $series2 = new \core\chart_series('Target Proficiency', [85]); 
            $series2->set_color('#28a745'); 
            
            $chart->add_series($series1);
            $chart->add_series($series2);
            $chart->set_labels(['Recovery Target: ' . $plan->name]);
        }

        return $chart;
    }

    public static function build_trend_chart($userid = 2) {
        global $DB;
        $chart = new \core\chart_line();
        $record = $DB->get_record('local_chartplugin_trends', ['userid' => $userid]);
        $data = ($record && !empty($record->monthly_trend)) ? json_decode($record->monthly_trend, true) : [];

        if (!empty($data)) {
            $series = new \core\chart_series('Your Progress', array_values($data));
            $series->set_color('#dc3545'); 
            $series->set_smooth(true);
            $chart->add_series($series);
            $chart->set_labels(array_keys($data));
        } else {
            $series = new \core\chart_series('No Data Found', [0,0,0,0,0,0]);
            $chart->add_series($series);
        }
        return $chart;
    }

    private static function get_all_user_grades($userid) {
        global $DB;
        $sql = "SELECT gi.id, gi.itemname, gg.finalgrade 
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gg.itemid = gi.id
                WHERE gg.userid = :userid AND gi.itemtype = 'course'";
        return $DB->get_records_sql($sql, ['userid' => $userid]);
    }
}