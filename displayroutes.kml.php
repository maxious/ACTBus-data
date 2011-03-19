<?php
header('Content-Type: application/vnd.google-earth.kml+xml');
echo '<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2"><Document>';
echo '
    <Style id="yellowLineGreenPoly">
      <LineStyle>
        <color>7f00ff00</color>
        <width>4</width>
      </LineStyle>
      <PolyStyle>
        <color>7f00ffff</color>
      </PolyStyle>
	</Style>';
$conn = pg_connect("dbname=openstreetmap user=postgres password=snmc");
if (!$conn) {
  echo "An error occured.\n";
  exit;
}

$result_route = pg_query($conn, "SELECT * from current_relation_tags, (Select id FROM current_relation_tags WHERE k = 'route' AND v = 'bus') as a 
where a.id = current_relation_tags.id and k = 'ref';");
if (!$result_route) {
  echo "An route retirieve error occured.\n";
  exit;
}

while ($route = pg_fetch_assoc($result_route)) {
 echo "\n<Placemark>\n";
 echo "<name>".$route['v']." position at ".$route['id']."</name>";
 echo "<description>".$route['v']." position at ".$route['id']."</description>";
echo "<styleUrl>#yellowLineGreenPoly</styleUrl>";
echo "      <LineString>
        <extrude>1</extrude>
        <coordinates> ";
$result_way = pg_query($conn, 'SELECT member_id, sequence_id FROM "current_relation_members" WHERE "id" = '.$route['id'].' order by "sequence_id" 
ASC');
if (!$result_way) {
  echo "An way retirieve error occured.\n";
  exit;
}
  $count = 0;

while ($way = pg_fetch_assoc($result_way)) {
	$result_node = pg_query($conn, 'SELECT * FROM current_nodes INNER JOIN current_way_nodes ON current_way_nodes.node_id=current_nodes.id WHERE 
current_way_nodes.id = '.$way['member_id'].' order by "sequence_id" ASC');
	if (!$result_node) {
	  echo "An node retirieve error occured.\n";
	  exit;
	}

	while ($node = pg_fetch_assoc($result_node)) {
   $count++;
		echo ($node['longitude']/10000000).",".($node['latitude']/10000000).",600 \n";
	}
}
	if ($count == 0) echo (0).",".(0).",600 \n";
echo "        </coordinates>
      </LineString>";
echo '</Placemark>';
}

echo "\n</Document></kml>\n";
?>
