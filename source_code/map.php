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
// Define the overlay, derived from google.maps.OverlayView
function Label(opt_options) {
    // Initialization
    this.setValues(opt_options);

    // Label specific
    var span = this.span_ = document.createElement('span');
    span.style.cssText = 'position: relative; left: -50%; top: -8px; ' +
                         'border: 1px solid blue; ' +
                         'padding: 2px; background-color: white';

    var div = this.div_ = document.createElement('div');
    div.appendChild(span);
    div.style.cssText = 'position: absolute; display: none';
}
Label.prototype = new google.maps.OverlayView();

// Implement onAdd
Label.prototype.onAdd = function() {
    var pane = this.getPanes().floatPane;
    pane.appendChild(this.div_);

    // Ensures the label is redrawn if the text or position is changed.
    var me = this;
    this.listeners_ = [
        google.maps.event.addListener(this, 'position_changed',
            function() { me.draw(); }),
        google.maps.event.addListener(this, 'text_changed',
            function() { me.draw(); })
    ];
};

// Implement onRemove
Label.prototype.onRemove = function() {
    var i, I;
    this.div_.parentNode.removeChild(this.div_);

    // Label is removed from the map, stop updating its position/text.
    for (i = 0, I = this.listeners_.length; i < I; ++i) {
        google.maps.event.removeListener(this.listeners_[i]);
    }
};

// Implement draw
Label.prototype.draw = function() {
    var projection = this.getProjection();
    var position = projection.fromLatLngToDivPixel(this.get('position'));

    var div = this.div_;
    try{
    div.style.left = position.x + 'px';
    }catch(e){
        alert(position);
    }
    div.style.top = position.y + 'px';
    div.style.display = 'block';

    this.span_.innerHTML = this.get('text').toString();
};

function initialize() {
    
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
        //bindMarkerToPolyines(marker, i);
    }
    
    // create an invisible marker
    labelMarker = new google.maps.Marker({
        position: myTrip[1],  
        map: map,
        visible: false,
        //title = speed[1]
    });
    
    var flightPath=new google.maps.Polyline({
        path:myTrip,
        strokeColor: '#800000',
        strokeOpacity: 0.75,
        strokeWeight: 4,
        map: map
    });

    var myLabel = new Label();
    

    // lets add an event listener, if you mouseover the line, i'll tell you the coordinates you clicked
    google.maps.event.addListener(flightPath, 'mouseover', function(e) {
        labelMarker.setPosition(e.latLng)
        myLabel.bindTo('position', labelMarker, 'position');
        myLabel.set('text', e.latLng.toString());
        myLabel.setMap(map);
    });
}

google.maps.event.addDomListener(window, 'load', initialize);
</script>
</head>
<body>
    <div id="map_canvas">
    </div>
</body>
</html>