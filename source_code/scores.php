<?php
require_once ('creds.php');

include('OScum.php');
include('accelcum.php');
include('anglecum.php');
// Connect to Database
$con = mysqli_connect($db_host, $db_user, $db_pass,$db_name);
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  die();
  }

//get current session

//$result = mysqli_query($con, "SELECT max(session) as maxtime from raw_logs where id='789ecbb09cf9ca79726f522d9782623f'");
//while ($row=mysqli_fetch_assoc($result)){
//    extract ($row);
//    $curr_session = $maxtime;
//}
//echo $curr_session."</br>";

$curr_session=1453655575694     ;

$max_time=$session;

//if no such record in database, insert, otherwise update

$check = mysqli_query($con,"SELECT * FROM overall WHERE  session=$curr_session AND id='789ecbb09cf9ca79726f522d9782623f'");

//get avg of speed score
$result = mysqli_query($con, "SELECT avg(score) as speed from speed where session='$curr_session' AND id='789ecbb09cf9ca79726f522d9782623f'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
    }

//get avg of accel score
$result = mysqli_query($con, "SELECT avg(score) as accel from accel where session='$curr_session' AND id='789ecbb09cf9ca79726f522d9782623f'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
    }

//get avg of break score
$result = mysqli_query($con, "SELECT avg(score) as break from break where session='$curr_session' AND id='789ecbb09cf9ca79726f522d9782623f'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
    }

//get avg of angle score
$result = mysqli_query($con, "SELECT avg(score) as angle from angle where session='$curr_session' AND id='789ecbb09cf9ca79726f522d9782623f'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
    }

//calculate overall total score

$total=round(0.5*$speed+0.2*$accel+0.2*$break+0.1*$angle,2);

echo $total."    total score.</br>";

//if new records
if (mysqli_num_rows($check)==0){
    
    $sql="INSERT INTO overall (session, id, speed, accel, break, angle, total_score) VALUES ($curr_session,'789ecbb09cf9ca79726f522d9782623f' , $speed, $accel, $break, $angle, $total)";
    
    if (mysqli_query($con, $sql)) {
        echo "New record INSERTED successfully". "<br>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }
       
}


//otherwise, update
else{
    
     $sql="UPDATE overall SET speed=$speed, accel=$accel, break=$break, angle=$angle, total_score=$total WHERE session=$curr_session AND id='789ecbb09cf9ca79726f522d9782623f' ";
    
    if (mysqli_query($con, $sql)) {
        echo "New record INSERTED successfully". "<br>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }
    

}



mysql_close($con);


// Return the response required by Torque
echo "OK!";

?>
