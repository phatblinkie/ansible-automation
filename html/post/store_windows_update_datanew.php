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
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Read POST data
$data = file_get_contents('php://input');
$data = json_decode($data, true);

// Initialize response
$response = ['status' => 'error', 'message' => 'Invalid data'];

// Check if data exists and has all required fields
if ($data && isset($data['hostname']) && isset($data['update_history']) && isset($data['project_id'])) {
    $hostname = $data['hostname'];
    $update_history = $data['update_history'];
    $project_id = $data['project_id'];

    // Function to insert update history
    function insertUpdateHistory($conn, $hostname, $project_id, $updates) {
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM windows_update_history WHERE hostname = ? AND project_id = ? AND title = ? AND date = ? AND operation = ? AND status = ? AND COALESCE(kb, '') = COALESCE(?, '') AND pc = ?");
        $stmt_insert = $conn->prepare("INSERT INTO windows_update_history (hostname, project_id, title, date, operation, status, kb, pc) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt_check || !$stmt_insert) {
            return ['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error];
        }

        $duplicates = 0;
        $insertions = 0;

        foreach ($updates as $update) {
            $title = $update['Title'] ?? null;
            $date = isset($update['Date']) ? date('Y-m-d H:i:s', strtotime($update['Date'])) : null;
            $operation = $update['Operation'] ?? null;
            $status = $update['Status'] ?? null;
            $kb = $update['KB'] ?? null;
            $pc = $update['PC'] ?? null;

            $stmt_check->bind_param("ssssssss", $hostname, $project_id, $title, $date, $operation, $status, $kb, $pc);
            $stmt_check->execute();
            $stmt_check->store_result(); // Ensure the result set is fully fetched
            $stmt_check->bind_result($count);
            $stmt_check->fetch();

            if ($count == 0) {
                $stmt_insert->bind_param("ssssssss", $hostname, $project_id, $title, $date, $operation, $status, $kb, $pc);
                if ($stmt_insert->execute()) {
                    $insertions++;
                } else {
                    return ['status' => 'error', 'message' => 'Execute failed: ' . $stmt_insert->error];
                }
            } else {
                $duplicates++;
            }
        }
        $stmt_check->close();
        $stmt_insert->close();
        return ['status' => 'success', 'message' => 'Data processed successfully', 'duplicates' => $duplicates, 'insertions' => $insertions];
    }

    // Insert update history
    $response = insertUpdateHistory($conn, $hostname, $project_id, $update_history);
}

// Set header to JSON
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>