#!/bin/bash
mkdir routes
ruby extract_timetables.rb
ruby fix_timing_points.rb

wget -c https://www.action.act.gov.au/googletransit/google_transit.zip
mkdir data 
unzip -f google_transit.zip -d data

files=( routes stops feedinfo  )

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
zip -v -j cbrfeed data/*
