<?php
defined('MOODLE_INTERNAL') || die();

global $CFG, $OUTPUT, $PAGE;

$user_level = 'freemium'; 
$user_level_label = strtoupper($user_level);
$currenttype = optional_param('type', 'synopsis', PARAM_TEXT);

// Nav logic for blue banner
$raw_items = [
    ['id' => 'synopsis', 'name' => 'Synopsis (to date)', 'req' => 'free'],
    ['id' => 'best', 'name' => 'Best Courses', 'req' => 'free'],
    ['id' => 'cohort', 'name' => 'Cohort Performance', 'req' => 'free'], 
    ['id' => 'best_plan', 'name' => 'Best Learning Plan', 'req' => 'enterprise'],
    ['id' => 'synopsis_30', 'name' => '30-Day Synopsis', 'req' => 'free'],
    ['id' => 'lowest_courses', 'name' => 'Lowest Courses', 'req' => 'free'],
    ['id' => 'frequency', 'name' => 'Study Frequency', 'req' => 'free'], 
    ['id' => 'style', 'name' => 'Preferred Style', 'req' => 'enterprise'],
];

$processed_nav = [];
foreach ($raw_items as $item) {
    $lock = ($item['req'] === 'enterprise') ? ' 🔒' : '';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { background: #f4f7f6 !important; }
        .deb-navbar { background: white; border-bottom: 1px solid #dee2e6; height: 60px; display: flex; align-items: center; padding: 0 5%; position: sticky; top: 0; z-index: 1050; }
        .deb-banner { background: #007bff; color: white; border-radius: 20px 20px 0 0; padding: 40px; margin: 20px 5% 0 5%; position: relative; }
        .deb-main-rect { background: white; border: 1px solid #dee2e6; border-top: none; margin: 0 5% 20px 5%; padding: 30px; display: flex; gap: 30px; min-height: 500px; }
        
        /* Footer Locked Styling (Image 2) */
        .deb-footer-custom { background: #007bff; color: white; padding: 60px 5% 20px 5%; border-radius: 20px; margin: 20px 5% 40px 5%; }
        .footer-socials { border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 20px; margin-bottom: 40px; display: flex; justify-content: space-between; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 50px; }
        .footer-logo-text h2 { font-size: 2rem; font-weight: bold; margin-bottom: 15px; }
        .footer-contact h5 { font-weight: bold; text-transform: uppercase; margin-bottom: 20px; }
    </style>
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<div id="page-wrapper">
    <header class="deb-navbar">
        <img src="<?php echo $OUTPUT->get_logo_url(); ?>" style="height: 35px;">
        <div class="ml-auto"><?php echo $OUTPUT->user_menu(); ?></div>
    </header>

    <div id="page">
        <div class="deb-banner">
            <h1 class="h2 font-weight-bold">Debonair Training A.I. Dashboard</h1>
            <?php echo $OUTPUT->render_from_template('theme_debtheme/custom_header', $template_context); ?>
        </div>

        <div id="page-content" class="deb-main-rect" data-region="main">
            <section id="region-main" style="flex: 3;">
                <?php echo $OUTPUT->main_content(); ?>
            </section>
            <aside id="block-region-side-pre" style="flex: 1;">
                <?php echo $OUTPUT->blocks('side-pre'); ?>
            </aside>
        </div>

        <footer class="deb-footer-custom">
            <div class="footer-socials">
                <span>Get connected with us on social networks:</span>
                <div><i class="fa fa-facebook mx-2"></i> <i class="fa fa-twitter mx-2"></i> <i class="fa fa-linkedin mx-2"></i></div>
            </div>
            <div class="footer-grid">
                <div>
                    <h2>Debonair Training Ltd</h2>
                    <p>Our mission is to harness digital learning technology and 21st century pedagogical strategies to implement our clients digital business plan for desired results...</p>
                </div>
                <div class="footer-contact">
                    <h5>Contact</h5>
                    <p><i class="fa fa-globe"></i> www.debonairtraining.com</p>
                    <p><i class="fa fa-envelope"></i> info@debonairtraining.com</p>
                    <p><i class="fa fa-phone"></i> +234 80 2228 8685</p>
                </div>
            </div>
            <div class="text-center mt-4 border-top pt-3">© 2002 - 2024 Copyright: Debonair Training Ltd</div>
            <div class="d-none"><?php echo $OUTPUT->standard_footer_html(); ?></div>
        </footer>
    </div>
</div>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>