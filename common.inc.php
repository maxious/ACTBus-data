<?php
function getPage($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $page = curl_exec($ch);
    curl_close($ch);
    return $page;
}

function cleanServiceID($serviceID){
    
    
    if (strpos($serviceID, "Vac-Weekday")) return "Weekday-SchoolVacation";
        if (strpos($serviceID, "SAT-Saturday")) return "Saturday";
        if (strpos($serviceID, "SUN-Sunday")) return "Sunday";
        if (strpos($serviceID, "MAST-Saturday")) return "Saturday";
        if (strpos($serviceID, "MAST-Sunday")) return "Sunday";
        if (strpos($serviceID, "xmas2011")) return "Christmas2011";
        if (strpos($serviceID, "MAST-Weekday")) return "Weekday";
        if (strpos($serviceID, "3DXS-Weekday")) return "Weekday-EndOfYearHolidays";
        die("Unknown service ID $serviceID");
}

function cleanRouteID($routeID) {
    $routeParts = explode("-",$routeID);
    return $routeParts[0];
}

function cleanStopName($name) {
    $name = str_replace(Array("from","before","after","opp","Service","Platform"),"",$name);
    $name = str_replace("Pk","Park",$name);
    $name = str_replace("Dr ","Drive ",$name);    
    $name = str_replace("Drive","Drive ",$name);
    $name = str_replace("Rd","Road",$name);
    $name = str_replace("Cr","Crescent",$name);
    $name = str_replace("Cct ","Circuit ",$name);
    $name = str_replace("Circuit","Circuit ",$name);
    $name = str_replace("Av ","Avenue ",$name);
    $name = str_replace("Avenue","Avenue ",$name);
    $name = str_replace("Bus","Bus Station",$name);
    $name = str_replace("Station Station","Station",$name);
    $name = str_replace("Station Station","Station",$name);
    $name = str_replace("  "," ",$name);
    return trim($name);
}
?>
