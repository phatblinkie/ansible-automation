<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$data = [
    [
        "name" => "PRODUCT A",
	"data" => [20, 44, 55, 41, 67, 22, 43]
    ],
    [
        "name" => "PRODUCT B",
	"data" => [10, 13, 23, 20, 8, 13, 27]
    ],
    [
        "name" => "PRODUCT C",
	"data" => [5, 11, 17, 15, 15, 21, 14]
    ],
    [
        "name" => "PRODUCT D",
	"data" => [10, 21, 7, 25, 13, 22, 8]
    ]
];
$dataseries["dataseries"] = $data;
$dataseries["catagories"] = ['01/01/2011 GMT', '01/02/2011 GMT', '01/03/2011 GMT', '01/04/2011 GMT', '01/05/2011 GMT', '01/06/2011 GMT', '01/07/2011 GMT'];


//print_r($dataseries);
$catagories[] = ['01/01/2011 GMT', '01/02/2011 GMT', '01/03/2011 GMT', '01/04/2011 GMT', '01/05/2011 GMT', '01/06/2011 GMT', '01/07/2011 GMT'];


//echo json_encode($catagories);
echo json_encode($dataseries);
?>

