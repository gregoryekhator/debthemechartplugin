<?php
defined('MOODLE_INTERNAL') || die();
global $CFG, $OUTPUT, $PAGE, $USER;

$currenttype = optional_param('type', 'synopsis', PARAM_TEXT);
$user_level = 'freemium'; // Change for testing

$is_premium = ($user_level === 'premium' || $user_level === 'enterprise');
$is_enterprise = ($user_level === 'enterprise');

$is_locked = false;
$required_level = "";
if (($currenttype === 'cohort' || $currenttype === 'frequency') && !$is_premium) {
    $is_locked = true;
    $required_level = "Premium";
} else if (($currenttype === 'best_plan' || $currenttype === 'style') && !$is_enterprise) {
    $is_locked = true;
    $required_level = "Enterprise";
}

$template_context = [
    'is_premium' => $is_premium,
    'is_enterprise' => $is_enterprise,
    'type_synopsis' => ($currenttype === 'synopsis'),
    'type_best' => ($currenttype === 'best'),
    'type_cohort' => ($currenttype === 'cohort'),
    'type_plan' => ($currenttype === 'best_plan'),
    'type_synopsis_30' => ($currenttype === 'synopsis_30'),
    'type_lowest' => ($currenttype === 'lowest_courses'),
    'type_frequency' => ($currenttype === 'frequency'),
    'type_style' => ($currenttype === 'style'),
];

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $PAGE->title; ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>
    <style>
        body { background-color: #f4f7f6 !important; padding-top: 60px; } /* Space for fixed nav */
        #page-header { display: none !important; }
        
        /* Dashboard Wrapper */
        .dashboard-outer-wrapper { margin: 0 5% 50px 5%; }
        .deb-header-rect { background-color: #007bff !important; border-radius: 20px 20px 0 0; padding: 40px; color: white; position: relative; }
        .deb-nav-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 20px; }
        
        /* Content Grid */
        .deb-content-rect { background-color: white; padding: 30px; display: grid; grid-template-columns: 74% 24%; gap: 2%; border: 1px solid #dee2e6; border-top: none; position: relative; min-height: 600px; }
        
        /* The Shield */
        .restricted-shield {
            position: absolute; top: 0; left: 0; width: 75%; height: 100%; z-index: 50;
            background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);
            display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 40px;
        }

        /* Buttons & Nav */
        .nav-btn { background-color: white !important; color: #007bff !important; border-radius: 8px; font-weight: bold; text-align: center; padding: 12px; text-decoration: none !important; display: block; }
        .nav-btn.active-tab { background-color: #004085 !important; color: white !important; }
        .locked-btn { opacity: 0.6; background-color: #e9ecef !important; }
    </style>
</head>
<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<nav class="fixed-top navbar navbar-light bg-white navbar-expand moodle-has-zindex" aria-label="<?php echo get_string('sitenavigation') ?>">
    <div class="container-fluid">
        <button class="navbar-toggler aabtn" type="button" data-toggle="collapse" data-target="#navbar-controls" aria-controls="navbar-controls" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-brand d-flex align-items-center">
            <img src="<?php echo $OUTPUT->get_logo_url(); ?>" alt="Logo" style="height: 35px;">
        </div>
        <div id="navbar-controls" class="collapse navbar-collapse">
            <?php echo $OUTPUT->custom_menu(); ?>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-flex align-items-center">
                    <?php echo $OUTPUT->user_menu(); ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="dashboard-outer-wrapper">
    <div class="deb-header-rect">
        <h1 class="h2 font-weight-bold">Debonair Training A.I. Dashboard</h1>
        <?php echo $OUTPUT->render_from_template('theme_debtheme/custom_header', $template_context); ?>
    </div>

    <div class="deb-content-rect <?php echo $is_locked ? 'is-locked-view' : ''; ?>">
        <?php if ($is_locked): ?>
        <div class="restricted-shield">
            <i class="fa fa-lock fa-5x text-muted mb-4"></i>
            <h2 class="text-dark">Access Restricted</h2>
            <p class="lead text-muted">The <strong><?php echo s($currenttype); ?></strong> insight is available on the <strong><?php echo s($required_level); ?> Plan</strong>.</p>
            <button class="btn btn-primary btn-lg mt-3" onclick="alert('Redirecting...')">Upgrade Dashboard</button>
        </div>
        <?php endif; ?>

        <main id="region-main" style="<?php echo $is_locked ? 'filter: blur(4px); pointer-events: none;' : ''; ?>">
            <div id="main-content-placeholder">
                <?php echo $OUTPUT->main_content(); ?>
            </div>
        </main>

        <aside id="block-region-side-pre">
            <div class="card shadow-sm border-0 p-3" style="background: #f8f9fa;">
                <h5 class="text-primary font-weight-bold">Tier Insights</h5>
                <p class="small text-muted">You are currently on the <strong><?php echo strtoupper($user_level); ?></strong> tier.</p>
                <hr>
                <?php echo $OUTPUT->blocks('side-pre'); ?>
            </div>
        </aside>
    </div>
</div>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>