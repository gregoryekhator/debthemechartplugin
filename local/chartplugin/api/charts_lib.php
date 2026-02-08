<?php
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
