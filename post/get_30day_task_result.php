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
$query[1] = "SELECT 
  DATE(start) AS date, 
  COUNT(*) AS total_count,
  status as statusvalue
  FROM semaphore_db.task
  WHERE DATE_SUB(NOW(), INTERVAL 1 month) < start 
  GROUP BY statusvalue, date
  order by date desc;";

//assign which one
$query = $query[$queryid];


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

if ($db->getLastErrno() === 0)
{
	//echo 'Update succesfull';
	if ($format == "json" || $format == "array") {
		stripslashes(print_r($rows));
	}
	else {
		print_r($rows);
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
	getdata_from_mysql($query, "json");
}
else
{
	echo '{"error": "missing queryid"}';
}
?>
