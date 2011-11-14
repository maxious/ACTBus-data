<?php
function distance($lat1, $lng1, $lat2, $lng2, $roundLargeValues = false) {
    $pi80 = M_PI / 180;
    $lat1*= $pi80;
    $lng1*= $pi80;
    $lat2*= $pi80;
    $lng2*= $pi80;
    $r = 6372.797; // mean radius of Earth in km
    $dlat = $lat2 - $lat1;
    $dlng = $lng2 - $lng1;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $km = $r * $c;
    if ($roundLargeValues) {
        if ($km < 1)
            return floor($km * 1000);
        else
            return round($km, 2) . "k";
    }
    else
        return floor($km * 1000);
}

$file = "shapes.txt";
$debug = false;
$line = 0;
$inhandle = fopen("input/" . $file, "r");
$outhandle = fopen("output/" . $file, "w");

echo "Processing $file \n";
$headers = Array();
if ($inhandle) {

    $distance = 0;
    $lastshape = 0;
    $lastlat = 0;
    $lastlon = 0;
    while (($data = fgetcsv($inhandle, 1000, ",")) !== FALSE) {
        if ($line == 0) {
            $headers = $data;
            // add additional fields
            $headers = array_merge($headers, Array("shape_dist_traveled"));
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

            // distance processing
            if ($data[array_search("shape_id", $headers)] != $lastshape) {
                $distance = 0;
                $lastshape = $data[array_search("shape_id", $headers)];
            } else {
                $distance += distance($lastlat, $lastlon, $data[array_search("shape_pt_lat", $headers)], $data[array_search("shape_pt_lon", $headers)]);
            }
            $lastlat = $data[array_search("shape_pt_lat", $headers)];
            $lastlon = $data[array_search("shape_pt_lon", $headers)];
$data[array_search("shape_dist_traveled", $headers)] = $distance;

            if ($debug) {
                foreach ($data as $key => $value) {
                    echo "$line: {$headers[$key]} => $value \n";
                }
                echo "\n";
            } else {
                echo ".";
                if ($line % 100 == 0)
                    echo "$line\n";
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

