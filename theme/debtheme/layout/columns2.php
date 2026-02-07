<?php
defined('MOODLE_INTERNAL') || die();
echo '<!-- USING columns2.php -->';

echo $OUTPUT->doctype();
?>
<html lang="<?php echo current_language(); ?>">
<head>
    <title><?php echo $SITE->fullname; ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<div class="debtheme-container">

    <?php
    echo $OUTPUT->render_from_template(
        'theme_debtheme/custom_header',
        []
    );
    ?>

    <div id="page-content" class="row mt-4">
        <main id="region-main" class="col-md-8">
            <?php echo $OUTPUT->main_content(); ?>
        </main>

        <?php if ($OUTPUT->blocks('side-pre')): ?>
            <aside id="region-side-pre" class="col-md-2">
                <?php echo $OUTPUT->blocks('side-pre'); ?>
            </aside>
        <?php endif; ?>

<?php if ($OUTPUT->blocks('side-post')): ?>
    <aside id="region-side-post" class="col-md-2">
        <?php echo $OUTPUT->blocks('side-post'); ?>
    </aside>
<?php endif; ?>
    </div>

    <?php
    echo $OUTPUT->render_from_template(
        'theme_debtheme/custom_footer',
        theme_debtheme_get_footer_context()
    );
    ?>

</div>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>

