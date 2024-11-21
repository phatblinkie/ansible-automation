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
$json = file_get_contents('php://input');

// Converts it into a PHP object
//not really used but good to know
$data = json_decode($json, true);


if ($_GET['taskid'] != null)
{$taskid=$_GET['taskid'];}



function put_array_into_mysql($json, $data, $taskid)
{
//table create
/*
CREATE TABLE `task_extra_data` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `data` varchar(255) NOT NULL,
  `facts_json` json DEFAULT NULL COMMENT 'for full fact data set'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='for storing extra information manually in plays';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `task_extra_data`
--
ALTER TABLE `task_extra_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `task_extra_data`
--
ALTER TABLE `task_extra_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
 */
$dbhost = 'localhost';
$dbuser = 'semaphore_user';
$dbpass = 'DFyuqwhjty34JK@#23@#';
$dbname = 'semaphore_db';
	
$db = new MysqliDb ($dbhost, $dbuser, $dbpass, $dbname);
$sqlstmt = Array ("task_id" => "$taskid",
               "facts_json" => "$json"
);
//print_r($sqlstmt);
$db->insert ('task_extra_data', $sqlstmt);

if ($db->getLastErrno() === 0)
  {echo 'Update succesfull';}
else
  {echo 'Update failed. Error: '. $db->getLastError();}

}  //end function

//phpinfo();

//print_r($data);
if (is_array($data) and ($taskid != null))
{
//echo "this is hard {$data['user_id']}";
//we got a valid json parse, lets split it up and and put it into a table

//for this script, its all or nothing for ansible facts full bore.
put_array_into_mysql($json, $data, $taskid);
}
else
{
  if (!is_array($data)){ echo "invalid json data";}
  if ($taskid = null) { echo "task id value not found";}
}
?>
