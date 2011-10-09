<?php
$file = "fare_attributes.txt";
$outhandle = fopen("output/" . $file, "w");

echo "Processing $file \n";
$headers = Array();
if ($outhandle) {
            $headers = Array("fare_id","price","currency_type","payment_method","transfers","transfer_duration");
            fputcsv($outhandle, $headers);
            
            $data = Array("cash_adult_on_peak","2.52","AUD","0","","5400"); // 5400 sec == 90 minutes
            fputcsv($outhandle, $data);
} else {
    echo "Error opening $file";
}

?>

