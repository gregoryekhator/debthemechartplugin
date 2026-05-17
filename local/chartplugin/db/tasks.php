<?php
/**
 * tasks.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// /var/www/html/moodle_test/local/chartplugin/db/tasks.php

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\local_chartplugin\task\compute_analytics_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ]
];