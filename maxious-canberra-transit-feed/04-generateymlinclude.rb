#!/usr/bin/ruby

require 'highline.rb'
include HighLine

#
# GeoPo Encode in Ruby
# @author : Shintaro Inagaki
# @param location (Hash) [lat (Float), lng (Float), scale(Int)]
# @return geopo (String)
#
def geopoEncode(lat, lng, scale)
	# 64characters (number + big and small letter + hyphen + underscore)
	chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_"
	
	geopo = ""
	
	# Change a degree measure to a decimal number
	lat = (lat + 90.0) / 180 * 8 ** 10; # 90.0 is forced FLOAT type when lat is INT
	lng = (lng + 180.0) / 360 * 8 ** 10; # 180.0 is same

	# Compute a GeoPo code from head and concatenate
	for i in 0..scale
		geopo += chars[(lat / 8 ** (9 - i) % 8).floor + (lng / 8 ** (9 - i) % 8).floor * 8, 1];
	end
	return geopo;
end

require 'rubygems'
require 'postgres'
require 'json'
require 'yaml'
require 'pp'
# make - { name: Civic Interchange Platform 1,stop_code: civic_platform_1, lat: -35.2794347, lng: 149.130588}
connbus = PGconn.connect("localhost", 5432, '', '', "bus", "postgres", "snmc")

f = File.open('cbrtable.yml.in.in')
header = f.readlines
f.close

File.open('cbrtable.yml.in', 'w') do |f2|  
	f2.puts header
	f2.puts "stops:\n";
	begin
		time_points = connbus.exec("SELECT * from timing_point ORDER BY name")
	rescue PGError => e
		puts "Error reading from DB #{e}"
		#conn.close() if conn
	end
	time_points.each do |time_point|
		#pp time_point
		# 0 = name
		# 1 = lat*100000
		# 2 = lng*100000
		# 7 = suburb(s)
		#pp time_point[0]
		f2.puts "  - { name: #{time_point[0]},stop_code: #{time_point[0]}, lat: #{Float(time_point[1])/10000000}, lng: #{Float(time_point[2])/10000000}, zone_id: #{geopoEncode(Float(time_point[1])/10000000,Float(time_point[2])/10000000,7)}@#{time_point[7]} }"
	end
	begin
		stops = connbus.exec("SELECT * from stops")
	rescue PGError => e
		puts "Error reading from DB #{e}"
		#conn.close() if conn
	end
	stops.each do |stop|
		#pp stop
		# 0 = geoPo
		# 1 = lat*100000
		# 2 = lng*100000
		# 3 = name
		# 4 = suburb(s)
		#pp time_point[0]
		f2.puts "  - { name: #{stop[3]},stop_code: #{stop[0]}, lat: #{Float(stop[1])/10000000}, lng: #{Float(stop[2])/10000000}, zone_id: #{stop[0]}@#{stop[4]} }"
	end
	f2.puts "routes:\n";
end

