require 'rubygems'
require 'postgres'
require 'pp'
require 'yaml'
class String
  def escape_single_quotes
    self.gsub(/'/, "''")
  end
end
class Array
  def to_yaml_style
    :inline
  end
end
Dir.chdir("output")

connbus = PGconn.connect("localhost", 5432, '', '', "bus", "postgres", "snmc")

Dir.glob("*.yml") { |file|
  timetable = YAML::load_file(file)
  if timetable
    route_name = timetable["short_name"]
    timetable["between_stops"] = {}
    for i in 0..timetable["time_points"].length-2
	begin
	  searchFrom = timetable["time_points"][i].escape_single_quotes.split("(")[0].strip
	  searchTo = timetable["time_points"][i+1].escape_single_quotes.split("(")[0].strip
	#  print "SELECT * from between_stops
	#	  WHERE fromlocation = '#{searchFrom}'
	#  AND tolocation = '#{searchTo}' AND routes LIKE '%#{route_name};%'"
	  between_points = connbus.exec("SELECT * from between_stops
		  WHERE fromlocation = '#{searchFrom}'
	  AND tolocation = '#{searchTo}' AND (routes LIKE '#{route_name};%' OR  routes LIKE '%;#{route_name};%')")
	rescue PGError => e
		puts "Error selecting matching between points from DB #{e}"
		#conn.close() if conn
	end
	if between_points.ntuples() == 0:
		  print "SELECT * from between_stops
			  WHERE fromlocation = '#{searchFrom}'
		  AND tolocation = '#{searchTo}' AND routes LIKE '%#{route_name};%'"
		raise "no row found"
	end
	between_points.each do |between_point_row|
		points = between_point_row['points'].strip.split(";")
		points.delete("")
		timetable["between_stops"][timetable["time_points"][i] + '-' +timetable["time_points"][i+1]] = points;
	end
    end
    #pp timetable["between_stops"]
    File.open(file, "w") do |f|
      f.write timetable.to_yaml
    end
  
  else
    print "error, #{file} empty\n"
  end
}
