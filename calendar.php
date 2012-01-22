<?php

include 'common.inc.php';
$file = "calendar.txt";
$debug = true;
$line = 0;

$inhandle = fopen("input/" . $file, "r");
$outhandle = fopen("output/" . $file, "w");
echo "Processing $file <br>\n";
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
                echo "------<br>\n";
                foreach ($data as $key => $value) {
                    echo "$line: {$headers[$key]} => $value <br>\n";
                }
                echo "---<br>\n";
            }
            $data[array_search("service_id", $headers)] = cleanServiceID($data[array_search("service_id", $headers)]);

            if ($debug) {
                foreach ($data as $key => $value) {
                    echo "$line: {$headers[$key]} => $value <br>\n";
                }
                echo "<br>\n";
            } else {
                echo ".";
                if ($line % 100 == 0)
                    echo "$line<br>\n";
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
fclose($inhandle);
fclose($outhandle);
?>

