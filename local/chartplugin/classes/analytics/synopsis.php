<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

use core\chart_bar;
use core\chart_series;

class synopsis {

    public static function build_dynamic_chart($type, $is_mini = false) {
        global $DB;
        
        $chart = new chart_bar();
        $labels = [];
        $values = [];
        $series_label = "Data";
        $params = [];
        
        // Time window: 30 days
        $thirtydaysago = time() - (30 * 24 * 60 * 60);

        // UI Adjustments for Thumbnails
        if ($is_mini) {
            $chart->set_legend_options(['display' => false]);
            // Simplified view for sidebar
        }

        switch ($type) {
            case 'best':
                $sql = "SELECT c.shortname, cs.avggrade as value 
                        FROM {local_chartplugin_course_stats} cs
                        JOIN {course} c ON c.id = cs.courseid
                        ORDER BY value DESC LIMIT 5";
                break;

            case 'lowest':
                $sql = "SELECT c.shortname, cs.avggrade as value 
                        FROM {local_chartplugin_course_stats} cs
                        JOIN {course} c ON c.id = cs.courseid
                        ORDER BY value ASC LIMIT 5";
                break;

            case '30day':
                $sql = "SELECT c.shortname, COUNT(ue.id) as value
                        FROM {course} c
                        JOIN {enrol} e ON e.courseid = c.id
                        JOIN {user_enrolments} ue ON ue.enrolid = e.id
                        WHERE ue.timecreated >= ?
                        GROUP BY c.id, c.shortname ORDER BY value DESC LIMIT 8";
                $params = [$thirtydaysago];
                break;

            case 'freq':
                $sql = "SELECT DAYNAME(FROM_UNIXTIME(timecreated)) as shortname, COUNT(id) as value
                        FROM {logstore_standard_log}
                        WHERE timecreated >= ?
                        GROUP BY shortname
                        ORDER BY FIELD(shortname, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
                $params = [$thirtydaysago];
                break;

            case 'style':
                $sql = "SELECT 
                            CASE 
                                WHEN component IN ('mod_resource', 'mod_book') THEN 'Reading'
                                WHEN component IN ('mod_quiz', 'mod_hvp') THEN 'Interactive'
                                WHEN component IN ('mod_forum', 'mod_glossary') THEN 'Social'
                                ELSE 'Other'
                            END as shortname, COUNT(id) as value
                        FROM {logstore_standard_log}
                        WHERE timecreated >= ?
                        GROUP BY shortname ORDER BY value DESC";
                $params = [$thirtydaysago];
                break;

            case 'cohort':
                $sql = "SELECT ch.name as shortname, COUNT(cm.id) as value
                        FROM {cohort} ch
                        JOIN {cohort_members} cm ON cm.cohortid = ch.id
                        GROUP BY ch.id, ch.name LIMIT 8";
                break;

            case 'plan':
                $sql = "SELECT lp.name as shortname, COUNT(lpc.id) as value
                        FROM {competency_plan} lp
                        LEFT JOIN {competency_plancomp} lpc ON lpc.planid = lp.id
                        GROUP BY lp.id, lp.name LIMIT 8";
                break;

            default: // synopsis_total
                $sql = "SELECT c.shortname, COUNT(ue.id) as value
                        FROM {course} c
                        JOIN {enrol} e ON e.courseid = c.id
                        JOIN {user_enrolments} ue ON ue.enrolid = e.id
                        GROUP BY c.id, c.shortname ORDER BY value DESC LIMIT 8";
                break;
        }

        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            foreach ($records as $record) {
                $labels[] = $record->shortname;
                $values[] = (float)$record->value;
            }
        }

        $chart->set_labels($labels);
        $series = new chart_series($series_label, $values);
        $chart->add_series($series);
        
        return $chart;
    }
}
