<?php

$file = "agency.txt";
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
            $headers = array_merge($headers, Array("agency_fare_url"));
            // save
            fputcsv($outhandle, $headers);
        } else {
            foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
            $data[array_search("agency_fare_url", $headers)] = "http://www.transport.act.gov.au/myway/fares.html"; 

            foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
            // save
            fputcsv($outhandle, $data);
        }

        $line++;
        echo "\n";
    }
} else {
    echo "Error opening $file";
}
?>

