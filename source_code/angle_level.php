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
$result = mysqli_query($con, "SELECT max(session) as session from raw_logs where id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
    
}


//echo $session."</br>";


//get time period

$result = mysqli_query($con, "SELECT max(time) as max_time from raw_logs where session='$session' AND id='bfb76f71614533352b6107609ab99ff0'");
while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
    
}


//if no such record in database, min_time = 0

$check = mysqli_query($con,"SELECT * FROM angle WHERE  session=$session AND id='bfb76f71614533352b6107609ab99ff0'");



if (mysqli_num_rows($check)==0){
    
    $min_time=0;
}

else{

    $result = mysqli_query($con, "SELECT max(time) as time from angle where session='$session' AND id='bfb76f71614533352b6107609ab99ff0'");
    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
        $min_time = $time;
    }
}


if ($max_time==$min_time){
    
//------------------get scores
    $result=mysqli_query($con, "SELECT score from angle WHERE id='bfb76f71614533352b6107609ab99ff0' AND session=$session AND time=(SELECT max(time) from angle where id='bfb76f71614533352b6107609ab99ff0' AND session=$session)");

    while ($row=mysqli_fetch_assoc($result)){
        extract ($row);
    }

    //echo $score;
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


    //variable
    $angleScore=array();//[mod event, agg event]
    $angles=array(); //from database
    $bearings = array();//difference of two angles
    $moderate = array();//moderate bearing angle 10< angle < 40
    $aggressive = array();//aggressive bearing angle 40 < angle
    $LMH=array();//low medium high

    $agg_value=40;
    $mod_value=10;

    //---------------get gps from database
    
    
    $data = mysqli_query($con, "SELECT max(kff1204) as maxdist, kff1005, kff1006 FROM `raw_logs` WHERE session = $session  AND  time<=$max_time ORDER by time");

    $lat=array();
    $lon=array();
    $gpsAngle=array();
    while ($row=mysqli_fetch_assoc($data)){
        extract ($row);
        $lat[] = $kff1006;
        $lon[]=$kff1005;
    }
    
    //calculate bearing angle by two gps points
    $count = count($lat);
    for ($i=0; $i< $count-1; $i++){
         $lat1=$lat[$i];
        $lat2=$lat[$i+1];
        $lon1=$lon[$i];
        $lon2=$lon[$i+1];
        $y = sin($lat2-$lat1) * cos($lon2);
        $x = cos($lon1)*sin($lon2)-sin($lon1)*cos($lon2)* cos($lat2-$lat1);
        $brng = abs(rad2deg(atan($y/$x)));
        $bearings[]= $brng;
//        echo "angle: ".$brng."</br>";
        if ($brng>=$mod_value and $brng<=$agg_value){
            //echo "$bearing"." moderate  </br>";
            $moderate[]=$brng;    
        }
        elseif ($brng>$agg_value){
            //echo "$bearing"." aggressive</br>";
            $aggressive[]=$brng;
        }
    }
    
    //moderate event /km (cumulative value)
    $mod = round(count($moderate)/$maxdist,2);

    $mid=3;//moderate mid point
    $high=6;//moderate high point

    if ($mod<=$mid){
        $angleScore[]=1;
        $LMH[]='LOW';
    }
    elseif ($mod>$mid and $mod<$high){
        $angleScore[]=2;
        $LMH[]='MEDIUM';
    }
    else {
        $angleScore[]=4;
        $LMH[]='HIGH';
    }

    //aggresive event /km (cumulative value)
     $agg = round(count($aggressive)/$maxdist,2);


    $mid=2;// Placeholder
    $high=4;


    if ($agg<=$mid){
        $angleScore[]=1;
        $LMH[]='LOW';
    }
    elseif ($agg>$mid and $agg<$high){
        $angleScore[]=2;
        $LMH[]='MEDIUM';
    }
    else {
        $angleScore[]=4;
        $LMH[]='HIGH';
    }

    //-------maximum angle
    
    
    $angles_p=array();
    $bearings_p=array();
    
    $data = mysqli_query($con, "SELECT kff1005, kff1006 FROM `raw_logs` WHERE session = $session  AND time>= $min_time AND time<=$max_time ORDER by time");

    
    $lat=array();
    $lon=array();
    $gpsAngle=array();
    while ($row=mysqli_fetch_assoc($data)){
        extract ($row);
        $lat[] = $kff1006;
        $lon[]=$kff1005;
    }


    $count = count($lat);
    for ($i=0; $i< $count-1; $i++){
        $lat1=$lat[$i];
        $lat2=$lat[$i+1];
        $lon1=$lon[$i];
        $lon2=$lon[$i+1];
        $y = sin($lat2-$lat1) * cos($lon2);
        $x = cos($lon1)*sin($lon2)-sin($lon1)*cos($lon2)* cos($lat2-$lat1);
        $brng = abs(rad2deg(atan($y/$x)));
        $bearings_p[]= $brng;
    }
 
    $maxAngle=max($bearings_p);
    $mid=$mod_value;// Placeholder
    $high=$agg_values;

    if ($maxAngle>=$high){
        $angleScore[]=4;
        $LMH[]='HIGH';
    }
    elseif ($maxAngle>$mid and $maxAngle<$high){
        $angleScore[]=2;
        $LMH[]='MEDIUM';
    }
    else {
        $angleScore[]=1;
        $LMH[]='LOW';
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
    
    $ANGtotal=((($l_area-$l_md)*($l_cp)+($m_area-$m_md)*($m_cp)+($h_area-$h_md)*($h_cp))/($l_area+$m_area+$h_area))/100;



    //INSERT Score to database 
    $sql="INSERT INTO angle (session, id, score,time, aggEvent, modEvent, BRp) VALUES ($session,'bfb76f71614533352b6107609ab99ff0' ,$ANGtotal, $max_time, '$LMH[0]','$LMH[1]','$LMH[2]')";
    
    mysqli_query($con, $sql);





//------------------print scores
$result=mysqli_query($con, "SELECT score from angle WHERE id='bfb76f71614533352b6107609ab99ff0' AND session=$session AND time=(SELECT max(time) from angle where id='bfb76f71614533352b6107609ab99ff0' AND session=$session)");

while ($row=mysqli_fetch_assoc($result)){
    extract ($row);
}
    
    echo $fuzzy." ".round($score,2)*100;
}


require_once ('overall.php');

mysql_close($con);

}

// Return the response required by Torque

?>
