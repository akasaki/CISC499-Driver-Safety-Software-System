<?php
require_once ('creds.php');


// Connect to Database
$con = mysqli_connect($db_host, $db_user, $db_pass,$db_name);
if (mysqli_connect_errno())
  {
  //echo "Failed to connect to MySQL: " . mysqli_connect_error();
  die();
  }

//get current session
$result = mysqli_query($con, "SELECT max(session) as maxtime from raw_logs where id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
    $curr_session = $maxtime;
}



//if no such record in database, insert, otherwise update

$check = mysqli_query($con,"SELECT * FROM overall WHERE  session=$curr_session AND id='bfb76f71614533352b6107609ab99ff0'");

//get avg of speed score
$result = mysqli_query($con, "SELECT avg(score) as speed from speed where session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
    }

//get avg of accel score
$result = mysqli_query($con, "SELECT avg(score) as accel from accel where session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
    }

//get avg of break score
$result = mysqli_query($con, "SELECT avg(score) as break from break where session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
    }

//get avg of angle score
$result = mysqli_query($con, "SELECT avg(score) as angle from angle where session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
    }

//calculate overall total score

$total=round(0.25*$speed+0.25*$accel+0.25*$break+0.25*$angle,2);
$speed=round($speed,2);
$accel=round($accel,2);
$break=round($break,2);
$angle=round($angle,2);



////echo $total."    total score.</br>";

//if new records
if (mysqli_num_rows($check)==0){
    
    $sql="INSERT INTO overall (session, id, speed, accel, break, angle, total_score) VALUES ($curr_session,'bfb76f71614533352b6107609ab99ff0' , $speed, $accel, $break, $angle, $total)";
    
    mysqli_query($con, $sql);
       
}


//otherwise, update
else{
    
     $sql="UPDATE overall SET speed=$speed, accel=$accel, break=$break, angle=$angle, total_score=$total WHERE session=$curr_session AND id='bfb76f71614533352b6107609ab99ff0' ";
    
    mysqli_query($con, $sql);
    

}



mysql_close($con);


// Return the response required by Torque
//echo "OK!";

?>
