<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_chartplugin_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Initial table creation.
    if ($oldversion < 2026041900) {
        $table = new xmldb_table('local_chartplugin_trends');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('monthly_trend', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('cohort_avg', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0.00000');
        $table->add_field('prediction', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('user_course', XMLDB_INDEX_UNIQUE, ['userid', 'courseid']);
        if (!$dbman->table_exists($table)) { $dbman->create_table($table); }
        upgrade_plugin_savepoint(true, 2026041900, 'local', 'chartplugin');
    }

    // Version 2026041902: Adding credits and valid_until for time-based logic.
    if ($oldversion < 2026041902) {
        $table = new xmldb_table('local_chartplugin_payments');
        
        // Add credits field.
        $field_credits = new xmldb_field('credits', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');
        if (!$dbman->field_exists($table, $field_credits)) {
            $dbman->add_field($table, $field_credits);
        }

        // Add valid_until (The subscription expiry timestamp).
        $field_valid = new xmldb_field('valid_until', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'transactionid');
        if (!$dbman->field_exists($table, $field_valid)) {
            $dbman->add_field($table, $field_valid);
        }

        upgrade_plugin_savepoint(true, 2026041902, 'local', 'chartplugin');
    }

    return true;
}