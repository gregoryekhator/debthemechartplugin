<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/settings.php
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Parameter 1 MUST match the folder name 'local_chartplugin'
    $settings = new admin_settingpage('local_chartplugin', 'Chart Plugin: Flight Deck');

    $settings->add(new admin_setting_configcheckbox(
        'local_chartplugin/enable_global_enterprise',
        'Global Enterprise Mode',
        'Check this to bypass payment tables for development/testing.',
        0
    ));

    $ADMIN->add('localplugins', $settings);
}