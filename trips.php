<?php

include 'common.inc.php';
$file = "trips.txt";
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
            $headers = array_merge($headers, Array());
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
            $data[array_search("route_id", $headers)] = cleanRouteID($data[array_search("route_id", $headers)]);
            $data[array_search("service_id", $headers)] = cleanServiceID($data[array_search("service_id", $headers)]);

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
    exit(1);
}
?>

