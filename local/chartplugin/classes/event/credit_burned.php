<?php
/**
 * credit_burned.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

namespace local_chartplugin\event; // Ensure this is SINGULAR 'event'

defined('MOODLE_INTERNAL') || die();

class credit_burned extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
    public static function get_name() { 
        return "Credit Burned"; 
    }
    public function get_description() { 
        return "User {$this->userid} used 1 credit."; 
    }
}