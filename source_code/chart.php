<html>
<head>
<script src= "https://cdn.zingchart.com/zingchart.min.js"></script>
<script> zingchart.MODULESDIR = "https://cdn.zingchart.com/modules/";
		ZC.LICENSE = ["569d52cefae586f634c54f86dc99e6a9","ee6b7db5b51705a13dc2339db3edaf6d"];
</script>
</head>
<?php
require_once ("./creds.php");

$con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $con) or die(mysql_error());

$result = mysql_query("SELECT `kd`, `time`
                       FROM `raw_logs` 
                       WHERE `session`=1456245924494 AND (`id` = '789ecbb09cf9ca79726f522d9782623f' or `id` = 'bfb76f71614533352b6107609ab99ff0')", $con) or die(mysql_error());
?>
<script language="javascript" type="text/javascript">
var myData=[<?php 
while($info=mysql_fetch_assoc($result))
    echo $info['kd'].',';
?>];
var myLabels=[<?php 
while($info=mysql_fetch_assoc($result))
    echo '"'.$info['time'].'",';
?>];
window.onload=function(){
zingchart.render({
    id:"myChart",
    width:"100%",
    height:400,
    data:{
    "type":"line",
    "title":{
        "text":"Data Pulled from MySQL Database"
    },
    "scale-x":{
        "labels":myLabels
    },
    "series":[
        {
            "values":myData
        }
</script>
<?php
/* Close the connection */
$mysqli->close(); 
?>
</html>