<?php
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

require_login();

// Context: system-level analytics access
$context = context_system::instance();

// Capability enforcement
require_capability('local/chartplugin:viewcharts', $context);

$chart = required_param('chart', PARAM_ALPHANUMEXT);

$data = [];

switch ($chart) {
    case 'last':
        $data = [
            'title' => 'Last Chart',
            'values' => [12, 18, 9, 22]
        ];
        break;

    case 'previous':
        $data = [
            'title' => 'Previous Chart',
            'values' => [5, 15, 25, 10]
        ];
        break;

    default:
        throw new moodle_exception('invalidchart', 'local_chartplugin');
}

echo json_encode($data);
