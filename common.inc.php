<?php
function cleanServiceID($serviceID){
    if (strpos($serviceID, "MAST-Weekday")) return "Weekday";
    if (strpos($serviceID, "SVac-Weekday")) return "WeekdaySchoolVacation";
        if (strpos($serviceID, "SAT-Saturday")) return "Saturday";
        if (strpos($serviceID, "SUN-Sunday")) return "Sunday";
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
