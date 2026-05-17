<?php
/**
 * renderer.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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