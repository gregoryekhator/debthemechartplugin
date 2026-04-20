<?php
defined('MOODLE_INTERNAL') || die();

$payables = [
    'credits' => [
        'component' => 'local_chartplugin',
        'callback'  => 'local_chartplugin_payment_callback', // Updated name
    ]
];