<?php
#phpinfo();
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

$sqlorig = "SELECT DATE(date_column) AS date, status, COUNT(*) AS count
        FROM your_table
        WHERE date_column >= CURDATE() - INTERVAL 7 DAY
        GROUP BY DATE(date_column), status
        ORDER BY DATE(date_column), status";

$sql = "SELECT DATE(`start`) AS `date`, `status`, COUNT(*) AS `count`
	FROM semaphore_db.task
	WHERE `start` >= CURDATE() - INTERVAL 7 DAY 
	GROUP BY DATE(`start`), `status` 
	ORDER BY DATE(`start`), `status`";

$result = $conn->query($sql);

$success = ["name" => "success", "data" => []];
$stopped = ["name" => "stopped", "data" => []];
$error = ["name" => "error", "data" => []];

$dates = [];
$data = ["success" => &$success["data"], "error" => &$error["data"], "stopped" => &$stopped["data"]];

for ($i = 6; $i >= 0; $i--) {
    $date = (new DateTime())->modify("-$i days")->format('Y-m-d');
    $dates[$date] = ["success" => 0, "error" => 0, "stopped" => 0];
}
//print_r($dates);

//print_r($result);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

//	print_r($row);
    	
	$date = (new DateTime($row["date"]))->format('Y-m-d'); // Format as month-day
        $dates[$date][$row["status"]] = (int)$row["count"];
    }
}
print_r($dates);
foreach ($dates as $date => $values) {
    foreach ($values as $status => $count) {
        $data[$status][] = $count;
    }
}

$output = [array_values($success), array_values($error), array_values($stopped)];

header('Content-Type: application/json');
echo json_encode($output);

//remake array data for chart again!
//[
//    {
//        "name": "PRODUCT A",
//        "data": [
//            44,
//            55,
//            41,
//            67,
//            22,
//            43
//        ]
//},
$conn->close();
?>

