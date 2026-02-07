<?php
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
