<?php
// Import
require('functions.php');

// JSON content-type header
header('Content-Type: application/json');

// FOR NOW, JUST RETURN AN ERROR
echo '{"error": "API not found", "msg": "Please refer to the documentation at /FoodMenuProject/docs"}';
?>