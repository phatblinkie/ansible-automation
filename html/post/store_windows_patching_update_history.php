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
if ($data && isset($data['hostname']) && isset($data['wmi_updates']) && isset($data['reliability_updates'])) {
    $hostname = $data['hostname'];
    $wmi_updates = $data['wmi_updates'];
    $reliability_updates = $data['reliability_updates'];

    // Insert into update_history table
    $stmt = $conn->prepare("INSERT INTO update_history (hostname) VALUES (?)");
    $stmt->bind_param("s", $hostname);
    $stmt->execute();
    $update_history_id = $stmt->insert_id;
    $stmt->close();

    // Function to insert update details
    function insertUpdateDetails($conn, $update_history_id, $source, $updates) {
        $stmt = $conn->prepare("INSERT INTO update_details (update_history_id, source, description, hotfix_id, installed_on, event_identifier, event_type, product_name, insertion_strings, time_generated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($updates as $update) {
            $description = $update['Description'] ?? null;
            $hotfix_id = $update['HotFixID'] ?? null;
            $installed_on = isset($update['InstalledOn']['DateTime']) ? $update['InstalledOn']['DateTime'] : null;
            $event_identifier = $update['EventIdentifier'] ?? null;
            $event_type = $update['EventType'] ?? null;
            $product_name = $update['ProductName'] ?? null;
            $insertion_strings = isset($update['InsertionStrings']) ? json_encode($update['InsertionStrings']) : null;
            $time_generated = $update['TimeGenerated'] ?? null;
            $stmt->bind_param("isssssssss", $update_history_id, $source, $description, $hotfix_id, $installed_on, $event_identifier, $event_type, $product_name, $insertion_strings, $time_generated);
            $stmt->execute();
        }
        $stmt->close();
    }

    // Insert WMI updates
    insertUpdateDetails($conn, $update_history_id, 'WMI', $wmi_updates);

    // Insert Reliability Records updates
    insertUpdateDetails($conn, $update_history_id, 'ReliabilityRecords', $reliability_updates);

    $response = ['status' => 'success', 'message' => 'Data stored successfully'];
}

// Set header to JSON
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>