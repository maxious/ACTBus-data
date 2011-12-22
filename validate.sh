#!/bin/bash
files=(calendar calendar_dates routes trips stop_times stops fare_attributes feed_info agency shapes)

for FILE in ${files[@]}
do
    if [ ! -f output/$FILE.txt ];
    then
       echo "File $FILE does not exists"
    fi
done

python feedvalidator.py -l 9999 cbrfeed.zip
