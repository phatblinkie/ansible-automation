<?php
$servername = "localhost";
$username = "semaphore_user";
$password = "DFyuqwhjty34JK@#23@#";
$dbname = "semaphore_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$project_id = $_GET['project_id'];
$hostname = $_GET['hostname'];

// Fetch the most recent data for the specified host from patching_status table
$query = "
    SELECT *
    FROM patching_status
    WHERE project_id = ?
    AND hostname = ?
    ORDER BY timestamp DESC
    LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $project_id, $hostname);
$stmt->execute();
$result = $stmt->get_result();
$hostDetails = $result->fetch_assoc();

if ($hostDetails) {
    // Fetch the list of updates for the specified host
    $query = "
        SELECT title
        FROM patching_updates
        WHERE patching_status_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $hostDetails['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $updates = [];
    while ($row = $result->fetch_assoc()) {
        $updates[] = $row['title'];
    }
    $hostDetails['updates'] = $updates;

    // Fetch the list of installed updates for the specified host
    $query = "
        SELECT title
        FROM windows_update_history
        WHERE hostname = ?
          AND operation = 'Installation'
          AND status = 'Succeeded'
        ORDER BY date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $hostname);
    $stmt->execute();
    $result = $stmt->get_result();
    $installedUpdates = [];
    while ($row = $result->fetch_assoc()) {
        $installedUpdates[] = $row['title'];
    }
    $hostDetails['installedUpdates'] = $installedUpdates;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($hostDetails);
?>