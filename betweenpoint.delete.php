<?php
  
  $conn = pg_connect("dbname=bus user=postgres password=snmc");
  if (!$conn) {
      echo "An error occured.\n";
      exit;
  }
  if ($_REQUEST['oldgeopo']) {
    
      $sql = " DELETE from stops WHERE geohash = '{$_REQUEST['oldgeopo']}'";
      $result = pg_query($conn, $sql);
      if (!$result) {
          echo("Error in SQL query: " . pg_last_error() . "<br>\n");
      } else {
      echo "Deleted {$_REQUEST['oldgeopo']}<br>";
      $updatedroutes = 0;
      $result_outdatedroutes = pg_query($conn, "Select * FROM between_stops where points LIKE '%" . $_REQUEST['oldgeopo'] . ";%'");
      while ($outdatedroute = pg_fetch_assoc($result_outdatedroutes)) {
          $newpoints = str_replace($_REQUEST['oldgeopo'].';', '', $outdatedroute['points']);
          $sql = "UPDATE between_stops set points='$newpoints' where fromlocation = '{$outdatedroute['fromlocation']}' AND tolocation = '{$outdatedroute['tolocation']}' ";
          $result = pg_query($conn, $sql);
          if (!$result) {
              echo("Error in SQL query: " . pg_last_error() . "<br>\n");
          }
	    echo "updated ".$outdatedroute['fromlocation']."->".$outdatedroute['tolocation']."<br>";
        
          $updatedroutes++;
      }
      echo "updated $updatedroutes routes<br>";
      }
  }
  flush();
?>