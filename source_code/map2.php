<title>Polyline with label on click</title>

<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
html { height: 100% }
body { height: 100%; margin: 0; padding: 0 }
#map_canvas { height: 100% }
</style>
<?php 
    require_once ("./creds.php");
    $con = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
    mysql_select_db($db_name, $con) or die(mysql_error());

    $sessionqry = mysql_query("SELECT kff1006, kff1005, kd, kff1204
                          FROM $db_table
                          WHERE session=1453655575694
                          ORDER BY time DESC", $con) or die(mysql_error());
    $geolocs = array();
    $speed = array();
    $distance = array();
    while($data = mysql_fetch_array($sessionqry)) {
        if (($data["0"] != 0) && ($data["1"] != 0)) {
            $geolocs[] = array("lat" => $data["0"], "lon" => $data["1"], "speed" => $data["2"]);
        }
    }
    
    // Create array of Latitude/Longitude strings in Google Maps JavaScript format
    $mapdata = array();
    $mapspeed = array();
    foreach($geolocs as $d) {
        $mapdata[] = "new google.maps.LatLng(".$d['lat'].", ".$d['lon'].")";
        $mapspeed[] =$d['speed'];
    }
    $imapdata = implode(",\n                    ", $mapdata);
    $imapdata2 = implode(",\n                    ", $mapspeed);
    mysql_free_result($sessionqry);
    mysql_close($con);
    
?>

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
window.onload = function () {
    
    var map = new google.maps.Map(document.getElementById("map_canvas"), {
        zoom: 5,
        center: new google.maps.LatLng(44.2609,-76.5602),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: true,
        mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
            poistion: google.maps.ControlPosition.TOP_RIGHT,
            mapTypeIds: [google.maps.MapTypeId.ROADMAP,
                         google.maps.MapTypeId.TERRAIN,
                         google.maps.MapTypeId.HYBRID,
                         google.maps.MapTypeId.SATELLITE]
        },
        navigationControl: true,
        navigationControlOptions: {
            style: google.maps.NavigationControlStyle.ZOOM_PAN
        },
        scaleControl: true,
        disableDoubleClickZoom: false,
        draggable: true,
        streetViewControl: true,
        draggableCursor: 'move'
    });
    
    var myTrip=[<?php echo $imapdata; ?>];
    // var distance = [<?php echo $distance; ?>];
    var speed = [<?php echo $imapdata2; ?>];
    
    // Create a boundary using the path to automatically configure
    // the default centering location and zoom.
    var bounds = new google.maps.LatLngBounds();
    for (i = 0; i < myTrip.length; i++) {
        bounds.extend(myTrip[i]);
    }
    map.fitBounds(bounds);
    
    for (i = 0; i < myTrip.length; i++){
        var marker = new google.maps.Marker({
            position: myTrip[i],
            map: map,
            visible: false,
            title: speed[i].text
        })
        
    }
    
    // create an invisible marker
    labelMarker = new google.maps.Marker({
        position: myTrip[1],  
        map: map,
        visible: false,
    });
    
    var flightPath=new google.maps.Polyline({
        path:myTrip,
        strokeColor: '#800000',
        strokeOpacity: 0.75,
        strokeWeight: 4,
        map: map
    });
    
    //Create InfoWindow.
    var infoWindow = new google.maps.InfoWindow();
    
    //Attach click event handler to the map.
    google.maps.event.addListener(flightPath, 'mouseover', function (e) {

        //Determine the location where the user has clicked.
        var location = e.latLng;
        var latitude = Math.floor(location.lat()*10000+0.5)/10000;
        var longitude = Math.floor(location.lng()*10000+0.5)/10000;

        //Set Content of InfoWindow.
        infoWindow.setContent('Latitude: ' + latitude + '<br />Longitude: ' + longitude);

        //Set Position of InfoWindow.
        infoWindow.setPosition(location);

        //Open InfoWindow.
        infoWindow.open(map);
    });
    
};
</script>
</head>
<body>
    <div id="map_canvas">
    </div>
</body>
</html>