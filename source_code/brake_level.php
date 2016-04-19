<?php
require_once ('creds.php');


// Connect to Database
$con = mysqli_connect($db_host, $db_user, $db_pass,$db_name);
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  die();
  }

//get current session
$result = mysqli_query($con, "SELECT max(session) as curr_session from raw_logs where id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
    
}


//echo $curr_session."</br>";

$result=mysqli_query($con, "SELECT score from break WHERE id='bfb76f71614533352b6107609ab99ff0' AND session=$curr_session AND time=(SELECT max(time) from break where id='bfb76f71614533352b6107609ab99ff0' AND session=$curr_session)");

while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
}

if ($score>=0.5){
    echo "HIGH ".round($score,2)*100;
}

elseif ($score<=0.25){
    echo "LOW ".round($score,2)*100;
}

else{
    echo "MEDIUM ".round($score,2)*100;
}

require_once ('overall.php');


mysql_close($con);



// Return the response required by Torque

?>
