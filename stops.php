<?php
function getPage($url)
{
    $ch = curl_init($url);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt( $ch, CURLOPT_HEADER, 0 );
$page = curl_exec($ch);
curl_close($ch);
return $page;
}
/*timetable["route_text_color"] = "000000"
  timetable["route_color"] = case timetable["short_name"]
  when /1? 31?/
    timetable["route_text_color"] = "FFFFFF"
    "00009C" #blue rapid
  when "900"
    timetable["route_text_color"] = "FFFFFF"
    "00009C" #blue rapid
  when "300"
    timetable["route_text_color"] = "FFFFFF"
    "00009C" #blue rapid
  when "200"
    "FF2400" #red rapid
  when "2" 
    "D9D919" #gold line
  when "3"
    "D9D919" #gold line
  when "4" 
    "32CD32" #green line
  when  "5"
    "32CD32" #green line
  when /7??/
    "845730" #Xpresso line
  else
    "FFFFFF" #default white
  end*/
// 
// http://developers.cloudmade.com/wiki/geocoding-http-api/Documentation
$conn = pg_connect("dbname=bus user=postgres password=snmc");
if (!$conn) {
  echo "An error occured.\n";
  exit;
}
echo "reverse geocode stops<br>";
$sql = "Select * from stops where name is null or suburb is null";
     $result_stops = pg_query($conn, $sql);
     if (!$result_stops) {
	echo("Error in SQL query: " . pg_last_error() ."<br>\n");
     }
     while ($stop = pg_fetch_assoc($result_stops)) {
	if ($stop['name'] == "") {
      echo "Processing ".$stop['geohash'] . " streetname ... ";
      $url = "http://geocoding.cloudmade.com/daa03470bb8740298d4b10e3f03d63e6/geocoding/v2/find.js?around=".($stop['lat']/10000000).",".($stop['lng']/10000000)."&distance=closest&object_type=road";
      $contents = json_decode(getPage($url));
      //print_r($contents);
      $name = $contents->features[0]->properties->name;
      echo "Saving $name ! <br>" ;
      $result_save = pg_query($conn, "UPDATE stops set name = '".pg_escape_string($name)."' where geohash = '{$stop['geohash']}' ");
			      if (!$result_save) {
	echo("Error in SQL query: " . pg_last_error() ."<br>\n");
			      }
	}
	if ($stop['suburb'] == "") {
      echo "Processing ".$stop['geohash'] . " suburb ... ";
	$sql = "select * from suburbs where the_geom @> 'POINT(".($stop['lng']/10000000)." ".($stop['lat']/10000000).")'::geometry";
     $result_suburbs = pg_query($conn, $sql);
     if (!$result_suburbs) {
	echo("Error in SQL query: " . pg_last_error() ."<br>\n");
     }
     $suburbs = "";
     while ($suburb = pg_fetch_assoc($result_suburbs)) {
	$suburbs .= $suburb['name_2006'].";";
     }
      echo "Saving $suburbs ! <br>" ;
      $result_save = pg_query($conn, "UPDATE stops set suburb = '".pg_escape_string($suburbs)."' where geohash = '{$stop['geohash']}' ");
			      if (!$result_save) {
	echo("Error in SQL query: " . pg_last_error() ."<br>\n");
			      }
     }
     flush();
     }
echo "reverse geocode timing points<br>";
$sql = "Select * from timing_point where suburb is null";
     $result_timingpoints = pg_query($conn, $sql);
     if (!$result_timingpoints) {
	echo("Error in SQL query: " . pg_last_error() ."<br>\n");
     }
     while ($timingpoint = pg_fetch_assoc($result_timingpoints)) {
	if ($timingpoint['suburb'] == "") {
      echo "Processing ".$timingpoint['name'] . " suburb ... ";
	$sql = "select * from suburbs where the_geom @> 'POINT(".($timingpoint['lng']/10000000)." ".($timingpoint['lat']/10000000).")'::geometry";
     $result_suburbs = pg_query($conn, $sql);
     if (!$result_suburbs) {
	echo("Error in SQL query: " . pg_last_error() ."<br>\n");
     }
     $suburbs = "";
     while ($suburb = pg_fetch_assoc($result_suburbs)) {
	$suburbs .= $suburb['name_2006'].";";
     }
      echo "Saving $suburbs ! <br>" ;
      $result_save = pg_query($conn, "UPDATE timing_point set suburb = '".pg_escape_string($suburbs)."' where name = '".pg_escape_string($timingpoint['name'])."'");
			      if (!$result_save) {
	echo("Error in SQL query: " . pg_last_error() ."<br>\n");
			      }
     }
     flush();
     }

?>

