<?php
namespace local_chartplugin\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

class analytics_page implements renderable, templatable {
    protected $data;

    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Prepares data for the template.
     */
    public function export_for_template(renderer_base $output) {
        return $this->data;
    }
}