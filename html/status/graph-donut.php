<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$data = [44, 55, 41, 17, 15];

echo json_encode($data);
?>
