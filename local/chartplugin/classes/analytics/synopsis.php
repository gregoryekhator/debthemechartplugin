<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();
use core\chart_series;

class synopsis {

    public static function build_dynamic_chart($type, $is_sidebar = false) {
        global $USER; $userid = $USER->id;

        if ($type === 'plan') return self::build_learning_plan_chart($userid);
        if ($type === 'style') return self::build_learning_style_chart($userid);
        if ($type === 'cohort' && !$is_sidebar) return self::build_distribution_chart();

        $chart = $is_sidebar ? new \core\chart_line() : new \core\chart_bar();
        $data_records = ($type === 'lowest') ? \local_chartplugin_get_lowest_courses($userid) : self::get_all_user_grades($userid);

        if (!$is_sidebar) {
            $header = ($type === 'lowest') ? "Recovery Focus: Mouse over to identify critical gaps." : "Cockpit Synopsis: Your standing relative to the cohort.";
            $chart->set_title($header);
        }

        $labels = []; $user_vals = []; $cohort_vals = [];
        foreach ($data_records as $record) {
            $labels[] = $record->itemname ?: 'Course';
            $user_vals[] = (float)$record->finalgrade;
            $cohort_vals[] = (float)\local_chartplugin_get_cohort_average($record->courseid);
        }

        $s1 = new chart_series('My Performance (%)', $user_vals);
        $s1->set_color('#6f42c1'); $chart->add_series($s1);

        if (!$is_sidebar) {
            $s2 = new chart_series('Class Average (%)', $cohort_vals);
            $s2->set_color('#ced4da'); $chart->add_series($s2);
        }

        $chart->set_labels($labels);
        return $chart;
    }

    public static function build_distribution_chart() {
        $chart = new \core\chart_bar();
        $chart->set_title("Enterprise Insight: Grade Distribution Curve (Mouse over for volume)");
        $dist = \local_chartplugin_get_grade_distribution(2); // Example course
        $series = new chart_series('Number of Students', array_values($dist));
        $series->set_color('#007bff');
        $chart->add_series($series);
        $chart->set_labels(array_keys($dist));
        return $chart;
    }

    public static function build_learning_style_chart($userid) {
        $chart = new \core\chart_line();
        $chart->set_title('AI Learning Profile: Multi-Modal Engagement Spider-Web');
        $series = new chart_series('Preference Score', [85, 40, 70, 55, 90]);
        $series->set_color('#007bff'); $series->set_fill(true); $series->set_smooth(true);
        $chart->add_series($series);
        $chart->set_labels(['Visual', 'Auditory', 'Social', 'Reflective', 'Kinesthetic']);
        return $chart;
    }

    public static function build_learning_plan_chart($userid) {
        global $DB;
        $plan = $DB->get_record('competency_plan', ['userid' => $userid, 'status' => 1], '*', IGNORE_MULTIPLE);
        $chart = new \core\chart_bar();
        if (!$plan) {
            $chart->add_series(new chart_series('On Track', [100]));
            $chart->set_labels(['Standing']);
        } else {
            $pred = $DB->get_field('local_chartplugin_trends', 'prediction', ['userid' => $userid]) ?: 0;
            $s1 = new chart_series('ML Forecasted Mastery', [(float)$pred]); $s1->set_color('#dc3545');
            $s2 = new chart_series('Target Proficiency', [85]); $s2->set_color('#28a745');
            $chart->add_series($s1); $chart->add_series($s2);
            $chart->set_labels(['Target: ' . $plan->name]);
        }
        return $chart;
    }

    public static function build_trend_chart($userid) {
        global $DB; $chart = new \core\chart_line();
        $record = $DB->get_record('local_chartplugin_trends', ['userid' => $userid]);
        $data = ($record && !empty($record->monthly_trend)) ? json_decode($record->monthly_trend, true) : [];
        if (!empty($data)) {
            $series = new chart_series('Your Progress', array_values($data));
            $series->set_color('#dc3545'); $series->set_smooth(true);
            $chart->add_series($series); $chart->set_labels(array_keys($data));
        }
        return $chart;
    }

    private static function get_all_user_grades($userid) {
        global $DB;
        $sql = "SELECT gi.id, gi.courseid, gi.itemname, gg.finalgrade FROM {grade_grades} gg
                JOIN {grade_items} gi ON gg.itemid = gi.id WHERE gg.userid = :userid AND gi.itemtype = 'course'";
        return $DB->get_records_sql($sql, ['userid' => $userid]);
    }
}