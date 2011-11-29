<?php

function getPage($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $page = curl_exec($ch);
    curl_close($ch);
    return $page;
}

/*
 * GeoPo Encode in PHP
 * @author : Shintaro Inagaki
 * @param $location (Array)
 * @return $geopo (String)
 */

function geopoEncode($lat, $lng) {
    // 64characters (number big and small letter hyphen underscore)
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

// 
// http://developers.cloudmade.com/wiki/geocoding-http-api/Documentation
$conn = pg_connect("dbname=bus user=postgres password=snmc");
if (!$conn) {
    echo "An error occured.\n";
    exit;
}
$file = "stops.txt";
$debug = false;
// load merged stop operations
$mergeoperationshandle = fopen("tmp/merge.operations.txt", "r");
if ($mergeoperationshandle) {
    while (($data = fgetcsv($stopshandle, 1000, ",")) !== FALSE) {
    $deleteStops = $data[1]; // delete stops that are no longer referenced in stop_times
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
        } else if (in_array($data[array_search("stop_lon", $headers)], $deleteStops)) {
            echo "Skipping stop id ".$data[array_search("stop_lon", $headers)]." because it is a redundant duplicate for name ".$data[array_search("stop_name", $headers)]."\n";
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
                $sql = "select name from planet_osm_line where name != '' ORDER BY ST_Distance(way,ST_Transform(GeomFromText('POINT(" . $data[array_search("stop_lon", $headers)] . " " . $data[array_search("stop_lat", $headers)] . ")',4326),900913)) limit 1";
                $result_street = pg_query($conn, $sql);
                if (!$result_street) {
                    echo("Error in SQL query: " . pg_last_error() . "<br>\n");
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
}
?>

