<?php

$file = "agency.txt";
$line = 0;
$inhandle = fopen("input/" . $file, "r");
$outhandle = fopen("output/" . $file, "w");

echo "Processing $file \n";
$headers = Array();
$agencyID = 0;
if ($inhandle && $outhandle) {
    while (($data = fgetcsv($inhandle, 1000, ",")) !== FALSE) {
        if ($line == 0) {
            $headers = $data;
            // add additional fields
            $headers[] = "agency_fare_url";
            $headers[] = "agency_id";
            // save
            fputcsv($outhandle, $headers);
        } else {
            foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
            $data[array_search("agency_fare_url", $headers)] = "http://www.transport.act.gov.au/myway/fares.html";
            
            $data[array_search("agency_id", $headers)] = $agencyID++;

            foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
            // save
            fputcsv($outhandle, $data);
        }
        $line++;
        echo "\n";
    }
    // add additional entries

    $data = Array();
    $data[array_search("agency_name", $headers)] = "Deane's Buslines";
    $data[array_search("agency_url", $headers)] = "http://www.deanesbuslines.com.au/queanbeyan/";
    $data[array_search("agency_timezone", $headers)] = "Australia/Sydney";
    $data[array_search("agency_lang", $headers)] = "en";
    $data[array_search("agency_phone", $headers)] = "(02) 6299 3722";
    $data[array_search("agency_fare_url", $headers)] = "http://www.deanesbuslines.com.au/queanbeyan/faresandsections.html";
    $data[array_search("agency_id", $headers)] = $agencyID++;
    foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
    fputcsv($outhandle, $data);
    $data = Array();
    $data[array_search("agency_name", $headers)] = "Transborder Express";
    $data[array_search("agency_url", $headers)] = "http://www.transborder.com.au/";
    $data[array_search("agency_timezone", $headers)] = "Australia/Sydney";
    $data[array_search("agency_lang", $headers)] = "en";
    $data[array_search("agency_phone", $headers)] = "(02) 6299 3722";
    $data[array_search("agency_fare_url", $headers)] = "http://www.transborder.com.au/ticketing.html";
    $data[array_search("agency_id", $headers)] = $agencyID++;
    foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
    fputcsv($outhandle, $data);
    $data = Array();
    $data[array_search("agency_name", $headers)] = "Royale Coach: Airport Transfers";
    $data[array_search("agency_url", $headers)] = "http://www.royalecoach.com.au";
    $data[array_search("agency_timezone", $headers)] = "Australia/Sydney";
    $data[array_search("agency_lang", $headers)] = "en";
    $data[array_search("agency_phone", $headers)] = "1300 368 897";
    $data[array_search("agency_fare_url", $headers)] = "http://www.royalecoach.com.au/docs/timetable_canberra.pdf";
    $data[array_search("agency_id", $headers)] = $agencyID++;
    foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
    fputcsv($outhandle, $data);
    $data = Array();
    $data[array_search("agency_name", $headers)] = "Murrays Australia Limited";
    $data[array_search("agency_url", $headers)] = "http://www.murrays.com.au/";
    $data[array_search("agency_timezone", $headers)] = "Australia/Sydney";
    $data[array_search("agency_lang", $headers)] = "en";
    $data[array_search("agency_phone", $headers)] = "13 22 51";
    $data[array_search("agency_fare_url", $headers)] = "http://www.murrays.com.au/ExpressBooking.aspx";
    $data[array_search("agency_id", $headers)] = $agencyID++;
    foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
    fputcsv($outhandle, $data);
    $data = Array();
    $data[array_search("agency_name", $headers)] = "Greyhound Australia";
    $data[array_search("agency_url", $headers)] = "http://www.greyhound.com.au/";
    $data[array_search("agency_timezone", $headers)] = "Australia/Sydney";
    $data[array_search("agency_lang", $headers)] = "en";
    $data[array_search("agency_phone", $headers)] = "1300 473 946";
    $data[array_search("agency_fare_url", $headers)] = "http://www.deanesbuslines.com.au/queanbeyan/faresandsections.html";
    
    $data[array_search("agency_id", $headers)] = $agencyID++;
    fputcsv($outhandle, $data);
    foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
    $data = Array();
    $data[array_search("agency_name", $headers)] = "RailCorp CountryLink";
    $data[array_search("agency_url", $headers)] = "http://www.countrylink.info/";
    $data[array_search("agency_timezone", $headers)] = "Australia/Sydney";
    $data[array_search("agency_lang", $headers)] = "en";
    $data[array_search("agency_phone", $headers)] = "13 22 32";
    $data[array_search("agency_fare_url", $headers)] = "http://www.countrylink.info/travelling_with_us/reservations_and_tickets";
    
    $data[array_search("agency_id", $headers)] = $agencyID++;
    foreach ($data as $key => $value) {
                echo "$line: {$headers[$key]} => $value \n";
            }
    fputcsv($outhandle, $data);
} else {
    echo "Error opening $file";
    exit(1);
}
?>

