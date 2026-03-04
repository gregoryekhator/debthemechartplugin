<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // We use a raw string for the title temporarily to bypass the get_string crash if the cache is stuck.
    $pagetitle = 'Growth Diagnostic Lab'; 
    $pagename = 'local_growthtest_settings';
    $pageurl = new moodle_url('/local/growthtest/index.php');
    $capability = 'local/growthtest:view';

    $ADMIN->add('localplugins', new admin_externalpage($pagename, $pagetitle, $pageurl, $capability));
}

$settings = null;