<?php
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

global $DB;

// ----------------------------
// CONFIG
// ----------------------------
$courseid       = 2;
$cohortnames    = ['Cohort Alpha', 'Cohort Beta'];
$userspercohort = 50;

// ----------------------------
// BOOTSTRAP
// ----------------------------
$course  = get_course($courseid);
$context = context_course::instance($courseid);

// ----------------------------
// STATE
// ----------------------------
$cohortids = [];
$userids   = [];

// ----------------------------
// LOAD USERS
// ----------------------------
$users = $DB->get_records_select(
    'user',
    'deleted = 0 AND suspended = 0 AND id > 2',
    [],
    'id ASC'
);

if (count($users) < ($userspercohort * count($cohortnames))) {
    cli_error('Not enough users to populate cohorts');
}
