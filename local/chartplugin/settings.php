<?php
/**
 * settings.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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