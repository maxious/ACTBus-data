<?php
  /*
   * GeoPo Encode in PHP
   * @author : Shintaro Inagaki
   * @param $location (Array)
   * @return $geopo (String)
   */
  function geopoEncode($lat, $lng)
  {
      // 64characters (number + big and small letter + hyphen + underscore)
      $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_";
      
      $geopo = "";
      $scale = 7;
      
      // Change a degree measure to a decimal number
      $lat = ($lat + 90) / 180 * pow(8, 10);
      $lng = ($lng + 180) / 360 * pow(8, 10);
      // Compute a GeoPo code from head and concatenate
      for ($i = 0; $i < $scale; $i++) {
          $geopo .= substr($chars, floor($lat / pow(8, 9 - $i) % 8) + floor($lng / pow(8, 9 - $i) % 8) * 8, 1);
      }
      return $geopo;
  }

  
  $conn = pg_connect("dbname=bus user=postgres password=snmc");
  if (!$conn) {
      echo "An error occured.\n";
      exit;
  }
  if ($_REQUEST['newlatlng']) {
      $latlng = explode(";", $_REQUEST['newlatlng']);
      $lat = (float)$latlng[0];
      $lng = (float)$latlng[1];
      
      $geoPo = geopoEncode($lat, $lng);
      $nodelat = (int)($lat * 10000000);
      $nodelon = (int)($lng * 10000000);
      echo($nodelat . "," . $nodelon . "=$geoPo<br>");
      $sql = "INSERT INTO stops (geohash,lat,lng) VALUES ('$geoPo', '$nodelat', '$nodelon')";
      $result = pg_query($conn, $sql);
      if (!$result) {
          echo("Error in SQL query: " . pg_last_error() . "<br>\n");
      } else {
      echo "Inserted new point at $geoPo <br>";
	}
  }
  flush();
?>