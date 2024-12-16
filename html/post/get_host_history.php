<?php
// Database credentials
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

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

// Retrieve project_id, hostname, and time_frame from query parameters
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$hostname = isset($_GET['hostname']) ? $conn->real_escape_string($_GET['hostname']) : '';
$time_frame = isset($_GET['time_frame']) ? $_GET['time_frame'] : 'today';

// Determine the date range based on the time frame
switch ($time_frame) {
    case 'week':
        $date_condition = "last_updated >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        break;
    case 'month':
        $date_condition = "last_updated >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        break;
    case '90days':
        $date_condition = "last_updated >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
        break;
    case 'today':
    default:
        $date_condition = "DATE(last_updated) = CURDATE()";
        break;
}

// Updated SQL query with subquery to find the ip_address
$sql = "SELECT
  last_updated,
  disk_capacity,
  proc_usage,
  uptime
FROM
  system_status_history
WHERE
  project_id = $project_id
  AND (
    hostname = '$hostname'
    OR hostname LIKE '$hostname.%'
    OR ip_address = (
      SELECT ip_address
      FROM system_status_history
      WHERE project_id = $project_id
      AND (hostname = '$hostname' OR hostname LIKE '$hostname.%')
      LIMIT 1
    )
  )
  AND $date_condition
ORDER BY
  last_updated ASC";

$result = $conn->query($sql);
$data = [];

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

// Output JSON data
echo json_encode($data);
?>