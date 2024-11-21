<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$data = [
    [
        "name" => "PRODUCT A",
        "data" => [44, 55, 41, 67, 22, 43]
    ],
    [
        "name" => "PRODUCT B",
        "data" => [13, 23, 20, 8, 13, 27]
    ],
    [
        "name" => "PRODUCT C",
        "data" => [11, 17, 15, 15, 21, 14]
    ],
    [
        "name" => "PRODUCT D",
        "data" => [21, 7, 25, 13, 22, 8]
    ]
];

echo json_encode($data);
?>

