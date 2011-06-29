<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <script src="openlayers/OpenLayers.js"></script>
 <SCRIPT TYPE="text/javascript" SRC="OpenStreetMap.js"></SCRIPT> 
<script type="text/javascript" src="jquery.1.3.2.min.js"></script>
    <script type="text/javascript">

function init()
{
    // create the ol map object
    var map = new OpenLayers.Map('map');
    
     var osmtiles = new OpenLayers.Layer.OSM("cloudmade", "http://b.tile.cloudmade.com/daa03470bb8740298d4b10e3f03d63e6/1/256/${z}/${x}/${y}.png")
var nearmap = new OpenLayers.Layer.OSM.NearMap("NearMap");

 // var osmtiles = new OpenLayers.Layer.OSM("local", "/tiles/${z}/${x}/${y}.png")
// use http://open.atlas.free.fr/GMapsTransparenciesImgOver.php and http://code.google.com/p/googletilecutter/ to make tiles
    markers = new OpenLayers.Layer.Markers("Between Stop Markers");
 
 //hanlde mousedown on regions that are not points by reporting latlng
OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {                
                defaultHandlerOptions: {
                    'single': true,
                    'double': false,
                    'pixelTolerance': 0,
                    'stopSingle': false,
                    'stopDouble': false
                },
 
                initialize: function(options) {
                    this.handlerOptions = OpenLayers.Util.extend(
                        {}, this.defaultHandlerOptions
                    );
                    OpenLayers.Control.prototype.initialize.apply(
                        this, arguments
                    ); 
                    this.handler = new OpenLayers.Handler.Click(
                        this, {
                            'click': this.trigger
                        }, this.handlerOptions
                    );
                }, 
 
                trigger: function(e) {
                    var lonlat = map.getLonLatFromViewPortPx(e.xy).transform(
            new OpenLayers.Projection("EPSG:900913"),
	    new OpenLayers.Projection("EPSG:4326")
            );
                    $('form input[name="newlatlng"]').val(lonlat.lat + ";" + lonlat.lon );
                }
 
            });
          var click = new OpenLayers.Control.Click();
                map.addControl(click);
                click.activate();
<?php
$conn = pg_connect("dbname=bus user=postgres password=snmc");
if (!$conn) {
	echo "An error occured.\n";
	exit;
}
$result_stops = pg_query($conn, "Select * FROM stops");
while ($stop = pg_fetch_assoc($result_stops)) {

	echo 'marker = new OpenLayers.Marker(new OpenLayers.LonLat(' . ($stop['lng'] / 10000000) . "," . ($stop['lat'] / 10000000) . ')
            .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            new OpenLayers.Projection("EPSG:900913") // to Spherical Mercator Projection
            ));';
	echo '
            marker.id="' . $stop['geohash'] . '";
            markers.addMarker(marker);
marker.events.register("mousedown", marker, function() {

document.getElementById("between_points").innerHTML += this.id+";";
document.getElementById("between_points").innerValue += this.id+";";
document.getElementById("response").innerHTML += this.id+";";
$(\'form input[name="oldgeopo"]\').val(this.id);
});
';
}
?>
var timeicon = new OpenLayers.Icon("icong.png",new OpenLayers.Size(16,16));
var timepoints = new OpenLayers.Layer.GeoRSS("Timing Points", "displaytimepoints.georss.php", { icon: timeicon });

            map.addLayers([osmtiles, nearmap, markers,timepoints]);
            map.addControl(new OpenLayers.Control.LayerSwitcher());
      map.zoomToExtent(markers.getDataExtent());
 }
    </script>
        <script type="text/javascript">
function submitBetween () {
        $.post("betweenpoint.submit.php", $("#inputform").serialize(), function(html){
        $("#response").html(html);
        clearForms();
	return false;
      });
};
function submitMove () {
        $.post("betweenpoint.move.php", $("#moveform").serialize(), function(html){
        $("#response").html(html);
	clearForms();
	return false;
      });
};
function submitDelete () {
        $.post("betweenpoint.delete.php", $("#moveform").serialize(), function(html){
        $("#response").html(html);
	clearForms();
	return false;
      });
};
function submitAdd () {
        $.post("betweenpoint.add.php", $("#moveform").serialize(), function(html){
        $("#response").html(html);
	clearForms();
	return false;
      });
};
function OnChange(dropdown)
{
    var myindex  = dropdown.selectedIndex
    var selValue = dropdown.options[myindex].value;
  $("#routes").val(selValue.split(":",2)[0]);
  fromto = selValue.split(":",2)[1];
  $("#from").val(fromto.split("->",2)[0]);
  $("#to").val(fromto.split("->",2)[1]);
 document.getElementById("between_points").innerHTML = "";
    return true;
}

// function will clear input elements on each form
function clearForms(){
document.getElementById("between_points").innerHTML = "";
  // declare element type
  var type = null;
  // loop through forms on HTML page
  for (var x=0; x<document.forms.length; x++){
    // loop through each element on form
    for (var y=0; y<document.forms[x].elements.length; y++){
      // define element type
      type = document.forms[x].elements[y].type
      // alert before erasing form element
      //alert('form='+x+' element='+y+' type='+type);
      // switch on element type
      switch(type){
        case "text":
        case "textarea":
        case "password":
        //case "hidden":
          document.forms[x].elements[y].value = "";
          break;
        case "radio":
        case "checkbox":
          document.forms[x].elements[y].checked = true;
          break;
        case "select-one":
          document.forms[x].elements[y].options[0].selected = true;
          break;
        case "select-multiple":
          for (z=0; z<document.forms[x].elements[y].options.length; z++){
            document.forms[x].elements[y].options[z].selected = false;
          }
        break;
      }
    }
  }
}
    </script>

  </head>
  <body onload="init()">
   <div id="inputpane"><form id="inputform">
<select name=selectPair onchange='OnChange(this.form.selectPair);'>
<option>Select a from/to pair...</option>
<?php
include ('spyc/spyc.php');
//$timetable = Spyc::YAMLLoad('../spyc.yaml');
$path = "maxious-canberra-transit-feed/output/";
$dhandle = opendir("maxious-canberra-transit-feed/output/");
// define an array to hold the files
$files = array();
$paths = array();
if ($dhandle) {
	// loop through all of the files
	while (false !== ($fname = readdir($dhandle))) {
		if (($fname != '.') && ($fname != '..')) {
			$timetable = Spyc::YAMLLoad("maxious-canberra-transit-feed/output/" . $fname);
			// Strip off individual platforms because it usually doesn't matter for routes
			$timetable["time_points"] = preg_replace("/\(Platform.*/", "", $timetable["time_points"]);
			for ($i = 0; $i < sizeof($timetable["time_points"]) - 1; $i++) {
				$key = trim($timetable["time_points"][$i]) . "->" . trim($timetable["time_points"][$i + 1]);
				if (strstr(@$paths[$key], ";" . $timetable["short_name"] . ";") === false) @$paths[$key].= $timetable["short_name"] . ";";
			}
		}
	}
}
ksort($paths);
$completedPaths = array();
$result_betweenstops = pg_query($conn, "Select * FROM between_stops");
while ($path = pg_fetch_assoc($result_betweenstops)) {
	$key = trim($path['fromlocation']) . "->" . trim($path['tolocation']);
	$completedPaths[$key].= trim($path['routes']);
}
$processed = 0;
foreach ($paths as $path => $routes) {
	if (!in_array($path, array_keys($completedPaths))) {
		echo "<option value=\"$routes:$path\"> $path ($routes) </option>\n";
		$processed++;
	}
	else {
		$completedRoutes = explode(";", $completedPaths[$path]);
		$incompleteRoutes = "";
		foreach (explode(";", $routes) as $route) {
			if (!in_array($route, $completedRoutes) && strstr($incompleteRoutes, ';' . $route . ';') === false) {
				$incompleteRoutes.= $route . ';';
			}
		}
		if ($incompleteRoutes != "") {
			echo "<option value=\"$incompleteRoutes:$path\"> $path ($incompleteRoutes) </option>\n";
			$processed++;
		}
	}
}
echo "</select>$processed";
?>
 from <input type="text" name="from" id="from"/>
 to <input type="text" name="to" id="to"/>
<br>
 on routes <input type="text" name="routes" id="routes"/>
Reverse? <input type="checkbox" name="reverse" id="reverse" checked="false"/> 
<input type="button" onclick="javascript:submitBetween()" value="Submit!">
<input type="button" value="Clear" onclick="javascript:clearForms()" title="Start clearForms() JavaScript function">
<br>
<textarea name="between_points" id="between_points" rows="1" cols="120"></textarea>
</form>
    <form id="moveform">
oldgeopo <input type="text" name="oldgeopo" id="oldgeopo"/>
newlatlng <input type="text" name="newlatlng" id="newlatlng" size="60"/>
 <input type="button" onclick="javascript:submitMove()" value="Move!">
 <input type="button" onclick="javascript:submitAdd()" value="Add!">
   <input type="button" onclick="javascript:submitDelete()" value="Delete!">
</form> 
<div id="response">
    <!-- Our message will be echoed out here -->
  </div>
</div>
   <div id="map" width="100%" height="100%"></div>
  </body>
</html>
