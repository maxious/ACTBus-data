<?php
/*
 * GeoPo Encode in PHP
 * @author : Shintaro Inagaki
 * @param $location (Array)
 * @return $geopo (String)
 */
function geopoEncode($lat, $lng) {
	// 64characters (number + big and small letter + hyphen + underscore)
	$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_";

	$geopo = "";
	$scale = 7;
	
	// Change a degree measure to a decimal number
	$lat = ($lat + 90) / 180 * pow(8, 10);
	$lng = ($lng + 180) / 360 * pow(8, 10);
	// Compute a GeoPo code from head and concatenate
	for($i = 0; $i < $scale; $i++) {
		$geopo .= substr($chars, floor($lat / pow(8, 9 - $i) % 8) + floor($lng / pow(8, 9 - $i) % 8) * 8, 1);
	}
	return $geopo;
}		

/*
 * GeoPo Decode in PHP
 * @author : Shintaro Inagaki
 * @param $geopo (String)
 * @return $location (Array)
 */
function geopoDecode($geopo) {
	// 64characters (number + big and small letter + hyphen + underscore)
	$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_";
	// Array for geolocation
	$location = array ();

	for ($i = 0; $i < strlen($geopo); $i++) {
		// What number of character that equal to a GeoPo code (0-63)
		$order = strpos($chars, substr($geopo, $i, 1));
		// Lat/Lng plus geolocation value of scale 
		$location['lat'] = $location['lat'] + floor($order % 8) * pow(8, 9 - $i);
		$location['lng'] = $location['lng'] + floor($order / 8) * pow(8, 9 - $i);
	}

	// Change a decimal number to a degree measure, and plus revised value that shift center of area
	$location['lat'] = $location['lat'] * 180 / pow(8, 10) + 180 / pow(8, strlen($geopo)) / 2 - 90;
	$location['lng'] = $location['lng'] * 360 / pow(8, 10) + 360 / pow(8, strlen($geopo)) / 2 - 180;
	$location['scale'] = strlen($geopo);

	return $location;
}	

$conn = pg_connect("dbname=bus user=postgres password=snmc");
if (!$conn) {
  echo "An error occured.\n";
  exit;
}
$xml = simplexml_load_file("04-locatebetweenpoints.osm");

/* Access the <rating> nodes of the first movie.
 * Output the rating scale, too. */
foreach ($xml->node as $node) {
	$geoPo = geopoEncode((float)$node['lat'],(float)$node['lon']);
	$node['lat'] = (int) ((float)$node['lat']*10000000);
	$node['lon'] = (int) ((float)$node['lon']*10000000);
	echo($node['lat'].",".$node['lon']."=$geoPo<br>");
   $sql = "INSERT INTO stops (geohash, lat, lng) VALUES('$geoPo','{$node['lat']}','{$node['lon']}')";
     $result = pg_query($conn, $sql);
     if (!$result) {
         echo("Error in SQL query: " . pg_last_error() ."<br>\n");
     }
flush();
    
}
?>
