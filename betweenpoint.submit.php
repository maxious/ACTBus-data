<?php
$conn = pg_connect("dbname=bus user=postgres password=snmc");
if (!$conn) {
  echo "An error occured.\n";
  exit;
}
print_r($_REQUEST);
$reverse=(isset($_REQUEST["reverse"]) ? $_REQUEST["reverse"] : "off");
$from=pg_escape_string($_REQUEST["from"]);
$to=pg_escape_string($_REQUEST["to"]);
$routes=$_REQUEST["routes"] ;
$points=$_REQUEST["between_points"];
   $sql = "INSERT INTO between_stops (fromLocation, toLocation, points, routes) VALUES('$from','$to','$points','$routes')";
     $result = pg_query($conn, $sql);
     if (!$result) {
         echo("Error in SQL query: " . pg_last_error() ."<br>\n");
     }
     if ($reverse === "on") {
	$ep = explode(";",$points);
	$epr = array_reverse($ep); 
	$p = implode(";",$epr).";";
	$pointsString = substr($p,1);
$sql = "INSERT INTO between_stops ( toLocation, fromLocation, points, routes) VALUES('$from','$to','$pointsString','$routes')";
$result = pg_query($conn, $sql);
     if (!$result) {
         echo("Error in SQL query: " . pg_last_error() ."<br>\n");
     }
     }
flush();
    
?>
