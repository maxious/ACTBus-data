<?php

$debug = false;
$replaceStations = Array();
$stationStopIDs = Array();

$stopshandle = fopen("input/stops.txt", "r");
$mergeoperationshandle = fopen("tmp/merge.operations.txt", "w");
$line = 0;
echo "Processing stops to remove duplicates \n";
$headers = Array();
if ($stopshandle) {
    while (($data = fgetcsv($stopshandle, 1000, ",")) !== FALSE) {
        if ($line == 0) {
            $headers = $data;
        } else {
            if (preg_match("/Bus Station|Community Station/",
                    $data[array_search("stop_name", $headers)])) {
                $stopName = trim(str_replace(Array(" Arrivals"," Arrive Platform 3 Set down only."),"",$data[array_search("stop_name", $headers)]));
                $stopID = $data[array_search("stop_id", $headers)];
                if (!in_array($stopName, array_keys($stationStopIDs))) {
                    $stationStopIDs[$stopName] = $stopID;
                    if ($debug) echo "$stopName is $stopID from now on\n";
                } else {
                    $replaceStations[$stopID] = $stationStopIDs[$stopName];
                    // type, from, to
                      fputcsv($mergeoperationshandle, array("stop",$stopID,$stationStopIDs[$stopName]));
                    if ($debug) echo "$stopName @ $stopID is a duplicate of {$stationStopIDs[$stopName]}\n";
                }
            }
        }

        $line++;
    }
} else {
    echo "Error opening stops for dupe removal";
    exit(1);
}
$file = "stop_times.txt";
$line = 0;
$inhandle = fopen("input/" . $file, "r");
$outhandle = fopen("output/" . $file, "w");

echo "Processing $file \n";
$headers = Array();
$stopIDField = 0;
if ($inhandle) {
    while (($data = fgetcsv($inhandle, 1000, ",")) !== FALSE) {
        if ($line == 0) {
            $headers = $data;
            // add additional fields
            $headers = array_merge($headers, Array());
            $stopIDField = array_search("stop_id", $headers);
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
            if (in_array($data[$stopIDField], array_keys($replaceStations))) {
                $data[$stopIDField] = $replaceStations[$data[$stopIDField]];
            }

            if ($debug) {
                foreach ($data as $key => $value) {
                    echo "$line: {$headers[$key]} => $value \n";
                }
                echo "\n";
            } else {
                if ($line % 1000 == 0)
                echo ".";
                if ($line % 10000 == 0)
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

