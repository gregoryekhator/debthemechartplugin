<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

use core\chart_base;
use core\chart_series;

class synopsis {
    /**
     * Builds a chart using real database values.
     */
    public static function build_dynamic_chart($type, $is_sidebar = false) {
        global $USER;
        
        // 1. Initialize the Moodle Chart API
        $chart = new \core\chart_bar();
        
        // 2. Fetch the data using our new lib.php logic
        $userid = $USER->id;
        
        if ($type === 'lowest') {
            $data_records = local_chartplugin_get_lowest_courses($userid);
        } else {
            // Default/Synopsis logic: For now, we'll pull all course grades
            $data_records = self::get_all_user_grades($userid);
        }

        $labels = [];
        $values = [];

        foreach ($data_records as $record) {
            $labels[] = $record->itemname ?: 'Course Total';
            $values[] = (float)$record->finalgrade;
        }

        // 3. Populate the Chart Series
        $series = new chart_series('Performance (%)', $values);
        //$series->set_type(chart_series::TYPE_BAR);
        $chart->add_series($series);
        $chart->set_labels($labels);

        // 4. Handle Sidebar Scaling (thumbnail mode)
        if ($is_sidebar) {
            $chart->set_title(''); // Cleaner look for sidebar
        }

        return $chart;
    }

    public static function build_trend_chart() {
    $chart = new \core\chart_line(); // Switches from Bar to Line
    $series = new \core\chart_series('Your Progress', [78, 82, 85, 80, 75, 65]);
    $chart->add_series($series);
    $chart->set_labels(['Sept', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb']);
    return $chart;
    }

    /**
     * Helper to get all course-level grades for the user.
     */
    private static function get_all_user_grades($userid) {
        global $DB;
        $sql = "SELECT gi.id, gi.itemname, gg.finalgrade 
                FROM {grade_grades} gg
                JOIN {grade_items} gi ON gg.itemid = gi.id
                WHERE gg.userid = :userid AND gi.itemtype = 'course'";
        return $DB->get_records_sql($sql, ['userid' => $userid]);
    }
}