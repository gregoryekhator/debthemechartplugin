<?php
// /var/www/html/moodle_test/local/chartplugin/lib.php

defined('MOODLE_INTERNAL') || die();

/**
 * Inject chart assets and container on dashboard only.
 */
function local_chartplugin_extend_navigation(global_navigation $nav) {
    global $PAGE;
    if ($PAGE->pagetype !== 'my-index') {
        return;
    }
}

/**
 * Calculates the average grade for all students in a given course.
 */
function local_chartplugin_get_cohort_average($courseid) {
    global $DB;

    $sql = "SELECT AVG(gg.finalgrade) 
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gi.courseid = :courseid 
              AND gi.itemtype = 'course'";

    $average = $DB->get_field_sql($sql, ['courseid' => $courseid]);

    return $average ? number_format((float)$average, 2) : "0.00";
}

/**
 * Fetches the grade for a specific user in a specific course.
 */
function local_chartplugin_get_user_grade($courseid, $userid) {
    global $DB;

    $sql = "SELECT gg.finalgrade 
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gi.courseid = :courseid 
              AND gi.itemtype = 'course'
              AND gg.userid = :userid";

    $grade = $DB->get_field_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);

    return $grade ? (float)$grade : 0.0;
}

/**
 * Fetches the three lowest course grades for the user.
 */
function local_chartplugin_get_lowest_courses($userid) {
    global $DB;

    $sql = "SELECT gi.itemname, gg.finalgrade 
            FROM {grade_grades} gg
            JOIN {grade_items} gi ON gg.itemid = gi.id
            WHERE gg.userid = :userid 
              AND gi.itemtype = 'course'
            ORDER BY gg.finalgrade ASC
            LIMIT 3";

    return $DB->get_records_sql($sql, ['userid' => $userid]);
}

/**
 * Automatically creates a Learning Plan if a student is at risk.
 */
function local_chartplugin_prescribe_learning_plan($userid) {
    global $DB, $CFG; // ADDED $CFG here to fix the "Undefined variable" error
    
    // Check if prediction exists first
    $prediction = $DB->get_field('local_chartplugin_trends', 'prediction', ['userid' => $userid]);

    if ($prediction !== false && $prediction < 70) {
        $syscontext = \context_system::instance();

        // 1. Check for Framework
        $framework = $DB->get_record('competency_framework', ['shortname' => 'AI Remedial Support']);
        
        if (!$framework) {
            // We use the DB to find scale items directly to avoid path issues
            $scale = $DB->get_record('scale', ['id' => 1]);
            $scaleitems = explode(',', $scale->scale);
            $config = [];
            foreach ($scaleitems as $index => $name) {
                $id = $index + 1; 
                $config[] = ['id' => $id, 'scaledefault' => ($id == 1 ? 1 : 0), 'proficient' => ($id == count($scaleitems) ? 1 : 0)];
            }

            $data = (object) [
                'shortname' => 'AI Remedial Support',
                'idnumber' => 'AI_REM_01',
                'description' => 'AI-generated support.',
                'descriptionformat' => FORMAT_HTML,
                'contextid' => $syscontext->id,
                'scaleid' => 1, 
                'visible' => 1,
                'scaleconfiguration' => json_encode($config),
                'timemodified' => time(),
                'usermodified' => 2
            ];
            
            try {
                // Moodle 4.x/5.x will autoload this class if we use the full namespace
                $framework_persistent = \core_competency\api::create_framework($data);
                $frameworkid = $framework_persistent->get('id');
            } catch (\Exception $e) {
                // Fallback to any existing framework
                $frameworkid = $DB->get_field('competency_framework', 'id', [], IGNORE_MULTIPLE);
            }
        }

        // 2. Create the Learning Plan
        $plan_data = (object) [
            'name' => 'Personalized Recovery Plan for User ' . $userid,
            'userid' => $userid,
            'description' => 'Based on your recent forecast, this plan helps you recover.',
            'descriptionformat' => FORMAT_HTML,
            'status' => 1, // Status 1 = Active
            'timemodified' => time(),
            'usermodified' => 2
        ];
        
        try {
            return \core_competency\api::create_plan($plan_data);
        } catch (\Exception $e) {
            // If plan already exists or fails, just return false
            return false;
        }
    }
    return false;
}