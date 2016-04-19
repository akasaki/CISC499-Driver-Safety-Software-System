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

//---------------------------GET OVERALL
//include ('overall.php');


$result = mysqli_query($con, "SELECT total_score from overall where session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
}


//---------------------------ROAD CONDITION

//POTHOLE

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

$result = mysqli_query($con,"SELECT count(roadCondition.id) as pothole FROM roadCondition WHERE kff1005<=$high1005 AND kff1005>=$low1005  AND kff1006<=$high1006 AND kff1006>=$low1006 AND type=1");

    while ($row = mysqli_fetch_assoc($result)) {
        extract ($row);
    }

//echo "pothole number: ".$pothole."</br>";


    $mid=1;
    $high=3;


    if ($pothole<=$mid){
        $potholeS=0;
    }
    elseif ($pothole>$mid and $pothole<$high){
        $potholeS=0.49;

    }
    else {
        $potholeS=1;
    }



//-----SLIPPER


$result = mysqli_query($con, "SELECT kff1005,kff1006 from raw_logs where session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0' AND time=$max_time");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
}



$result = mysqli_query($con,"SELECT count(roadCondition.id) as slipper FROM roadCondition WHERE kff1005<=$high1005 AND kff1005>=$low1005  AND kff1006<=$high1006 AND kff1006>=$low1006 AND type=2");

    while ($row = mysqli_fetch_assoc($result)) {
        extract ($row);
    }

//echo "slipper number: ".$slipper."</br>";


    $mid=1;
    $high=3;


    if ($slipper<=$mid){
        $slipperS=0;
    }
    elseif ($slipper>$mid and $slipper<$high){
        $slipperS=0.49;

    }
    else {
        $slipperS=1;
    }

//------------------------------Surronding

$result = mysqli_query($con, "SELECT kff1005 as lat, kff1006 as lon from raw_logs where id='bfb76f71614533352b6107609ab99ff0' and session= $curr_session and time=(SELECT max(time) from raw_logs where id='bfb76f71614533352b6107609ab99ff0' and session= $curr_session)");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
}

$result=mysqli_query($con,"SELECT id, count(id) as aggDriver from raw_logs where id!='bfb76f71614533352b6107609ab99ff0' AND (time between $curr_time-1 and $curr_time+1) AND (kff1005 between $lat-0.008 and $lat+0.008) AND (kff1006 between $lon-0.008 and $lon+0.008) AND id IN (select `id` from overall group by `id` having avg(`total_score`)>0.5)");

//
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
//    echo $id."</br>";
}
$mid=1;//placeholder
$high=3;
if ($aggDriver<=$mid){
    $surround=0;
}
elseif ($aggDriver>=$high){
    $surround=1;
}
else{
    $surround=0.49;
}
//
//echo "profile: ".$total_score."</br>";
//echo "pothole: ".$potholeS."</br>";
//echo "slipper: ".$slipperS."</br>";
//echo "Surrounding: ".$surround."</br>";


 $risk=0.5*$total_score+0.1*$potholeS+0.1*$slipperS+0.3*$surround;


if ($risk>=0.5){ // Not right here... where is $
    echo "RISKY";
    // echo $risk;
}
else{
    echo "SAFE";
    // echo $risk;
}




    mysql_close($con);



// Return the response required by Torque

?>
