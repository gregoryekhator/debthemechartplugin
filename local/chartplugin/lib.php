<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Inject chart assets and container on dashboard only.
 */
function local_chartplugin_extend_navigation(global_navigation $nav) {
    global $PAGE, $USER;

    // Only dashboard.
    if ($PAGE->pagetype !== 'my-index') {
        return;
    }

    // Never admin pages.
    if (is_siteadmin() && strpos($PAGE->url->out(), '/admin/') !== false) {
        return;
    }

    // Require AMD module.
    //$PAGE->requires->js_call_amd('local_chartplugin/charts', 'init');

    // Inject container immediately after header.
    //$PAGE->requires->data_for_js('local_chartplugin_render', true);
}
