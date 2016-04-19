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
$result = mysqli_query($con, "SELECT max(session) as session from raw_logs where id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
    
}



//echo $session."</br>";


$result = mysqli_query($con, "SELECT max(time) as max_time from raw_logs where session=$session AND id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
    
}


//if no such record in database, min_time = 0
//
$check = mysqli_query($con,"SELECT * FROM accel WHERE  session='$session' AND id='bfb76f71614533352b6107609ab99ff0'");



if (mysqli_num_rows($check)==0){
    
    $min_time=0;
}

else{


    $result = mysqli_query($con, "SELECT max(time) as min_time from accel where session='$session' AND id='bfb76f71614533352b6107609ab99ff0'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);

    }
}


if ($max_time==$min_time){
    $result=mysqli_query($con, "SELECT score from accel WHERE id='bfb76f71614533352b6107609ab99ff0' AND session=$session AND time=(SELECT max(time) from accel where id='bfb76f71614533352b6107609ab99ff0' AND session=$session)");

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

    //variables
    $speed=array();
    $all_speed=array();

    $times=array();
    $negAccel=array();
    $posAccel=array();
    $distance=array();
    $accel = array();
    $accScore = array();
    $modAcc = array();
    $modBre = array();
    $aggAcc = array();
    $aggBre = array();
    $breScore=array();
    $accLMH=array();
    $breLMH=array();


    $agg_value=2;
    $mod_value=1;
    
    //cumulative inputs
    $data = mysqli_query($con, "SELECT kd , max(kff1204) as maxdist, kff1266 FROM raw_logs WHERE session = '$session' and id = 'bfb76f71614533352b6107609ab99ff0' ORDER by time");


    while ($row=mysqli_fetch_assoc($data)){
        extract ($row);
        $speed[] = $kd;
        $times[]= $kff1266;

    } 


    $count = count($speed);
 
 for ($i=0; $i< $count-1; $i++){
//     echo "1st speed: ".$speed[$i]."</br>";
//     echo "2nd speed: ".$speed[$i+1]."</br>";
//     echo "1st time: ".$times[$i]."</br>";
//     echo "2nd time: ".$times[$i+1]."</br>";
     $time1=$times[$i];
     $time2=$times[$i+1];
     settype($time1, "float"); 
     settype($time2, "float"); 
//     echo "type: ".gettype($time1)."</br>";
     $timeDiff=$time2-$time1;
//     echo "time difference: ".$timeDiff."</br>";
     $acc = -($speed[$i] - $speed[$i+1])*1000/3600/$timeDiff;
//     echo "The accel is ".$acc."</br>";
     if ($acc>0){
         $posAccel[] = $acc;
         if ($acc>=$mod_value and $acc<$agg_value){
             $modAcc [] =$acc;
         }
         elseif ($acc>=$agg_value){
             $aggAcc[] =$acc;
         }
     }
     elseif ($acc<0){
         $negAccel[] = $acc;
         if ($acc<=-$mod_value and $acc>-$agg_value){
             $modBre[] = $acc;
         }
         elseif ($acc<=-$agg_value){
             $aggBre[] = $acc;
         }
     }    
     // $accel[] = $acc;
     //echo $accel[$i]."</br>";
}

 

//-------------------------Accel

    
    //number of moderate accel event /km
    $modACCp = round(count($modAcc)/$maxdist,2);

    $mid=12;//placeholder
    $high=16;


    if ($modACCp<=$mid){
        $accScore[]=1;
        $accLMH[]='LOW';
    }
    elseif ($modACCp>$mid and $modACCp<$high){
        $accScore[]=2;
        $accLMH[]='MEDIUM';
    }
    else {
        $accScore[]=4;
        $accLMH[]='HIGH';
    }


    //number of aggressive accel event /km

    $aggACCp = round(count($aggAcc)/$maxdist,2);


    $mid=1;// Placeholder
    $high=2;


    if ($aggACCp<=$mid){
        $accScore[]=1;
        $accLMH[]='LOW';
    }
    elseif ($aggACCp>$mid and $aggACCp<$high){
        $accScore[]=2;
        $accLMH[]='MEDIUM';
    }
    else {
        $accScore[]=4;
        $accLMH[]='HIGH';

    }


//max accel 
    
    $data = mysqli_query($con, "SELECT kd , kff1204,kff1266 FROM test_table WHERE session = '$session' and id = 'bfb76f71614533352b6107609ab99ff0' AND time>= $min_time AND time<=$max_time ORDER by time");
    
    $speed_p=array();
    $times_p=array();
    $acc_p=array();

    while ($row=mysqli_fetch_assoc($data)){
        extract ($row);
        $speed_p[] = $kd;
        $times_p[]= $kff1266;

    } 


    $count = count($speed_p);
 
    for ($i=0; $i< $count-1; $i++){
    //     echo "1st speed: ".$speed[$i]."</br>";
    //     echo "2nd speed: ".$speed[$i+1]."</br>";
    //     echo "1st time: ".$times[$i]."</br>";
    //     echo "2nd time: ".$times[$i+1]."</br>";
         $time1=$times_p[$i];
         $time2=$times_p[$i+1];
         settype($time1, "float"); 
         settype($time2, "float"); 
    //     echo "type: ".gettype($time1)."</br>";
         $timeDiff=$time2-$time1;
    //     echo "time difference: ".$timeDiff."</br>";
         $acc = -($speed_p[$i] - $speed_p[$i+1])*1000/3600/$timeDiff;
    //     echo "The accel is ".$acc."</br>";

          $acc_p[] = $acc;
         //echo $accel[$i]."</br>";
    }

    
    
    $maxACC=max($acc_p);
    $maxbrake=min($acc_p);
    
    if ($maxACC>=$agg_value){
        $accScore[]=4;
        $accLMH[]='HIGH';
    }
    
    elseif ($maxACC<=1){
        $accScore[]=1;
        $accLMH[]='LOW';
    }
    else{
        $accScore[]=2;
        $accLMH[]='MEDIUM';
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
    
    
    $acctotal=((($l_area-$l_md)*($l_cp)+($m_area-$m_md)*($m_cp)+($h_area-$h_md)*($h_cp))/($l_area+$m_area+$h_area))/100;


    //INSERT ACCEL DATA
    $sql="INSERT INTO accel (session, id, score,time, aggEvent, modEvent, GAp) VALUES ($session,'bfb76f71614533352b6107609ab99ff0' ,$acctotal, $max_time, '$accLMH[0]','$accLMH[1]','$accLMH[2]')";
    //

    mysqli_query($con, $sql);



    //--------------------------braking
    
    //number of moderate event /km

    $modBREp = count($modBre)/$maxdist;



    $mid=10;//placeholder
    $high=13;


    if ($modBREp<=$mid){
        $breScore[]=1;
        $breLMH[]='LOW';
    }
    elseif ($modBREp>$mid and $modBREp<$high){
        $breScore[]=2;
        $breLMH[]='MEDIUM';
    }
    else {
        $breScore[]=4;
            $breLMH[]='HIGH';

    }

    //number of aggresive accel event /km


    $aggBREp = round(count($aggBre)/$maxdist,2);


    $mid=3;// Placeholder
    $high=5;


    if ($aggBREp<=$mid){
        $breScore[]=1;
        $breLMH[]='LOW';
    }
    elseif ($aggBREp>$mid and $aggBREp<$high){
        $breScore[]=2;
        $breLMH[]='MEDIUM';
    }
    else {
        $breScore[]=4;
        $breLMH[]='HIGH';
    }


    
    
    //max brake
    
    
    if (abs($maxbrake)>=$agg_value){
        $breScore[]=4;
        $breLMH[]='HIGH';
    }
    
    elseif (abs($maxbrake)<=1){
        $breScore[]=1;
        $breLMH[]='LOW';
}
    else{
        $breScore[]=2;
        $breLMH[]='MEDIUM';
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
    
    
    $bretotal=((($l_area-$l_md)*($l_cp)+($m_area-$m_md)*($m_cp)+($h_area-$h_md)*($h_cp))/($l_area+$m_area+$h_area))/100;

    
    

    //INSERT BREAKING DATA

    
    $sql="INSERT INTO break (session, id, score,time, aggEvent, modEvent, GAn) VALUES ($session,'bfb76f71614533352b6107609ab99ff0' ,$bretotal, $max_time, '$breLMH[0]','$breLMH[1]','$breLMH[2]')";
    
    mysqli_query($con, $sql);







    //-----------------get ACCEL score

    $result=mysqli_query($con, "SELECT score from accel WHERE id='bfb76f71614533352b6107609ab99ff0' AND session=$session AND time=(SELECT max(time) from accel where id='bfb76f71614533352b6107609ab99ff0' AND session=$session)");

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
