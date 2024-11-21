<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$data = [
    [
        "name" => "Success",
        "data" => [55, 62, 89, 66, 98, 72, 101, 75, 94, 120, 117, 139]
    ],
    [
        "name" => "Failure",
        "data" => [5, 6, 9, 22, 0, 7, 15, 25, 4, 1, 0, 21]
    ]
];

echo json_encode($data);
?>

