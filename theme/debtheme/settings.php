<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading(
        'theme_debtheme/footerheading',
        get_string('footerheading', 'theme_debtheme'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'theme_debtheme/organisation',
        get_string('organisation', 'theme_debtheme'),
        '',
        'Debonair Training Ltd'
    ));

    $settings->add(new admin_setting_configtextarea(
        'theme_debtheme/mission',
        get_string('mission', 'theme_debtheme'),
        '',
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'theme_debtheme/website',
        get_string('website', 'theme_debtheme'),
        '',
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'theme_debtheme/email',
        get_string('email', 'theme_debtheme'),
        '',
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'theme_debtheme/phone',
        get_string('phone', 'theme_debtheme'),
        '',
        ''
    ));
}
