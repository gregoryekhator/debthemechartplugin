<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');

$userid = 2;
echo "Phase 3: Running ML Prediction Engine for User $userid...\n";

// 1. Fetch the historical trend from our high-speed table
$record = $DB->get_record('local_chartplugin_trends', ['userid' => $userid]);
$data = json_decode($record->monthly_trend, true);
$scores = array_values($data);

// 2. Simple Linear Regression (y = mx + b)
$n = count($scores);
$sumX = 0; $sumY = 0; $sumXY = 0; $sumXX = 0;

foreach ($scores as $x => $y) {
    $sumX += $x;
    $sumY += $y;
    $sumXY += ($x * $y);
    $sumXX += ($x * $x);
}

// Calculate the slope (m)
$slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
// Calculate the intercept (b)
$intercept = ($sumY - $slope * $sumX) / $n;

// 3. Predict the next month (index $n)
$predicted_score = ($slope * $n) + $intercept;

// 4. Save to Database
$DB->set_field('local_chartplugin_trends', 'prediction', $predicted_score, ['userid' => $userid]);

echo "Success: Predicted score for next month is " . round($predicted_score, 2) . "%\n";
