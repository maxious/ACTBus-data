<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <script src="openlayers/OpenLayers.js"></script>
 <SCRIPT TYPE="text/javascript" SRC="OpenStreetMap.js"></SCRIPT> 
    <script type="text/javascript">

function init()
{
    var extent = new OpenLayers.Bounds(148.98, -35.48, 149.25, -35.15);
 
		// set up the map options
		var options = 
		{
			   maxExtent: extent,
			   numZoomLevels: 20, 
		}; 
 
		// create the ol map object
		var map = new OpenLayers.Map('map', options);
    
var osmtiles = new OpenLayers.Layer.OSM("local", "http://10.0.1.154/tiles/${z}/${x}/${y}.png");
// use http://open.atlas.free.fr/GMapsTransparenciesImgOver.php and http://code.google.com/p/googletilecutter/ to make tiles
 var graphic = new OpenLayers.Layer.Image(
                'Weekday Bus Map',
                'weekday_bus_map.png',
                new OpenLayers.Bounds(149.0, -35.47, 149.16, -35.16),
                new OpenLayers.Size(1191, 2268),
		{baseLayer: false}
            );

var nearmap = new OpenLayers.Layer.OSM.NearMap("NearMap");

    var routes = new OpenLayers.Layer.GML("Routes", "displayroutes.kml.php", {
        format: OpenLayers.Format.KML,
        formatOptions: {
            extractStyles: true,
            extractAttributes: true,
            maxDepth: 2
        }
    });
var stopicon = new OpenLayers.Icon("http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png",new OpenLayers.Size(32,32));
    var stops = new OpenLayers.Layer.GeoRSS("Stops", "displaystops.georss.php", { icon: stopicon });
var timeicon = new OpenLayers.Icon("http://maps.google.com/mapfiles/kml/pushpin/grn-pushpin.png",new OpenLayers.Size(32,32));
    var timepoints = new OpenLayers.Layer.GeoRSS("Timing Points", "displaytimepoints.georss.php", { icon: timeicon }); 

	map.addLayers([osmtiles,stops,routes,timepoints,nearmap]);

    var lonLat = new OpenLayers.LonLat(149.11, -35.28).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
    map.setCenter(lonLat, 13);
    map.addControl( new OpenLayers.Control.LayerSwitcher({'ascending':false}));
    map.addControl(new OpenLayers.Control.MousePosition(
    {
        displayProjection: new OpenLayers.Projection("EPSG:4326"),
        suffix: "__________________________________"
    }));
    map.addControl(new OpenLayers.Control.MousePosition(
    {
        displayProjection: new OpenLayers.Projection("EPSG:900913")
    }));

}
 
    </script>

  </head>
  <body onload="init()">
    <div id="map" width="100%" height="100%" class="smallmap"></div>
  </body>
</html>

