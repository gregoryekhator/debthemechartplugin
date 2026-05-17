<?php
/**
 * dashboard.php
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

use renderable;
use templatable;
use renderer_base;

class dashboard implements renderable, templatable {
    protected $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function export_for_template(renderer_base $output) {
        // We render the chart object into HTML inside the export phase.
        // This is the Learning Analytics "fragment" trick.
        if (isset($this->data['chart_object'])) {
            $this->data['main_chart_html'] = $output->render($this->data['chart_object']);
        }
        return $this->data;
    }
}