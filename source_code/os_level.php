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


//if no such record in database, min_time = 0

$check = mysqli_query($con,"SELECT * FROM speed WHERE  session=$curr_session AND id='bfb76f71614533352b6107609ab99ff0'");



if (mysqli_num_rows($check)==0){
    
    $min_time=0;
}

else{
    
    $result = mysqli_query($con, "SELECT max(time) as min_time from speed where session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
   
    }
}


if ($max_time==$min_time){
    $result=mysqli_query($con, "SELECT score from speed WHERE id='bfb76f71614533352b6107609ab99ff0' AND session=$curr_session AND time=(SELECT max(time) from speed where id='bfb76f71614533352b6107609ab99ff0' AND session=$curr_session)");

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
}

else{
    //SET LIMIT SPEED
    $limitSpeed=120;
    $OSscore=array();
    $LMH=array();

    
    //-----------------Relative Overspeed-------------------------

    $result = mysqli_query($con,"SELECT count(kd) as total FROM raw_logs WHERE session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0' AND time >= '$min_time' AND time<='$max_time'");

    while ($row = mysqli_fetch_assoc($result)) {
        extract ($row);
    }


    $result = mysqli_query($con,"SELECT count(kd) as osNum FROM raw_logs WHERE kd>'$limitSpeed' AND session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0' AND  time<=$max_time");

    while ($row = mysqli_fetch_assoc($result)) {
        extract ($row);
    }


    $OSt=$osNum/$total;//relative overspeed in [0,1]


    $mid=0.33;
    $high=0.66;


    if ($OSt<=$mid){
        $OSscore[]=1;
        $LMH[]='LOW';

    }
    elseif ($OSt>$mid and $OSt<$high){
        $OSscore[]=2;
        $LMH[]='MEDIUM';

        

    }
    else {
        $OSscore[]=4;
        $LMH[]='HIGH';

        

    }

    //---------------------maximum overspeed

    $result = mysqli_query($con,"SELECT max(kd) as maxSpeed FROM raw_logs WHERE session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0' AND time>= $min_time AND time<=$max_time");

    while ($row = mysqli_fetch_assoc($result)) {
        extract ($row);
        $OSp=$maxSpeed-$limitSpeed;//max overspeed
    }

    //echo 'Maximum Overspeed: '.$OSp."</br>";

    $mid=round(0.2*$limitSpeed,2);
    $high=round(0.4*$limitSpeed,2);

    if ($OSp<=$mid){
        $OSscore[]=1;
            $LMH[]='LOW';

    }
    elseif ($OSp>$mid and $OSp<$high){
        $OSscore[]=2;
            $LMH[]='MEDIUM';

    }
    else {
        $OSscore[]=4;
            $LMH[]='HIGH';

    }

    //---------------------avg overspeed

    $result = mysqli_query($con,"SELECT avg(kd) as avgOverSpeed FROM raw_logs WHERE kd>'$limitSpeed' AND session='$curr_session' AND id='bfb76f71614533352b6107609ab99ff0' AND time>= $min_time AND time<=$max_time");

    while ($row = mysqli_fetch_assoc($result)) {
        extract ($row);
        $OSa=round($avgOverSpeed-$limitSpeed,2);//average overspeed
    }


    $mid=round(0.15*$limitSpeed,2);
    $high=round(0.3*$limitSpeed,2);

    if ($OSa<=$mid){

        $OSscore[]=1;
            $LMH[]='LOW';

    }
    elseif ($OSa>$mid and $OSa<$high){
        $OSscore[]=2;
            $LMH[]='MEDIUM';

    }
    else {
        $OSscore[]=4;
            $LMH[]='HIGH';

    }

    
    $l_count=0;
    $m_count=0;
    $h_count=0;
    
    for ($i=0; $i<3; $i++){
        if ($LMH[$i]=='LOW'){$l_count++;}
        elseif ($LMH[$i]=='MEDIUM'){$m_count++;}
        elseif ($LMH[$i]=='HIGH'){$h_count++;}
    }
    
    
    //apply fuzzy rules
    if ($l_count==3){
        $fuzzy="LOW";
    }
    elseif ($l_count==2 and $m_count==1){
        $fuzzy="LOW";
    }
    elseif ($l_count==2 and $h_count==1){
        $fuzzy="MEDIUM";
    }
    elseif ($l_count==1 and $m_count==2){
        $fuzzy="LOW";
    }
    elseif ($l_count==1 and $h_count==2){
        $fuzzy="HIGH";
    }
    elseif ($l_count==2 and $m_count==1 and $h_count==1){
        $fuzzy="MEDIUM";
    }
    elseif ($m_count==3){
        $fuzzy="MEDIUM";
    }
    elseif ($m_count==2 and $h_count==1){
        $fuzzy="HIGH";
    }
    elseif ($h_count==3){
        $fuzzy="HIGH";
    }
    elseif ($m_count==1 and $h_count==2){
        $fuzzy="HIGH";
    }
    
    //apply defuzzy algorithm
    //x-axis centroid point
    $l_cp=20;
    $m_cp=50;
    $h_cp=80;
    
    //strength applied (membership degree)
    $l_md=$l_count*1/3;
    $m_md=$m_count*1/3;
    $h_md=$h_count*1/3;
    
    //area under the graph
    $l_area=$l_md*(40+40-(1-$l_md)*10)/2;
    $m_area=$m_md*(40+40-(1-$m_md)*5)/2;
    $l_area=$h_md*(40+40-(1-$h_md)*10)/2;
    
    $OStotal=((($l_area-$l_md)*($l_cp)+($m_area-$m_md)*($m_cp)+($h_area-$h_md)*($h_cp))/($l_area+$m_area+$h_area))/100;


   

    //--------------insert Scores into score database




    $sql="INSERT INTO speed (session, id, score,time, OSt,OSp,OSa) VALUES ($curr_session,'bfb76f71614533352b6107609ab99ff0' ,$OStotal, $max_time, '$LMH[0]','$LMH[1]','$LMH[2]')";


    mysqli_query($con, $sql);

    //if (mysqli_query($con, $sql)) {
    //    echo "New record updated successfully". "<br>";
    //} else {
    //    mysqli_query($con, "INSERT INTO driver_score (session, id, speed_s, speed_c) VALUES ($curr_session,'bfb76f71614533352b6107609ab99ff0' , $OStotal,$OStotal)");
    //    echo "Error: " . $sql . "<br>" . mysqli_error($con);
    //}

    //----------get score level



    $result=mysqli_query($con, "SELECT score from speed WHERE id='bfb76f71614533352b6107609ab99ff0' AND session=$curr_session AND time=(SELECT max(time) from speed where id='bfb76f71614533352b6107609ab99ff0' AND session=$curr_session)");

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


}
// Return the response required by Torque

?>
