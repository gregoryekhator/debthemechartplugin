<?php
namespace local_chartplugin\analytics;

/**
 * miner.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class miner {
    
    // Function 1: Mines student performance (Success Prediction)
    public static function export_to_csv($userid) {
        global $DB;
        $sql = "SELECT gi.courseid, g.finalgrade as grade, 
                       (SELECT COUNT(*) FROM {logstore_standard_log} l 
                        WHERE l.userid = g.userid AND l.courseid = gi.courseid) as activity_count
                FROM {grade_grades} g
                JOIN {grade_items} gi ON g.itemid = gi.id
                WHERE g.userid = :userid 
                  AND g.finalgrade IS NOT NULL 
                  AND gi.courseid IS NOT NULL";

        $records = $DB->get_records_sql($sql, ['userid' => $userid]);
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

    // Function 2: Mines Question Bank content (Bulk Course Scaling)
    public static function mine_lesson_content($categoryid) {
        global $DB;

        // This query explicitly joins the versioning tables required by Moodle 4.0+
        $sql = "SELECT q.id, q.name, q.questiontext 
                FROM {question} q
                JOIN {question_versions} qv ON qv.questionid = q.id
                JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                WHERE qbe.questioncategoryid = :categoryid 
                  AND q.qtype = 'description'";

        return $DB->get_records_sql($sql, ['categoryid' => (int)$categoryid]);
    }
}