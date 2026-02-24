<?php
namespace local_chartplugin\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

class renderer extends plugin_renderer_base {
    /**
     * Deploys the full dashboard using the integrated template.
     */
    public function render_analytics_dashboard($data) {
        // Use triple braces in mustache for 'main_chart' and 'content' 
        // so Moodle handles the HTML rendering of the chart objects.
        return $this->render_from_template('local_chartplugin/analytics_page', $data);
    }
}