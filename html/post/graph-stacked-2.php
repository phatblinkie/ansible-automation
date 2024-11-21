<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$data = [
  ["date" => "2024-11-05", "total_count" => 2, "statusvalue" => "error"],
  ["date" => "2024-11-05", "total_count" => 4, "statusvalue" => "success"],
  ["date" => "2024-11-04", "total_count" => 35, "statusvalue" => "error"],
];

echo json_encode($data);
?>

