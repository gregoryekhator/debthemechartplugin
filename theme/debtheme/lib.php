<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Load SCSS for Debtheme (inherits Boost).
 */
function theme_debtheme_get_main_scss_content($theme) {
    global $CFG;

    require_once($CFG->dirroot . '/theme/boost/lib.php');

    $scss = theme_boost_get_main_scss_content($theme);

    $customscss = $CFG->dirroot . '/theme/debtheme/scss/styles.scss';
    if (file_exists($customscss)) {
        $scss .= "\n\n/* Debtheme overrides */\n";
        $scss .= file_get_contents($customscss);
    }

    return $scss;
}

/**
 * Footer context for custom footer mustache.
 */
function theme_debtheme_get_footer_context() {
    return [
        'brandorganization_footer' => 'Debonair Training Ltd',
        'footnote' => 'Our mission is to harness digital learning technology and 21st-century pedagogy to deliver measurable excellence.',
        'brandwebsite_footer' => 'www.debonairtraining.com',
        'brandemail_footer' => 'info@debonairtraining.com',
        'brandphone_footer' => '+234 80 2228 8685 OR +44 7828 151901',
        'year' => date('Y'),
    ];
}


