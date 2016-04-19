<?php
require_once ('creds.php');
header('Content-Type: text/plain; charset=utf-8'); 

// Connect to Database
$con = mysqli_connect($db_host, $db_user, $db_pass,$db_name);
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  die();
  }

//get current session
$result = mysqli_query($con, "SELECT max(session) as maxtime from raw_logs where id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
    $curr_session = $maxtime;
}

//echo $curr_session."</br>";

//get current time
//$curr_time=time();

$result = mysqli_query($con, "SELECT max(time) as curr_time from raw_logs where id='bfb76f71614533352b6107609ab99ff0' AND session=$curr_session");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
}

//echo $curr_time."</br>";
//get current gps

$result = mysqli_query($con, "SELECT kff1005 as lat, kff1006 as lon from raw_logs where id='bfb76f71614533352b6107609ab99ff0' and session= $curr_session and time=(SELECT max(time) from raw_logs where id='bfb76f71614533352b6107609ab99ff0' and session= $curr_session)");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
}

//echo $lat, $lon;

//get surrounding id
$result=mysqli_query($con,"SELECT id, count(id) as aggDriver from raw_logs where id!='bfb76f71614533352b6107609ab99ff0' AND (time between $curr_time-1 and $curr_time+1) AND (kff1005 between $lat-0.008 and $lat+0.008) AND (kff1006 between $lon-0.008 and $lon+0.008) AND id IN (select `id` from overall group by `id` having avg(`total_score`)>0.5)");

//
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
//    echo $id."</br>";
}
$mid=1;//placeholder
$high=3;
if ($aggDriver<=$mid){
    echo 'LOW';
}
elseif ($aggDriver>=$high){
    echo 'HIGH';
}
else{
    echo 'MEDIUM';
}


mysql_close($con);


// Return the response required by Torque
//echo "OK!";

?>
