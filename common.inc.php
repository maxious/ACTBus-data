<?php

function getPage($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $page = curl_exec($ch);
    curl_close($ch);
    return $page;
}

function cleanServiceID($serviceID) {


    /* if (strpos($serviceID, "Vac-Weekday"))
      return "Weekday-SchoolVacation";
      if (strpos($serviceID, "MAST-Weekday"))
      return "Weekday";
      if (strpos($serviceID, "MAST-Saturday"))
      return "Saturday";
      if (strpos($serviceID, "MAST-Sunday"))
      return "Sunday";
     */
    $mapping = Array(
        "2012-COMBMAST-Saturday-04" => "Saturday12.0",
        "2012-COMBMAST-Saturday-05" => "Saturday12.1",
        "2013-COMBMAST-Saturday-01" => "Saturday13.0",
        
        "2012-COMBMAST-Sunday-05" => "Sunday12.0",
        "2012-xmas2012-Sunday-00" => "Xmas-Sunday",
        "2013-COMBMAST-Sunday-01" => "Sunday13.0",
        
        "2012-COMBMAST-Weekday-14" => "Weekday12.0",
        "2012-COMBMAST-Weekday-12" => "Weekday12.1",
        "2012-MODXMAS-Weekday-01"  => "Xmas-Weekday12.1",
        "2013-MODXMAS-Weekday-01"  => "Xmas-Weekday13.0",
        "2013-COMBSVac-Weekday-01" => "Weekday13.0"
    );
    return $mapping[$serviceID];

    die("Unknown service ID $serviceID");
}

function cleanRouteID($routeID) {
    $routeParts = explode("-", $routeID);
    return $routeParts[0];
}

function cleanStopName($name) {
    $name = str_replace(Array("from", "before", "after", "opp", "Service", "Platform"), "", $name);
    $name = str_replace("Pk", "Park", $name);
    $name = str_replace("Dr ", "Drive ", $name);
    $name = str_replace("Drive", "Drive ", $name);
    $name = str_replace("Rd", "Road", $name);
    $name = str_replace("Cr", "Crescent", $name);
    $name = str_replace("Cct ", "Circuit ", $name);
    $name = str_replace("Circuit", "Circuit ", $name);
    $name = str_replace("Av ", "Avenue ", $name);
    $name = str_replace("Avenue", "Avenue ", $name);
    $name = str_replace("Bus", "Bus Station", $name);
    $name = str_replace("Station Station", "Station", $name);
    $name = str_replace("Station Station", "Station", $name);
    $name = str_replace("  ", " ", $name);
    return trim($name);
}

?>
