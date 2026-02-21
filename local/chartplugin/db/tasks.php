<?php
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
