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

$sql = "SELECT
  ip_address,
  hostname,
  ansible_ping,
  CAST(REPLACE(disk_capacity, '%', '') AS UNSIGNED) AS disk_capacity,
  proc_usage,
  CASE
    WHEN app_check IN ('running', 'started') THEN 'OK'
    ELSE 'Failed'
  END AS app_check,
  last_updated,
  last_responded,
  id,
  uptime
FROM
  system_status
WHERE
  id IN (
    SELECT
      MAX(id)
    FROM
      system_status
    GROUP BY
      hostname
  )
  AND (uptime != 0 OR TIMESTAMPDIFF(HOUR, last_responded, NOW()) <= 4 OR (ansible_ping = 'unreachable' AND TIMESTAMPDIFF(HOUR, last_updated, NOW()) <= 4))
ORDER BY
  id DESC;";

$result = $conn->query($sql);
$data = [];

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo "0 results";
}

$conn->close();

// Set header to JSON
header('Content-Type: application/json');
// Output JSON data
echo json_encode($data);
?>
