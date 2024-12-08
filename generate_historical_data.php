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

// Function to generate random data
function generateRandomData($min, $max) {
    return rand($min * 10, $max * 10) / 10;
}

// Query existing hosts
$hosts_query = "SELECT DISTINCT hostname, ip_address, project_id FROM system_status_history";
$hosts_result = $conn->query($hosts_query);

if ($hosts_result->num_rows > 0) {
    // Generate data for each host
    while ($host = $hosts_result->fetch_assoc()) {
        $hostname = $host['hostname'];
        $ip_address = $host['ip_address'];
        $project_id = $host['project_id'];

        // Generate data for the past 3 months at hourly intervals
        $start_date = new DateTime();
        $start_date->modify('-3 months');
        $end_date = new DateTime();
        $interval = new DateInterval('PT1H');
        $period = new DatePeriod($start_date, $interval, $end_date);

        $uptime = 0;
        $last_reset_month = $start_date->format('m');

        foreach ($period as $date) {
            $current_month = $date->format('m');
            if ($current_month != $last_reset_month) {
                $uptime = 0; // Reset uptime at the start of a new month
                $last_reset_month = $current_month;
            }

            $last_updated = $date->format('Y-m-d H:i:s');
            $disk_capacity = generateRandomData(10, 90);
            $proc_usage = generateRandomData(5, 80);
            $uptime += 3600; // Increment uptime by 1 hour (3600 seconds)

            $sql = "INSERT INTO system_status_history (ip_address, hostname, ansible_ping, disk_capacity, proc_usage, app_check, uptime, project_id, task_id, last_updated, last_responded)
                    VALUES ('$ip_address', '$hostname', 'pong', '$disk_capacity', '$proc_usage', 'running', '$uptime', '$project_id', 1, '$last_updated', '$last_updated')";

            if ($conn->query($sql) === TRUE) {
                echo "New record created successfully for $hostname at $last_updated\n";
            } else {
                echo "Error: " . $sql . "\n" . $conn->error;
            }
        }
    }
} else {
    echo "No hosts found in the system_status_history table.\n";
}

$conn->close();
?>