<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="utf-8"?> <feed xmlns="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss">';
echo '<title> Points</title>';
$conn = pg_connect("dbname=bus user=postgres password=snmc");
if (!$conn) {
  echo "An error occured.\n";
  exit;
}
$result_timepoints = pg_query($conn, "Select * FROM timing_point where lat is not null and lng is not null ");
if (!$result_timepoints) {
  echo "An timepoints retirieve error occured.\n";
  exit;
}

while ($timepoint = pg_fetch_assoc($result_timepoints)) {
 echo "<entry>";
 echo "<summary>".htmlspecialchars ($timepoint['name'])."</summary>";
 echo "<title>".htmlspecialchars($timepoint['name'])."</title>";
echo "<georss:point> ".($timepoint['lat']/10000001)." ".($timepoint['lng']/10000000)."</georss:point>";
echo "</entry>\n";
}

echo "\n</feed>\n";
?>
