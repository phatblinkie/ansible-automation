<?php
header('Content-Type: application/json');

// Custom error handler to capture warnings and errors
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error = [
        'error' => [
            'type' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]
    ];
    echo json_encode($error);
    exit();
}

// Set the custom error handler
set_error_handler('customErrorHandler');

// Database connection
$servername = "localhost";
$username = "semaphore_user";
$password = "DFyuqwhjty34JK@#23@#";
$dbname = "semaphore_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    trigger_error('Database connection failed: ' . $conn->connect_error, E_USER_ERROR);
}

// Get the project_id, hostname, and search term from the query parameters
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$hostname = isset($_GET['hostname']) ? $conn->real_escape_string($_GET['hostname']) : '';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if ($project_id === 0 || empty($hostname) || empty($search)) {
    echo json_encode([]);
    $conn->close();
    exit();
}

// Query to search for installed updates
$sql = "SELECT title FROM windows_update_history WHERE project_id = $project_id AND hostname = '$hostname' AND title LIKE '%$search%'";
$result = $conn->query($sql);

$updates = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $updates[] = $row['title'];
    }
}

echo json_encode($updates);

$conn->close();
?>