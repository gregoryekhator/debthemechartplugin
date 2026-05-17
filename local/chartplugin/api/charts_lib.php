<?php
/**
 * charts_lib.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/charts_lib.php');

function chart_synopsis(): array {
    global $DB;

    $sql = "
        SELECT
            c.shortname AS label,
            AVG(s.avggrade) AS value
        FROM {local_chartplugin_course_stats} s
        JOIN {course} c ON c.id = s.courseid
        GROUP BY c.shortname
        ORDER BY c.shortname
    ";

    $records = $DB->get_records_sql($sql);

    return [
        'type'   => 'bar',
        'title'  => 'Average Performance by Course',
        'labels' => array_column($records, 'label'),
        'series' => [
            [
                'name' => 'Average grade',
                'data' => array_map('floatval', array_column($records, 'value')),
            ],
        ],
    ];
}