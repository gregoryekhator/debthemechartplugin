<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Renamed handshake for the local plugin.
 */
function local_growthtest_extend_navigation_course($navigation, $course, $context) {
    // We leave this empty or rename the logic inside to avoid conflicts.
    // This stops the "Cannot redeclare" error.
}

// If there are other functions in there, rename them all from:
// report_growth_...  TO  local_growthtest_...