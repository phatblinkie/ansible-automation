<?php
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

// $sql = "SELECT date_format(`start`, '%m/%d/%y GMT')as date, status, COUNT(*) as count FROM semaphore_db.task where `start` >= current_timestamp() - interval 6 month group by date, status";

$sql = "SELECT DATE_FORMAT(`start`, '%Y-%b') AS `month`, `status`, COUNT(*) AS `count`
FROM semaphore_db.task
WHERE `start` >= CURRENT_TIMESTAMP() - INTERVAL 6 MONTH
GROUP BY `month`, `status`
ORDER BY STR_TO_DATE(`month`, '%Y-%b')";
$result = $conn->query($sql);

$dates = [];
$successData = [];
$errorData = [];
$stoppedData = [];

// Initialize dates and corresponding data arrays
/*for ($i = 7; $i >= 0; $i--) {
    $date = (new DateTime())->modify("-$i days")->format('m-d');
    $dates[] = $date;
    $successData[$date] = 0;
    $errorData[$date] = 0;
    $stoppedData[$date] = 0;
}
*/

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
	// $date = (new DateTime())->modify("-$i days")->format('m-d');
   	 $date = $row["month"]; 
	 $dates[] = $date;
    	 $successData[$date] = 0;
    	 $errorData[$date] = 0;
    	 $stoppedData[$date] = 0;
    }
}

//print_r($result);

if ($result->num_rows > 0) {
//    while ($row = $result->fetch_assoc()) {
	foreach ($result as $row){
	//$date = (new DateTime($row["date"]))->format('m-d');
//	print_r($row);
	$date = $row["month"];
            if ($row["status"] == "success") {
		    $successData[$date] += (int)$row["count"];
            } elseif ($row["status"] == "error") {
                $errorData[$date] += (int)$row["count"];
            } elseif ($row["status"] == "stopped") {
                $stoppedData[$date] += (int)$row["count"];
            }
    }
}

// Ensure all series have data for each date
$series = [
    ["name" => "success", "data" => []],
    ["name" => "error", "data" => []],
    ["name" => "stopped", "data" => []]
];
//print_r($successData);
//print_r($errorData);
//print_r($stoppedData);


$keys = array_keys($successData);
//print_r($keys);
//foreach ($dates as $date) {
foreach ($keys as $date)	{
    $series[0]["data"][] = $successData[$date];
    $series[1]["data"][] = $errorData[$date];
    $series[2]["data"][] = $stoppedData[$date];
}

$output = ["dates" => $keys, "series" => $series];

header('Content-Type: application/json');
echo json_encode($output);

$conn->close();
?>

