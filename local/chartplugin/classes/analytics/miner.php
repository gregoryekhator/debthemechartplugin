<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

class miner {
    public static function export_to_csv($userid) {
        global $DB;

        // Mining grades and activity logs
        $sql = "SELECT gi.courseid, g.finalgrade as grade, 
                       (SELECT COUNT(*) FROM {logstore_standard_log} l 
                        WHERE l.userid = g.userid AND l.courseid = gi.courseid) as activity_count
                FROM {grade_grades} g
                JOIN {grade_items} gi ON g.itemid = gi.id
                WHERE g.userid = :userid 
                  AND g.finalgrade IS NOT NULL 
                  AND gi.courseid IS NOT NULL";

        $records = $DB->get_records_sql($sql, ['userid' => $userid]);

        // Creating the file in Moodle's temp folder
        $filename = "user_data_mine.csv";
        $path = make_temp_directory('chartplugin');
        $fullpath = $path . '/' . $filename;

        $fp = fopen($fullpath, 'w');
        fputcsv($fp, ['courseid', 'grade', 'activity_count']);

        foreach ($records as $r) {
            fputcsv($fp, [$r->courseid, $r->grade, $r->activity_count]);
        }

        fclose($fp);
        return $fullpath;
    }
}