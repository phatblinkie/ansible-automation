<?php
// Database credentials
$servername = "localhost";
$username = "semaphore_user";
$password = "DFyuqwhjty34JK@#23@#";
$dbname = "semaphore_db";

// Basic checks for "hostname" param
$hostname = $_GET['hostname'] ?? '';
if (empty($hostname)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No hostname provided',
    ]);
    exit;
}

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed: ' . $conn->connect_error,
    ]);
    exit;
}

// Prepare query to find "installed" updates
// Example: "Installation" in the "operation" column
$stmt = $conn->prepare("
    SELECT title
    FROM windows_update_history
    WHERE hostname = ?
      AND operation = 'Installation'
      AND status = 'Succeeded'
    ORDER BY date DESC
");

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare failed: ' . $conn->error,
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("s", $hostname);
$stmt->execute();
$result = $stmt->get_result();

$installedUpdates = [];
while ($row = $result->fetch_assoc()) {
    // Just collect the Title. Adjust as needed
    $installedUpdates[] = $row['title'];
}
$stmt->close();
$conn->close();

// Output JSON
echo json_encode([
    'status' => 'success',
    'installedUpdates' => $installedUpdates,
]);
?>