<?php
/**
 * upgrade_user.php
 *
 * @package    local_chartplugin
 * @copyright  2026 Debonair Training
 * @author     Gregory Ekhator <greg_ekhator@yahoo.com>
 * @company    Debonair Training Limited
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

namespace local_chartplugin\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

use external_api;
use external_function_parameters;
use external_value;

class upgrade_user extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'The ID of the user to upgrade'),
            'license_type' => new external_value(PARAM_ALPHA, 'premium or enterprise')
        ]);
    }

    public static function execute($userid, $license_type) {
        global $DB;

        // Security check: Ensure the caller is authorized
        self::validate_context(\context_system::instance());

        // Update the user's Learning Dashboard status in the database
        // We use set_user_preference so it persists across sessions
        set_user_preference('local_chartplugin_license', $license_type, $userid);

        return [
            'status' => true,
            'message' => "User {$userid} successfully upgraded to {$license_type} mode."
        ];
    }

    public static function execute_returns() {
        return new \external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Response message')
        ]);
    }
}