#!/bin/bash
#for f in output/*
#do
#	echo "Processing $f"
#	sed -ir 's/^- /  - /g' $f 
#	sed -ir 's/  - - /- - /g' $f
#done

sed -i "s/- ---/- /g" cbrtable.yml
