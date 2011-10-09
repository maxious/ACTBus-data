#!/bin/bash
mkdir routes
ruby extract_timetables.rb
ruby fix_timing_points.rb

wget -c https://www.action.act.gov.au/googletransit/google_transit.zip
mkdir input 
python long_name_writer.py google_transit.zip
unzip google_transit.zip -d input
mkdir output
cp input/* output

files=( routes stops stop_times fare_attributes feed_info agency )

for file in ${files[@]}
do
	echo $file
	php $file.php
	if [ $? -eq 0 ] ; then
		echo "$file.txt processing failed"
		exit 1
	fi
done
echo "All GTFS files processed correctly"
zip -v -j cbrfeed output/*
