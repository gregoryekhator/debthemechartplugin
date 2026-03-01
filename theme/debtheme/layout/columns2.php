<?php
defined('MOODLE_INTERNAL') || die();

global $OUTPUT, $PAGE;

$deb_renderer = $PAGE->get_renderer('theme_debtheme');

// 1. REPLICATING BOOST PRIMARY NAV
$primarynav = $PAGE->primarynav;
$p_menu = new \core\navigation\output\more_menu($primarynav, 'nav-tabs');
$p_menudata = $p_menu->export_for_template($OUTPUT);

// 2. REPLICATING BOOST SECONDARY NAV (Fixes Site Admin)
$secondarynav = $PAGE->secondarynav;
$s_menudata = null;
if ($secondarynav && $secondarynav->has_children()) {
    $s_menu = new \core\navigation\output\more_menu($secondarynav, 'nav-tabs');
    $s_menudata = $s_menu->export_for_template($OUTPUT);
}

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $PAGE->title; ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>
</head>
<body id="<?php echo $PAGE->bodyid; ?>" class="<?php echo $PAGE->bodyclasses; ?>">
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<div id="page-wrapper" class="d-flex flex-column">

    <nav class="fixed-top navbar navbar-light bg-white navbar-expand border-bottom px-3" id="main-nav">
        <div class="container-fluid">
            <div class="primary-navigation mr-auto">
                <?php echo $OUTPUT->render_from_template('core/moremenu', $p_menudata); ?>
            </div>
            <div class="ml-auto d-flex align-items-center">
                <?php echo $OUTPUT->user_menu(); ?>
            </div>
        </div>
    </nav>

    <div id="page" class="container-fluid drawer-page-content" style="margin-top: 80px;">
        
        <?php if ($s_menudata): ?>
            <div class="secondary-navigation d-print-none mb-3">
                <?php echo $OUTPUT->render_from_template('core/moremenu', $s_menudata); ?>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-12">
                <?php echo $deb_renderer->render_from_template('theme_debtheme/custom_header', $deb_renderer->get_flight_deck_context()); ?>
            </div>
        </div>

        <div id="page-content" class="row">
            <div id="region-main-box" class="col-12">
                <section id="region-main" data-region="main" aria-label="<?php echo get_string('content'); ?>">
                    <?php echo $OUTPUT->main_content(); ?>
                </section>
            </div>
        </div>
    </div>

    <div id="nav-drawer" class="d-none" aria-hidden="true" data-region="drawer">
        <div class="drawer-content"></div>
    </div>

    <footer id="page-footer">
        <?php echo $deb_renderer->render_from_template('theme_debtheme/custom_footer', theme_debtheme_get_footer_context()); ?>
    </footer>
</div>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>