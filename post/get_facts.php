<?php
#phpinfo();

require_once ('/usr/share/PHP-MySQLi-Database-Class/MysqliDb.php');
//https://github.com/ThingEngineer/PHP-MySQLi-Database-Class?tab=readme-ov-file#join-method
//
$dbhost = 'localhost';
$dbuser = 'semaphore_user';
$dbpass = 'DFyuqwhjty34JK@#23@#';
$dbname = 'semaphore_db';

//use later in function
//$db = new MysqliDb ($dbhost, $dbuser, $dbpass, $dbname);

// Takes raw data from the request
//$json = file_get_contents('php://input');

// Converts it into a PHP object
//not really used but good to know
//$data = json_decode($json, true);


if (isset($_GET['queryid']))
{$queryid=$_GET['queryid'];}
else 
{
	echo '{"error": "missing queryid"}';
}

//define some queries to use that can be switched by changing the ?queryid=1  on the url
$query[1] = "SELECT facts_json FROM `task_extra_data` where `facts_json` -> '$.lo.mtu' > 0 limit 1;";

//assign which one
$query = $query[$queryid];

//$test = $db->rawQuery($query);
//print_r($test); array at this point
//Json return type

//$json = $db->JsonBuilder()->rawQuery($query);
//print_r($json);// returns json data


function getdata_from_mysql($query, $format)
{
$dbhost = 'localhost';
$dbuser = 'semaphore_user';
$dbpass = 'DFyuqwhjty34JK@#23@#';
$dbname = 'semaphore_db';
	
$db = new MysqliDb ($dbhost, $dbuser, $dbpass, $dbname);
//we dont need some data ias jsonbuilder, since its already in json format in sql db
if ($format == "json")
	{ //json
	$rows = $db->JsonBuilder()->rawQuery($query);
	}
elseif ($format == "array")
	{ //array
	$rows = $db->ArrayBuilder()->rawQuery($query);
	}
else 
	{ //object
	$rows = $db->ObjectBuilder()->rawQuery($query);
	}
//print_r($rows);// returns json data
//echo $json;

//$sqlstmt = Array ("task_id" => "$taskid",
//               "facts_json" => "$json"
//);
//print_r($sqlstmt);
//$db->insert ('task_extra_data', $sqlstmt);

if ($db->getLastErrno() === 0)
{
	//echo 'Update succesfull';
	if ($format == "json") {echo stripslashes($rows);}
	elseif ($format == "array") {echo stripslashes(json_encode($rows, JSON_PRETTY_PRINT));}
	else {
		foreach($rows as $data)
		{
		echo stripslashes(json_encode($data));
		//print_r($data);
		}
	}
	
}
else
{
	echo '{"error": "'.$db->getLastError().'"}';
}

} //end function

//phpinfo();

//print_r($data);
if ($queryid != null)
{
	getdata_from_mysql($query, 'array');
}
else
{
	echo '{"error": "missing queryid"}';
}
?>