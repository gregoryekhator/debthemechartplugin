<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_chartplugin_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // This version number must match what you put in version.php next
    if ($oldversion < 2026021101) {

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

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026021101, 'local', 'chartplugin');
    }
    return true;
}
