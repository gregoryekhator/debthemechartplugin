<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $OUTPUT, $PAGE;

// 1. ENSURE VARIABLES ARE ALWAYS DEFINED (Fixes the crash in Image 2)
$user_level = 'freemium'; // This will eventually come from your DB
$user_level_label = strtoupper($user_level);
$currenttype = optional_param('type', 'synopsis', PARAM_TEXT);

// 2. PRE-PROCESS NAV (Same as before)
$raw_items = [
    ['id' => 'synopsis', 'name' => 'Synopsis (to date)', 'req' => 'free'],
    ['id' => 'best', 'name' => 'Best Courses', 'req' => 'free'],
    ['id' => 'cohort', 'name' => 'Cohort Performance', 'req' => 'premium'],
    ['id' => 'best_plan', 'name' => 'Best Learning Plan', 'req' => 'enterprise'],
    ['id' => 'synopsis_30', 'name' => '30-Day Synopsis', 'req' => 'free'],
    ['id' => 'lowest_courses', 'name' => 'Lowest Courses', 'req' => 'free'],
    ['id' => 'frequency', 'name' => 'Study Frequency', 'req' => 'premium'],
    ['id' => 'style', 'name' => 'Preferred Style', 'req' => 'enterprise'],
];

$processed_nav = [];
foreach ($raw_items as $item) {
    $lock = ($item['req'] !== 'free' && $user_level === 'freemium') ? ' 🔒' : '';
    $processed_nav[] = [
        'url' => $CFG->wwwroot . '/local/chartplugin/index.php?type=' . $item['id'],
        'display_name' => $item['name'] . $lock,
        'is_active' => ($currenttype === $item['id'])
    ];
}

$template_context = ['nav_links' => $processed_nav];

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $PAGE->title; ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<div id="page-wrapper">
    <nav class="navbar navbar-light bg-white border-bottom px-5 py-2 mb-3">
        <img src="<?php echo $OUTPUT->get_logo_url(); ?>" style="height: 30px;">
        <div class="ml-auto"><?php echo $OUTPUT->user_menu(); ?></div>
    </nav>

    <div id="page" class="container-fluid px-5">
        <div id="page-header" class="d-none"><?php echo $OUTPUT->full_header(); ?></div>

        <div class="deb-banner" style="background: #007bff; color: white; border-radius: 20px 20px 0 0; padding: 40px; position: relative;">
            <span style="position: absolute; top: 20px; right: 40px; background: white; color: #007bff; padding: 5px 15px; border-radius: 20px; font-weight: bold;">
                <?php echo $user_level_label; ?>
            </span>
            <h1 class="h2 font-weight-bold">Debonair Training A.I. Dashboard</h1>
            <?php echo $OUTPUT->render_from_template('theme_debtheme/custom_header', $template_context); ?>
        </div>

        <div id="page-content" class="row bg-white border border-top-0 m-0 p-4" data-region="main">
            <section id="region-main" class="col-md-9">
                <?php echo $OUTPUT->main_content(); ?>
            </section>

            <aside id="block-region-side-pre" class="col-md-3 block-region" data-blockregion="side-pre">
                <?php echo $OUTPUT->blocks('side-pre'); ?>
            </aside>
        </div>

        <footer class="footer-rect" style="background: #007bff; color: white; padding: 40px; border-radius: 0 0 20px 20px; margin-bottom: 40px;">
            <div class="row">
                <div class="col-md-6"><strong>Debonair Training Ltd</strong></div>
                <div class="col-md-6 text-right">© 2002 - 2026</div>
            </div>
            <div class="d-none"><?php echo $OUTPUT->standard_footer_html(); ?></div>
        </footer>
    </div>
</div>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>