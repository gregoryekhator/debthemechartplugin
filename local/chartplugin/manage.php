<?php
/**
 * Path: /local/chartplugin/manage.php
 * Rebranded: Learning Dashboard Control Center with Search
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $DB, $PAGE, $OUTPUT;

require_login();
require_capability('moodle/site:config', context_system::instance());

$search = optional_param('search', '', PARAM_RAW);

$PAGE->set_url(new moodle_url('/local/chartplugin/manage.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Credit Management");
$PAGE->set_heading("Learning Dashboard: Control Center");

echo $OUTPUT->header();

// --- 1. ACTION HANDLING ---
$targetuserid = optional_param('userid', 0, PARAM_INT);
$newcredits   = optional_param('credits', null, PARAM_INT);
$action       = optional_param('action', 'update', PARAM_ALPHA);

if ($targetuserid) {
    $record = $DB->get_record('local_chartplugin_payments', ['userid' => $targetuserid]);
    
    if ($action === 'deactivate' && $record) {
        $record->valid_until = time() - 3600; 
        $DB->update_record('local_chartplugin_payments', $record);
        echo $OUTPUT->notification("Access deactivated for User ID: $targetuserid", 'notifynotice');
    } else if ($newcredits !== null) {
        // --- FIXED: DATABASE PERSISTENCE LOGIC ---
        if ($record) {
            // Update existing row
            $record->credits = $newcredits;
            $DB->update_record('local_chartplugin_payments', $record);
        } else {
            // Create a new row if user has never had credits before
            $newrecord = new stdClass();
            $newrecord->userid = $targetuserid;
            $newrecord->credits = $newcredits;
            $newrecord->status = 'active';
            $newrecord->valid_until = 0;
            $DB->insert_record('local_chartplugin_payments', $newrecord);
        }
        echo $OUTPUT->notification("Balance updated to $newcredits for User ID: $targetuserid.", 'notifysuccess');
    }
}

// --- 2. TOP NAV & SEARCH (Intuitive UI) ---
echo '<div class="d-flex justify-content-between align-items-center mb-4">';
echo '<a href="index.php" class="btn btn-secondary shadow-sm"><i class="fa fa-arrow-left"></i> Learning Dashboard</a>';
echo '<form method="GET" class="form-inline">
        <input type="text" name="search" class="form-control mr-2" placeholder="Search Name or ID..." value="'.s($search).'">
        <button type="submit" class="btn btn-primary">Search</button>
      </form>';
echo '</div>';

// --- 3. SEARCH LOGIC ---
$params = [];
$wheresql = "u.deleted = 0";
if (!empty($search)) {
    $wheresql .= " AND (u.firstname LIKE :s1 OR u.lastname LIKE :s2 OR u.id = :s3)";
    $params['s1'] = $params['s2'] = "%$search%";
    $params['s3'] = (int)$search;
}

$sql = "SELECT u.id, u.firstname, u.lastname, p.credits, p.valid_until 
        FROM {user} u 
        LEFT JOIN {local_chartplugin_payments} p ON u.id = p.userid 
        WHERE $wheresql ORDER BY u.lastname ASC LIMIT 50";
$users = $DB->get_records_sql($sql, $params);

// --- 4. RENDER TABLE ---
echo '<table class="table table-hover mt-3 shadow-sm" style="background: #fff; border-radius: 8px; overflow: hidden;">
        <thead class="thead-dark">
            <tr><th>User</th><th>Credits</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>';

foreach ($users as $u) {
    $current = $u->credits ?? 0;
    $is_active = (!empty($u->valid_until) && $u->valid_until > time());
    $status_badge = $is_active ? 'badge-success' : 'badge-secondary';
    
    echo "<tr>
            <td><strong>$u->firstname $u->lastname</strong><br><small class='text-muted'>ID: $u->id</small></td>
            <td><strong>$current</strong></td>
            <td><span class='badge $status_badge'>" . ($is_active ? 'BOOST ACTIVE' : 'NO BOOST') . "</span></td>
            <td>
                <form method='POST' class='form-inline'>
                    <input type='hidden' name='userid' value='$u->id'>
                    <input type='number' name='credits' class='form-control form-control-sm mr-2' style='width:65px;' value='$current'>
                    <button type='submit' name='action' value='update' class='btn btn-success btn-sm mr-1'>Update</button>
                    <button type='submit' name='action' value='deactivate' class='btn btn-danger btn-sm'>Kill Access</button>
                </form>
            </td>
          </tr>";
}
echo '</tbody></table>';
echo $OUTPUT->footer();