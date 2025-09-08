<?php
// api_search.php

// Set the content type to JSON
header('Content-Type: application/json');

require_once 'functions.php';

// Define the list of allowed filter keys from the frontend
$allowed_filters = [
    'brand',
    'model',
    'year',
    'max_price',
    'body_type',
    'transmission',
    'fuel_type',
    'condition'
];

// Initialize an empty array to hold the active filters
$filters = [];

// Loop through the allowed keys and add them to the filters array if they exist in the GET request
foreach ($allowed_filters as $key) {
    if (!empty($_GET[$key])) {
        $filters[$key] = $_GET[$key];
    }
}

// Use the get_cars function to fetch the cars based on the provided filters
$cars = get_cars($pdo, $filters);

// Return the results as a JSON object
echo json_encode($cars);

exit;
?>
