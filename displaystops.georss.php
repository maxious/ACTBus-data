<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" 
  xmlns:georss="http://www.georss.org/georss"><title>Bus Stops from OSM</title>';
$conn = pg_connect("dbname=bus user=postgres password=snmc");
if (!$conn) {
  echo "An error occured.\n";
  exit;
}
$result_stops = pg_query($conn, "Select * FROM stops ");
if (!$result_stops) {
  echo "An stops retirieve error occured.\n";
  exit;
}

while ($stop = pg_fetch_assoc($result_stops)) {
 echo "\n<entry>\n";
 echo "<summary> {$stop['geohash']}</summary>";
 echo "<title>{$stop['geohash']}</title>";

echo "<georss:point> ";echo ($stop['lat']/10000000)." ".($stop['lng']/10000000);
echo "        </georss:point>";
echo '</entry>';
}

echo "\n</feed>\n";
?>
