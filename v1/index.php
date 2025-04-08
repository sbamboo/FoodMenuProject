<?php
// Import
require('functions.php');

// JSON Content-Type header
header('Content-Type: application/json; charset=utf-8');

// Custom error handler function
function errorHandler($severity, $message, $file, $line){
    error_log(
        "Error: [$severity] $message in $file on line $line",
        0
    );
    // Only handle errors that are not suppressed with @
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
}

// Custom shutdown function to catch fatal errors
function shutdownHandler() {
    $error = error_get_last();
    if ($error && $error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)) {
        // Fatal error occurred
        $errfile = $error['file'];
        $errline = $error['line'];
        $errstr = $error['message'];

        echo json_encode([
            'error' => "Fatal error: $errstr in $errfile on line $errline",
            'msg' => '',
            'status' => 'failed'
        ], JSON_UNESCAPED_UNICODE);
    }
}

// Register the custom error handler and shutdown function
set_error_handler('errorHandler');
register_shutdown_function('shutdownHandler');

// Main Logic
try {

    // Get filter options
    $options = getOptionsFromURL($_REQUEST);

    // If not $_REQUEST contains either $date or $year or $week return error
    if (!isset($_REQUEST['date']) && !isset($_REQUEST['year']) && !isset($_REQUEST['week'])) {
        echo json_encode([
            'error' => "MissingParameters",
            'msg' => 'Not all required parameters where given! Please supply either a specific date or alterantively a year and/or week-number. (See api docs)',
            'status' => 'failed'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get the seeds
    $seed_info = getSeed(); //MARK: For now hardcode
    //$seed_info = ["/v1/foodmenu/week", ["2025;12"]];
    $seeds = $seed_info[1];
    $entrypoint_name = $seed_info[0];

    // Get the entries for "veggy" and "non_veggy"
    $entries = getEntries();
    $veggy_entries = $entries[0];
    $non_veggy_entries = $entries[1];

    // Iterate all seeds and call generateRandomWeek to set $weeks[$week_number]
    $weeks = [];
    //print_r($seeds);
    foreach ($seeds as $seed) {
        $parsed_seed = explode(';', $seed);
        $year = $parsed_seed[0];
        $week_number = $parsed_seed[1];
        $entries = generateRandomWeek(
            $seed,
            $veggy_entries,
            $non_veggy_entries
        );
        $weeks[$week_number] = filterItems($year, "$week_number", $entries, $options);
    }

    // Assemble the response
    $response = [
        'format' => 0,
        "status" => "success",
        "endpoint_name" => $entrypoint_name,
        "filters" => getOptionFilters($options),
        "weeks" => $weeks
    ];

    // Return the response
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    $e_name = get_class($e);
    // Fatal error handling
    echo json_encode([
        'error' => "SERVER_ERROR.{$e_name}",
        'msg' => $e->getMessage(),
        'status' => 'failed'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
