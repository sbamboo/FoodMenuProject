<?php
// Import
require('functions.php');

// JSON Content-Type header
header('Content-Type: application/json; charset=utf-8');

// Get the seeds
//$seeds = getSeed(); //MARK: For now hardcode
$seeds = ["2025;12"];

// Get the entries for "veggy" and "non_veggy"
$entries = getEntries();
$veggy_entries = $entries[0];
$non_veggy_entries = $entries[1];

// Iterate al seeds and call generateRandomWeek setting $weeks[$week_number]
$weeks = [];
foreach ($seeds as $seed) {
    $week_number = explode(';', $seed)[1];
    $weeks[$week_number] = generateRandomWeek($seed, $veggy_entries, $non_veggy_entries);
}

echo json_encode($weeks, JSON_UNESCAPED_UNICODE);


// FOR NOW, JUST RETURN AN ERROR
//echo '{"error": "API not found", "msg": "Please refer to the documentation at /FoodMenuProject/docs"}';
?>