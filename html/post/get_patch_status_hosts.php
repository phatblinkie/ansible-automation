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

// Fetch hosts and number of available updates from patching_status table
$query = "
    SELECT hostname, found_update_count AS available_updates
    FROM patching_status
    WHERE project_id = ?
    AND timestamp = (
        SELECT MAX(timestamp)
        FROM patching_status AS ps
        WHERE ps.hostname = patching_status.hostname
        AND ps.project_id = patching_status.project_id
    )
    ORDER BY hostname";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

$hosts = [];
while ($row = $result->fetch_assoc()) {
    $hosts[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($hosts);
?>