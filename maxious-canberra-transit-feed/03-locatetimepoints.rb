#!/usr/bin/ruby

require 'highline.rb'
include HighLine

require 'rubygems'
require 'postgres'
require 'json'
require 'net/http'
def cbr_geocode(query)
   base_url = "http://geocoding.cloudmade.com/daa03470bb8740298d4b10e3f03d63e6/geocoding/v2/find.js?query="
   url = "#{base_url}#{URI.encode(query)}&bbox=-35.47,148.83,-35.16,149.25&return_location=true"
   resp = Net::HTTP.get_response(URI.parse(url))
   data = resp.body
pp url
   # we convert the returned JSON data to native Ruby
   # data structure - a hash
   result = JSON.parse(data)

   # if the hash has 'Error' as a key, we raise an error
   if result.has_key? 'Error'
      raise "web service error"
   end
   return result
end
class Array

   def find_dups
      inject(Hash.new(0)) { |h,e| h[e] += 1; h }.select { |k,v| v > 1 }.collect { |x| x.first }
   end
end

require 'yaml'
require 'pp'
Dir.chdir("output")

def getTimePoints()
        $time_points = []
        $time_points_sources = Hash.new([])
        Dir.glob("*.yml") { |file|
	       pp file
                timetable = YAML::load_file(file)
                $time_points = $time_points | timetable["time_points"]
                timetable["time_points"].each do |timepoint|
                        $time_points_sources[timepoint] = $time_points_sources[timepoint] | [ file ]
                end
        }
end

getTimePoints()
$time_points.sort!

connbus = PGconn.connect("localhost", 5432, '', '', "bus", "postgres", 
"snmc")

if ask_if("Insert Timing Point names to database?")
	$time_points.each do |time_point|
		begin
			time_point = time_point.gsub(/\\/, '\&\&').gsub(/'/, "''") # DON'T PUT MORE GSUB HERE
			res = connbus.exec("INSERT INTO timing_point (name) VALUES ('#{time_point}')")
			puts "Put '#{time_point}' into DB"
		rescue PGError => e
			puts "Error inserting '#{time_point}' to DB #{e}"
			#conn.close() if conn
		end
	end
end


if ask_if("Fill null Timing Points from geocoder?")
	begin
		null_points = connbus.exec('SELECT name FROM timing_point WHERE lat IS null OR lng IS null;')
	rescue PGError => e
                puts "Error selecting null points from DB #{e}"
                #conn.close() if conn
        end

	null_points.each do |null_point_name|
		pp null_point_name
		name = null_point_name.to_s.gsub(/\\/, '\&\&').gsub(/'/, "''")
		results = cbr_geocode(null_point_name.to_s.gsub("Shops", ""))
		if !results.empty? 
			results['features'].each_with_index { |feature,index|
				print "#{index}: #{feature['properties']['name']} (#{feature['location']}) => #{feature['centroid']['coordinates']}\n"
			}
			nodeID = ask("Enter selected node ID:", :integer) 
			if results['features'].at(nodeID) != nil
				node = results['features'][nodeID]
				puts "Location #{node['centroid']['coordinates'][0]},#{node['centroid']['coordinates'][1]} for #{null_point_name}"
				begin
		        	        res = connbus.exec("UPDATE timing_point SET lat = #{node['centroid']['coordinates'][0]*10000000}, lng = 
	#{node['centroid']['coordinates'][1]*10000000},guess = true WHERE name = '#{name}'")
			               	puts "Put '#{null_point_name}' into DB"
			       	rescue PGError => e
		        	        puts "Error inserting '#{null_point_name}' to DB #{e}"
					ask_if("Continue?")
			               	#conn.close() if conn
			       	end
			else
				puts "Uhh, there was no suggestion ID like that. Try again next time!"
			end
		else
			puts "Uhh, there were no geocoding results. Try again next time!"
		end		
	end
end


