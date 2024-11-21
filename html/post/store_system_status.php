<?php
// Database credentials
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

// Read POST data
$data = file_get_contents('php://input');
$data = json_decode($data, true);

// Initialize response
$response = ['status' => 'error', 'message' => 'Invalid data'];

// Check if data exists and has all required fields
if ($data && is_array($data)) {
    $valid = true;

    foreach ($data as $item) {
        if (!isset($item['hostname'], $item['ansible_ping'], $item['disk_capacity'], $item['proc_usage'], $item['app_check'])) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO system_status (hostname, ansible_ping, disk_capacity, proc_usage, app_check) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $hostname, $ansible_ping, $disk_capacity, $proc_usage, $app_check);

        $success = true;

        foreach ($data as $item) {
            // Extract data
            $hostname = $item['hostname'];
            $ansible_ping = $item['ansible_ping'];
            $disk_capacity = $item['disk_capacity'];
            $proc_usage = implode(",", $item['proc_usage']); // Convert array to string
            $app_check = $item['app_check'];

            // Execute the statement
            if (!$stmt->execute()) {
                $success = false;
                break;
            }
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();

        // Return a response
        if ($success) {
            $response = ['status' => 'success'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to insert data'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Missing required fields'];
    }
}

// Set header to JSON
header('Content-Type: application/json');
echo json_encode($response);
?>

