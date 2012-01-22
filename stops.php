<?php

include 'common.inc.php';

// 
// http://developers.cloudmade.com/wiki/geocoding-http-api/Documentation
$conn = pg_connect("dbname=bus user=postgres password=snmc");
if (!$conn) {
    echo "An error occurred connecting to database.\n";
    exit(1);
}
$file = "stops.txt";
$debug = false;
// load merged stop operations
$mergeoperationshandle = fopen("tmp/merge.operations.txt", "r");
if ($mergeoperationshandle) {
    while (($data = fgetcsv($mergeoperationshandle, 1000, ",")) !== FALSE) {
    $deleteStops[] = $data[1]; // delete stops that are no longer referenced in stop_times
    }
}
$line = 0;
$inhandle = fopen("input/" . $file, "r");
$outhandle = fopen("output/" . $file, "w");

echo "Processing $file \n";
$headers = Array();
if ($inhandle) {
    while (($data = fgetcsv($inhandle, 1000, ",")) !== FALSE) {
        if ($line == 0) {
            $headers = $data;
            // add additional fields
            $headers = array_merge($headers, Array());
            // save
            fputcsv($outhandle, $headers);
        } else if (in_array($data[array_search("stop_id", $headers)], $deleteStops)) {
            echo "\nSkipping stop id ".$data[array_search("stop_id", $headers)]." because it is a redundant duplicate for name ".$data[array_search("stop_name", $headers)]."\n";
        } else {
            if ($debug) {
                echo "------\n";
                foreach ($data as $key => $value) {
                    echo "$line: {$headers[$key]} => $value \n";
                }
                echo "---\n";
            }
            if ($data[array_search("stop_code", $headers)] == "") {
                $data[array_search("stop_code", $headers)] = geopoEncode($data[array_search("stop_lat", $headers)], $data[array_search("stop_lon", $headers)]);
            }
            if ($data[array_search("stop_desc", $headers)] == "") {
                $sql = "select name from planet_osm_line where name != '' ORDER BY ST_Distance(way,ST_Transform(ST_GeomFromText('POINT(" . $data[array_search("stop_lon", $headers)] . " " . $data[array_search("stop_lat", $headers)] . ")',4326),900913)) limit 1";
                $result_street = pg_query($conn, $sql);
                if (!$result_street) {
                    echo("Error in SQL query: " . pg_last_error() . "<br>\n");
                    exit(1);
                }
                $street_row = pg_fetch_row($result_street);
                $street = $street_row[0];

                /*
                  // cloudmade street geocoder
                  $url = "http://geocoding.cloudmade.com/daa03470bb8740298d4b10e3f03d63e6/geocoding/v2/find.js?around=".$data[array_search("stop_lat", $headers)].",". $data[array_search("stop_lon", $headers)]."&distance=closest&object_type=road";
                  $contents = json_decode(getPage($url));
                  //print_r($contents);
                  $street = $contents->features[0]->properties->name; */
                $sql = "select ssc_name from suburbs where 'POINT(" . $data[array_search("stop_lon", $headers)] . " " . $data[array_search("stop_lat", $headers)] . ")'::geometry @ the_geom";
                $result_suburbs = pg_query($conn, $sql);
                if (!$result_suburbs) {
                    echo("Error in SQL query: " . pg_last_error() . "<br>\n");
                }
                $suburbs = "";
                while ($suburb = pg_fetch_assoc($result_suburbs)) {
                    $suburbs .= ($suburbs != "" ? "," : "") . str_replace(Array(" (ACT)", " (NSW)"), "", $suburb['ssc_name']);
                }
                $data[array_search("stop_desc", $headers)] = "Street: $street<br>Suburb: $suburbs";
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
    exit(1);
}
?>

