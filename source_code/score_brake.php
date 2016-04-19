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


//get surrounding id
$result=mysqli_query($con,"select avg(break) as brake from overall where id='bfb76f71614533352b6107609ab99ff0' ");


//
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
//    echo $id."</br>";
}

//echo $brake;
echo round($brake,4)*100;

    
mysql_close($con);


// Return the response required by Torque
//echo "OK!";

?>