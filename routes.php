<?php
function clean_name($name) {
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
$file = "routes.txt";
$debug = false;
$line = 0;

$inhandle = fopen("input/" . $file, "r");
$outhandle = fopen("output/" . $file, "w");

echo "Processing $file \n";
$headers = Array();
if ($inhandle && $outhandle) {
    while (($data = fgetcsv($inhandle, 1000, ",")) !== FALSE) {
        if ($line == 0) {
            $headers = $data;
            // add additional fields
            $headers = array_merge($headers, Array("route_text_color", "route_color"));
            // save
            fputcsv($outhandle, $headers);
        } else {
            if ($debug) {
                echo "------\n";
                foreach ($data as $key => $value) {
                    echo "$line: {$headers[$key]} => $value \n";
                }
                echo "---\n";
            }
            $data[array_search("route_long_name", $headers)] = clean_name($data[array_search("route_long_name", $headers)]);
            $data[array_search("route_text_color", $headers)] = "000000";
            $data[array_search("route_color", $headers)] = "FFFFFF"; #default  black on white
            switch ($data[array_search("route_short_name", $headers)]) {
                case "900":
                    $data[array_search("route_text_color", $headers)] = "FFFFFF";
                    $data[array_search("route_color", $headers)] = "00009C"; #blue rapid
                case "300":
                    $data[array_search("route_text_color", $headers)] = "FFFFFF";
                    $data[array_search("route_color", $headers)] = "00009C"; #blue rapid
                case "200":
                    $data[array_search("route_color", $headers)] = "FF2400"; #red rapid
                case "2":
                    $data[array_search("route_color", $headers)] = "D9D919"; #gold line
                case "3":
                    $data[array_search("route_color", $headers)] = "D9D919"; #gold line
                case "4":
                    $data[array_search("route_color", $headers)] = "32CD32"; #green line
                case "5":
                    $data[array_search("route_color", $headers)] = "32CD32"; #green line
            };
            if (preg_match("/^1.|^31./", $data[array_search("route_short_name", $headers)])) {
                $data[array_search("route_text_color", $headers)] = "FFFFFF";
                $data[array_search("route_color", $headers)] = "00009C"; #blue rapid
            }
            if (preg_match("/^7../", $data[array_search("route_short_name", $headers)])) {
                $data[array_search("route_color", $headers)] = "845730"; #Xpresso line
            }
            
                          if ($debug) {
                foreach ($data as $key => $value) {
                    echo "$line: {$headers[$key]} => $value \n";
                }
                echo "\n";
            } else {
                echo ".";
                if ($line %100 == 0) echo "$line\n";
            }
            // save
            fputcsv($outhandle, $data);
        }

        $line++;
    }
} else {
    echo "Error opening $file";
}
?>
