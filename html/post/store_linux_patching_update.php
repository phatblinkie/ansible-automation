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

// Sanity check function
function checkAndSetDefault(&$array, $key, $default, &$missing_fields, $allow_empty_string = false) {
    if (!isset($array[$key]) || $array[$key] === null || (!$allow_empty_string && $array[$key] === '')) {
        $array[$key] = $default;
        $missing_fields[] = $key;
        return false;
    }
    return true;
}

// Check if data exists and has all required fields
$missing_fields = [];
if ($data && isset($data['changed']) && isset($data['failed']) && isset($data['msg']) && isset($data['rc']) && isset($data['project_id']) && isset($data['task_id'])) {
    $status = $data;
    $hostname = $data['hostname'] ?? 'unknown_host';
    $results = $status['results'] ?? [];

    // Sanity checks for status fields
    if (!checkAndSetDefault($status, 'changed', false, $missing_fields)) $missing_fields[] = 'changed';
    if (!checkAndSetDefault($status, 'failed', false, $missing_fields)) $missing_fields[] = 'failed';
    if (!checkAndSetDefault($status, 'msg', '', $missing_fields, true)) $missing_fields[] = 'msg';
    if (!checkAndSetDefault($status, 'rc', 0, $missing_fields)) $missing_fields[] = 'rc';
    if (!checkAndSetDefault($status, 'project_id', 0, $missing_fields)) $missing_fields[] = 'project_id';
    if (!checkAndSetDefault($status, 'task_id', 0, $missing_fields)) $missing_fields[] = 'task_id';

    // Insert or update patching status
    $linux_patching_status_id = insertOrUpdatePatchingStatus($conn, $hostname, $status);

    // Insert or update patching results
    insertOrUpdatePatchingResults($conn, $linux_patching_status_id, $results, $status['project_id'], $status['task_id']);

    $response = ['status' => 'success'];
    if (!empty($missing_fields)) {
        $response['message'] = 'Missing fields: ' . implode(', ', $missing_fields);
    }
} else {
    $response['message'] = 'Invalid data: Missing required fields';
}

// Function to insert or update patching status
function insertOrUpdatePatchingStatus($conn, $hostname, $status) {
    $stmt = $conn->prepare("INSERT INTO linux_patching_status (hostname, changed, failed, msg, rc, project_id, task_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE changed=VALUES(changed), failed=VALUES(failed), msg=VALUES(msg), rc=VALUES(rc), project_id=VALUES(project_id), task_id=VALUES(task_id), timestamp=CURRENT_TIMESTAMP");
    $stmt->bind_param("siisiii", $hostname, $status['changed'], $status['failed'], $status['msg'], $status['rc'], $status['project_id'], $status['task_id']);
    $stmt->execute();
    $linux_patching_status_id = $stmt->insert_id;
    $stmt->close();
    return $linux_patching_status_id;
}

// Function to insert or update patching results
function insertOrUpdatePatchingResults($conn, $linux_patching_status_id, $results, $project_id, $task_id) {
    foreach ($results as $result) {
        $stmt = $conn->prepare("INSERT INTO linux_patching_results (linux_patching_status_id, result, project_id, task_id)
                                VALUES (?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE result=VALUES(result), project_id=VALUES(project_id), task_id=VALUES(task_id)");
        $stmt->bind_param("isii", $linux_patching_status_id, $result, $project_id, $task_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Set header to JSON
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>