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

// Fetch the most recent data for the specified host from patching_status table
$query = "
    SELECT id
    FROM patching_status
    WHERE project_id = ?
    AND hostname = ?
    ORDER BY timestamp DESC
    LIMIT 1";
$stmt = $conn->prepare($query);
if (!$stmt) {
    trigger_error('Statement prepare failed: ' . $conn->error . ' - ' . $conn->errno, E_USER_ERROR);
}
$stmt->bind_param("is", $project_id, $hostname);
$stmt->execute();
$result = $stmt->get_result();
$hostDetails = $result->fetch_assoc();

$updates = [];
if ($hostDetails) {
    // Fetch the list of available updates for the specified host
    $query = "
        SELECT *
        FROM patching_updates
        WHERE patching_status_id = ?
        AND project_id = ?
        AND title LIKE ? ESCAPE '!'";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        trigger_error('Statement prepare failed: ' . $conn->error . ' - ' . $conn->errno, E_USER_ERROR);
    }
    $likeSearch = "%{$search}%";

    $stmt->bind_param("iis", $hostDetails['id'], $project_id, $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $updates[] = $row['title'];
    }
}

$stmt->close();
$conn->close();

echo json_encode($updates);
?>