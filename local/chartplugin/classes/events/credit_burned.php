<?php
namespace local_chartplugin\event;
defined('MOODLE_INTERNAL') || die();
class credit_burned extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
    public static function get_name() { return "Credit Burned"; }
    public function get_description() { return "User {$this->userid} used 1 credit."; }
}