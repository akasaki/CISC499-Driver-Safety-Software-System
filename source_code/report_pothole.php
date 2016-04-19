<?php
require_once ('creds.php');
header('Content-Type: text/plain; charset=utf-8'); 

// Connect to Database
$con = mysqli_connect($db_host, $db_user, $db_pass,$db_name);
if (mysqli_connect_errno())
  {
//  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  die();
  }

//get current session
$result = mysqli_query($con, "SELECT max(session) as curr_session from raw_logs where id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
}

//echo $curr_session."</br>";

    
$result = mysqli_query($con, "SELECT max(time) as max_time from raw_logs where session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
}


//echo $max_time."</br>";

$result = mysqli_query($con, "SELECT kff1005,kff1006 from raw_logs where session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0' AND time=$max_time");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
} 

settype($kff1005, "float");
settype($kff1006, "float");

//INSERT POTHOLE DATA

$check = mysqli_query($con,"SELECT * FROM roadCondition WHERE cast(kff1005 as decimal(10,4)) = cast($kff1005 as decimal(10,4)) and cast(kff1006 as decimal(10,4)) = cast($kff1006 as decimal(10,4)) and type=1");

if (mysqli_num_rows($check)==0){

    $sql="INSERT INTO roadCondition VALUES (UUID(),$max_time,$kff1005,$kff1006,1)";

    mysqli_query($con, $sql);
    
 }

    echo "Pothole reported successfully.";

    mysql_close($con);



// Return the response required by Torque

?>
