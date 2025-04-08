<?php
// Build the query string from current $_GET
$query = http_build_query($_GET);

// Target URL
$target = '../v1/index.php';

// Append query string if it's not empty
if (!empty($query)) {
    $target .= '?' . $query;
}

// Redirect with 307 to preserve method (optional)
http_response_code(307); // or 302 if you prefer
header("Location: $target");
exit();