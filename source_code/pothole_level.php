<?php
require_once ('creds.php');


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

//echo $kff1005." and ".$kff1006."</br>";
settype($kff1005, "float");
settype($kff1006, "float");

$low1005=$kff1005-0.0008;
$high1005=$kff1005+0.0008;


$low1006=$kff1006-0.0008;
$high1006=$kff1006+0.0008;

//echo $low1005." to ".$high1005."</br>";

//echo $low1006." to ".$high1006."</br>";

$result = mysqli_query($con,"SELECT count(roadCondition.id) as pothole FROM roadCondition WHERE cast(kff1005 as decimal)<=cast($high1005 as decimal) AND cast(kff1005 as decimal)>= cast($low1005 as decimal) AND cast(kff1006 as decimal)<= cast($high1006 as decimal) AND cast(kff1006 as decimal)>=cast($low1006 as decimal) AND type=1");

    while ($row = mysqli_fetch_assoc($result)) {
        extract ($row);
    }

//echo "pothole number: ".$pothole."</br>";


    $mid=1;
    $high=3;


    if ($pothole<=$mid){
        echo 'LOW';
    }
    elseif ($pothole>$mid and $pothole<$high){
        echo 'MEDIUM';

    }
    else {
        echo 'HIGH';
    }



    mysql_close($con);



// Return the response required by Torque

?>
