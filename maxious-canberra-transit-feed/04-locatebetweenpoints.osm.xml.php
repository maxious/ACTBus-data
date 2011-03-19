<?php
header('Content-Type: application/xml');
echo "<?xml version='1.0' encoding='UTF-8'?>
<osm version='0.6' generator='xapi: OSM Extended API 2.0' xmlns:xapi='http://www.informationfreeway.org/xapi/0.6' 
xapi:uri='/api/0.6/*[bbox=148.98,-35.48,149.21,-35.15]' xapi:planetDate='20100630' xapi:copyright='2010 OpenStreetMap contributors' 
xapi:license='Creative commons CC-BY-SA 2.0' xapi:bugs='For assistance or to report bugs contact 80n80n@gmail.com' xapi:instance='zappyHyper'>
";
$conn = pg_connect("dbname=openstreetmap user=postgres password=snmc");
if (!$conn) {
  echo "An error occured.\n";
  exit;
}
$result_stops = pg_query($conn, "Select * FROM current_node_tags INNER JOIN current_nodes ON 
current_node_tags.id=current_nodes.id WHERE v LIKE '%bus%' ");
if (!$result_stops) {
  echo "An stops retirieve error occured.\n";
  exit;
}

while ($stop = pg_fetch_assoc($result_stops)) {
$stop['latitude'] = $stop['latitude']/10000000;
$stop['longitude'] = $stop['longitude']/10000000;

echo "<node id='{$stop['id']}' lat='{$stop['latitude']}' lon='{$stop['longitude']}' version='1' changeset='242919' 
user='latch' uid='6647' visible='true' timestamp='2007-08-22T05:03:00Z'>\n";
 $result_stopkeys = pg_query($conn, "SELECT * from current_node_tags where id = {$stop['id']};");
 if (!$result_stopkeys) {
   echo "An stops keys retirieve error occured.\n";
   exit;
 }
	$name = "";
	while ($stopkeys = pg_fetch_assoc($result_stopkeys)) {
		echo "<tag k='{$stopkeys['k']}' v='".htmlentities($stopkeys['v'])."'/>\n";
	}
	echo "</node>\n";
}

echo "\n</osm>\n";
?>
