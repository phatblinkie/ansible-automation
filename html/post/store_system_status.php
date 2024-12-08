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
        if (!isset($item['ip_address'], $item['hostname'], $item['ansible_ping'], $item['disk_capacity'], $item['proc_usage'], $item['app_check'], $item['uptime'], $item['project_id'], $item['task_id'])) {
            $valid = false;
            break;
        }
    }

    if ($valid) {
        // Prepare and bind for system_status table
        $stmt = $conn->prepare("INSERT INTO system_status (ip_address, hostname, ansible_ping, disk_capacity, proc_usage, app_check, uptime, project_id, task_id, last_updated, last_responded)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), IF(? = 'pong', NOW(), NULL))
        ON DUPLICATE KEY UPDATE hostname=VALUES(hostname), ansible_ping=VALUES(ansible_ping), disk_capacity=VALUES(disk_capacity), proc_usage=VALUES(proc_usage), app_check=VALUES(app_check), uptime=VALUES(uptime), task_id=VALUES(task_id), last_updated=NOW(), last_responded=IF(VALUES(ansible_ping)='pong', VALUES(last_updated), last_responded)");
        $stmt->bind_param("ssssssiiss", $ip_address, $hostname, $ansible_ping, $disk_capacity, $proc_usage, $app_check, $uptime, $project_id, $task_id, $ansible_ping);

        // Prepare and bind for system_status_history table
        $stmt_history = $conn->prepare("INSERT INTO system_status_history (ip_address, hostname, ansible_ping, disk_capacity, proc_usage, app_check, uptime, project_id, task_id, last_updated, last_responded)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), IF(? = 'pong', NOW(), NULL))");
        $stmt_history->bind_param("ssssssiiss", $ip_address, $hostname, $ansible_ping, $disk_capacity, $proc_usage, $app_check, $uptime, $project_id, $task_id, $ansible_ping);

        $success = true;

        foreach ($data as $item) {
            // Extract data
            $ip_address = $item['ip_address'];
            $hostname = $item['hostname'];
            $ansible_ping = $item['ansible_ping'];
            $disk_capacity = $item['disk_capacity'];
            $proc_usage = implode(",", $item['proc_usage']); // Convert array to string
            $app_check = $item['app_check'];
            $uptime = $item['uptime'];
            $project_id = $item['project_id'];
            $task_id = $item['task_id'];

            // Execute the statement for system_status table
            if (!$stmt->execute()) {
                $success = false;
                break;
            }

            // Execute the statement for system_status_history table
            if (!$stmt_history->execute()) {
                $success = false;
                break;
            }
        }

        // Close the statements and connection
        $stmt->close();
        $stmt_history->close();
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