<?php
defined('MOODLE_INTERNAL') || die();

$areas = [
    'credits' => [
        'component' => 'local_chartplugin',
        'paymentarea' => 'credits',
        'callback' => '\local_chartplugin\payment\processor::process_payment',
    ],
];