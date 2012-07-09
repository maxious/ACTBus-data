<?php
$file = "feed_info.txt";
$outhandle = fopen("output/" . $file, "w");
date_default_timezone_set('Australia/Melbourne');

echo "Processing $file \n";
$headers = Array();
if ($outhandle) {
            $headers = Array("feed_publisher_name","feed_publisher_url","feed_lang","feed_start_date","feed_end_date","feed_version");
            fputcsv($outhandle, $headers);
            
            $data = Array("bus.lambdacomplex.org","http://bus.lambdacomplex.org","en","20120623","20121223",date("c")); 
            fputcsv($outhandle, $data);
} else {
    echo "Error opening $file";
    exit(1);
}

?>

