<?php
declare(strict_types=1);

use core\chart_bar;
use core\chart_series;

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/chartplugin:viewanalytics', $context);

/**
 * PAGE API — Core Moodle bootstrap
 */
$PAGE->set_context($context);
$PAGE->set_url('/local/chartplugin/analytics.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title('Learning Analytics Dashboard');
$PAGE->set_heading('Learning Analytics Dashboard');

$PAGE->requires->css('/local/chartplugin/styles.css');
$PAGE->requires->js_call_amd('local_chartplugin/dashboard', 'init');

echo $OUTPUT->header();
?>

<div class="analytics-header">
    <div class="header-buttons" id="header-buttons">
        <div class="button active" data-chart="synopsis">Synopsis (to date)</div>
        <div class="button" data-chart="best-courses">Best Courses</div>
        <div class="button" data-chart="cohort">Cohort Performance</div>
        <div class="button wide" data-chart="best-plan">Best Learning Plan</div>

        <div class="button" data-chart="synopsis-30">30-Day Synopsis</div>
        <div class="button" data-chart="worst-courses">Lowest Courses</div>
        <div class="button" data-chart="frequency">Study Frequency</div>
        <div class="button wide" data-chart="preferred-style">Preferred Learning Style</div>
    </div>
</div>

<div class="analytics-layout">

    <!-- MAIN CHART PANEL -->
    <div class="analytics-main">
        <div class="generalbox">
            <h3 id="chart-title">Synopsis (to date)</h3>

            <?php
            $sql = "
                SELECT c.shortname, s.avggrade
                  FROM {local_chartplugin_course_stats} s
                  JOIN {course} c ON c.id = s.courseid
              ORDER BY c.shortname
            ";

            $records = $DB->get_records_sql($sql);

            $labels = [];
            $values = [];

            foreach ($records as $r) {
                $labels[] = $r->shortname;
                $values[] = (float)$r->avggrade;
            }

            if ($labels) {
                $chart = new chart_bar();
                $chart->set_title('Average Grade by Course');

                $series = new chart_series('Average grade', $values);
                $chart->add_series($series);
                $chart->set_labels($labels);

                echo $OUTPUT->render($chart);
            } else {
                echo html_writer::div(
                    'No analytics data available yet.',
                    'alert alert-info'
                );
            }
            ?>
        </div>
    </div>

    <!-- RIGHT-HAND ANALYTICS BLOCKS -->
    <div class="analytics-blocks">

        <div class="block block-chart-last">
            <h5>Last Chart</h5>
            <div class="block-content" data-role="last-chart">
                <em>No chart selected yet</em>
            </div>
        </div>

        <div class="block block-chart-previous">
            <h5>Previous Chart</h5>
            <div class="block-content" data-role="previous-chart">
                <em>No chart selected yet</em>
            </div>
        </div>

        <div class="block block-chart-ai">
            <h5>Advanced Analytics</h5>
            <p class="muted">
                Ask complex questions or view AI-generated insights.
                (Coming soon)
            </p>
            <input type="text" disabled placeholder="e.g. Why is my cohort underperforming?" />
            <button disabled>Analyse</button>
        </div>

    </div>
</div>

<?php
echo $OUTPUT->footer();
