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

// Fetch hosts from linux_patching_status and patching_status tables
$query = "
    SELECT DISTINCT hostname FROM linux_patching_status WHERE project_id = ?
    UNION
    SELECT DISTINCT hostname FROM patching_status WHERE project_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $project_id, $project_id);
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