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
function checkAndSetDefault(&$array, $key, $default) {
    if (!isset($array[$key])) {
        $array[$key] = $default;
        return false;
    }
    return true;
}

// Check if data exists and has all required fields
if ($data && isset($data['msg']) && is_array($data['msg'])) {
    $status = $data['msg'][1];
    $hostname = $data['hostname'] ?? 'unknown_host';
    $filtered_updates = $status['filtered_updates'] ?? [];
    $updates = $status['updates'] ?? [];

    // Sanity checks for status fields
    $missing_fields = [];
    if (!checkAndSetDefault($status, 'changed', false)) $missing_fields[] = 'changed';
    if (!checkAndSetDefault($status, 'failed', false)) $missing_fields[] = 'failed';
    if (!checkAndSetDefault($status, 'failed_update_count', 0)) $missing_fields[] = 'failed_update_count';
    if (!checkAndSetDefault($status, 'found_update_count', 0)) $missing_fields[] = 'found_update_count';
    if (!checkAndSetDefault($status, 'installed_update_count', 0)) $missing_fields[] = 'installed_update_count';
    if (!checkAndSetDefault($status, 'reboot_required', false)) $missing_fields[] = 'reboot_required';
    if (!checkAndSetDefault($status, 'rebooted', false)) $missing_fields[] = 'rebooted';

    // Insert or update patching status
    $patching_status_id = insertOrUpdatePatchingStatus($conn, $hostname, $status);

    // Insert or update filtered updates
    insertOrUpdatePatchingUpdates($conn, $patching_status_id, $filtered_updates, 'filtered');

    // Insert or update updates
    insertOrUpdatePatchingUpdates($conn, $patching_status_id, $updates, 'regular');

    $response = ['status' => 'success'];
    if (!empty($missing_fields)) {
        $response['message'] = 'Missing fields: ' . implode(', ', $missing_fields);
    }
}

// Function to insert or update patching status
function insertOrUpdatePatchingStatus($conn, $hostname, $status) {
    $stmt = $conn->prepare("INSERT INTO patching_status (hostname, changed, failed, failed_update_count, found_update_count, installed_update_count, reboot_required, rebooted)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE changed=VALUES(changed), failed=VALUES(failed), failed_update_count=VALUES(failed_update_count), found_update_count=VALUES(found_update_count), installed_update_count=VALUES(installed_update_count), reboot_required=VALUES(reboot_required), rebooted=VALUES(rebooted), timestamp=CURRENT_TIMESTAMP");
    $stmt->bind_param("siiiiiii", $hostname, $status['changed'], $status['failed'], $status['failed_update_count'], $status['found_update_count'], $status['installed_update_count'], $status['reboot_required'], $status['rebooted']);

    // Print the MySQL statement
    $query = "INSERT INTO patching_status (hostname, changed, failed, failed_update_count, found_update_count, installed_update_count, reboot_required, rebooted)
              VALUES ('$hostname', {$status['changed']}, {$status['failed']}, {$status['failed_update_count']}, {$status['found_update_count']}, {$status['installed_update_count']}, {$status['reboot_required']}, {$status['rebooted']})
              ON DUPLICATE KEY UPDATE changed=VALUES(changed), failed=VALUES(failed), failed_update_count=VALUES(failed_update_count), found_update_count=VALUES(found_update_count), installed_update_count=VALUES(installed_update_count), reboot_required=VALUES(reboot_required), rebooted=VALUES(rebooted), timestamp=CURRENT_TIMESTAMP";
    echo "Executing query: $query\n";

    $stmt->execute();
    $patching_status_id = $stmt->insert_id;
    $stmt->close();
    return $patching_status_id;
}

// Function to insert or update patching updates
function insertOrUpdatePatchingUpdates($conn, $patching_status_id, $updates, $update_type) {
    foreach ($updates as $update_id => $update) {
        $categories = implode(", ", $update['categories']);
        $kb = implode(", ", $update['kb']);
        $downloaded = $update['downloaded'] ? 1 : 0;
        $installed = $update['installed'] ? 1 : 0;
        $stmt = $conn->prepare("INSERT INTO patching_updates (patching_status_id, update_id, title, categories, downloaded, installed, kb, update_type)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE title=VALUES(title), categories=VALUES(categories), downloaded=VALUES(downloaded), installed=VALUES(installed), kb=VALUES(kb), update_type=VALUES(update_type)");
        $stmt->bind_param("issssiss", $patching_status_id, $update_id, $update['title'], $categories, $downloaded, $installed, $kb, $update_type);

        // Print the MySQL statement
        $query = "INSERT INTO patching_updates (patching_status_id, update_id, title, categories, downloaded, installed, kb, update_type)
                  VALUES ($patching_status_id, '$update_id', '{$update['title']}', '$categories', $downloaded, $installed, '$kb', '$update_type')
                  ON DUPLICATE KEY UPDATE title=VALUES(title), categories=VALUES(categories), downloaded=VALUES(downloaded), installed=VALUES(installed), kb=VALUES(kb), update_type=VALUES(update_type)";
        echo "Executing query: $query\n";

        $stmt->execute();
        $stmt->close();
    }
}

// Set header to JSON
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>