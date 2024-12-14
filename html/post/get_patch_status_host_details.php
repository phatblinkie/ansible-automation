<?php
$servername = "localhost";
$username = "semaphore_user";
$password = "DFyuqwhjty34JK@#23@#";
$dbname = "semaphore_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$project_id = $_GET['project_id'];
$hostname = $_GET['hostname'];

// Fetch the most recent data for the specified host from linux_patching_status and patching_status tables
$query = "
    SELECT * FROM linux_patching_status WHERE project_id = ? AND hostname = ? ORDER BY timestamp DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $project_id, $hostname);
$stmt->execute();
$result = $stmt->get_result();
$hostDetails = $result->fetch_assoc();

if (!$hostDetails) {
    $query = "
        SELECT * FROM patching_status WHERE project_id = ? AND hostname = ? ORDER BY timestamp DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $project_id, $hostname);
    $stmt->execute();
    $result = $stmt->get_result();
    $hostDetails = $result->fetch_assoc();
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($hostDetails);
?>