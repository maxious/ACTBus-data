require 'rubygems'
require 'nokogiri'
require 'open-uri'
require 'pp'
require 'yaml'
class Array
  def to_yaml_style
    :inline
  end
end

def makeTimetable(table, period, short_name, pdf_url)
	timetable = {"between_stops" => [], "short_name" => short_name, "route_url" => pdf_url}
	time_points = table.xpath('tr[1]//th').map do |tp|
		if tp.content != "\302\240" && tp.content != "" && tp.content != "<br/>"
			timing_point = tp.content.squeeze(" ").gsub("Shops"," ").gsub("Bus Station"," Bus Station ").gsub("Interchange"," Bus Station ").gsub(" Platform"," (Platform").gsub("StationPlatform","Station (Platform")
			timing_point = timing_point.gsub("Machonochie","Maconochie").gsub("Hume"," ").gsub("Market Place","Marketplace").gsub("Terminus Fyshwick","Terminus")
			timing_point = timing_point.gsub("  - "," - ").gsub("\n"," ").gsub("\r"," ").gsub("\t"," ").gsub("\\"," / ").gsub("/"," / ").gsub(","," ").gsub("*","").gsub("\302\240","").squeeze(" ").strip
			if (short_name == "923" or short_name == "924" or short_name == "938") and timing_point == "Pearce"
			  timing_point = "Canberra Hospital"
			end
			if (tp.content.match('Platform'))
			  timing_point.concat(")")
			end
			if tp.to_s.match(/[0-9][0-9][0-9]/) or tp.to_s.include? "Wheelchair" or timing_point == ""
			  timing_point = nil
			end
			timing_point
		end
	end
	time_points.delete(nil)
	timetable["time_points"] = time_points.to_a
	timetable["long_name"] = "To " + time_points.last
	periodtimes = []
	table.css('tr').each do |row|
	
		times = row.css('td').map do |cell|
			time = cell.content.squeeze(" ").strip
			time = time.gsub(/ *A\S?M/,"a").gsub(/ ?P\S?M/,"p").gsub(/ ?P\S?m/,"p").gsub(/ *a\S?m/,"a").gsub(/ ?p\S?m/,"p")
			time = time.gsub("12:08 AM","1208x").gsub(":","").gsub("1.","1").gsub("2.","2").gsub("3.","3").gsub("4.","4")
			time = time.gsub("5.","5").gsub("6.","6").gsub("7.","7").gsub("8.","8").gsub("9.","9").gsub("10.","10")
			time = time.gsub("11.","11").gsub("12.","12").gsub(/\.+/,"-").gsub("\302\240","")
			if (time == "" 	or time.include? "chool" or time.include? "On Race Days" or time.include? "Bus" or time.include? "l") 
				time = nil # This hacky way is faster than using position()>1 xpath on <TD>s!
			end 
			time
		end
		times.delete(nil)
		if not times.empty?
			triproute = times.shift; # strip first column as route number
			if (triproute == short_name or triproute.start_with? short_name or triproute.end_with? short_name or short_name.start_with? triproute or short_name.end_with? " "+triproute or triproute.end_with? "s" or triproute.end_with? "a" or triproute.end_with? "x" or triproute.end_with? "y" or triproute.end_with? "r")
			  periodtimes << times.to_a
			else
			  if not (triproute == "Notes" or triproute == "s" or triproute == "S" or triproute == "a" or triproute == "x" or triproute == "y" or triproute == "r" or short_name == "300")
			    print short_name + " != " + triproute +"\n"
			   raise("ERROR: shifting route numbers should only appear on 300 timetable")
			  end
			end
			
		end
	end
	if periodtimes.size < 1
		raise "No times for route " + short_name + " in period " + period
	end
	timetable[period] = periodtimes.to_a
	
	timetable["route_text_color"] = "000000"
	timetable["route_color"] = case timetable["short_name"]
	when /1? 31?/
	  timetable["route_text_color"] = "FFFFFF"
	  "00009C" #blue rapid
	when "900"
	  timetable["route_text_color"] = "FFFFFF"
	  "00009C" #blue rapid
	when "300"
	  timetable["route_text_color"] = "FFFFFF"
	  "00009C" #blue rapid
	when "200"
	  "FF2400" #red rapid
	when "2" 
	  "D9D919" #gold line
	when "3"
	  "D9D919" #gold line
	when "4" 
	  "32CD32" #green line
	when  "5"
	  "32CD32" #green line
	when /7??/
	  "845730" #Xpresso line
	else
	  "FFFFFF" #default white
	end
	# pp timetable
	filename = timetable["short_name"] + "-" + timetable["long_name"]+ "." + period + ".yml"
	filename = filename.downcase.gsub(" ","-").gsub("/","-").gsub("(","").gsub(")","")
	puts "Saving " + filename
	File.open("#{File.dirname(__FILE__)}/routes/"+filename, "w") do |f|
		f.write timetable.to_yaml
	end
	timetable
end

Dir.glob("source-html/*oute_*.htm*") { |file|
	puts "Opened " + file
	doc = Nokogiri::HTML(open(file))
	# Search for nodes by css
	timetables = []
	short_name = "";
	doc.xpath('//title').each do |title|
		short_name = title.content.gsub("Route_","").gsub("Route ","").gsub("Series","").gsub("route ","").gsub(", ","/").gsub("ACTION Buses Timetable for ","").squeeze(" ").strip
	end
	if short_name == ""
		raise "Route number(s) not found in <title> tag"
	end

	pdf_url = "";
	doc.xpath('//a[text()="View timetable and map"]').each do |pdf|
		pdf_url = "http://www.action.act.gov.au/Routes_101001/" + pdf['href']
		pdf_url = pdf_url.gsub("\n","").gsub("\r","").gsub("\t","")
	end
	if pdf_url == ""
		raise "PDF Timetable/map not found"
	end

	doc.xpath('//table[preceding::text()="Weekdays"]').each do |table|
		timetables << makeTimetable(table, "stop_times",short_name, pdf_url)
	end
	doc.xpath('//table[preceding::text()="This timetable is effective from Monday 15th November 2010."]').each do |table|
		if short_name[0].chr != "9" or short_name.size == 1
		  timetables << makeTimetable(table, "stop_times",short_name, pdf_url)
		end
	end
	#all tables are weekdays on some really malformatted timetables
	if short_name == "170"
		doc.xpath('//table').each do |table|
			timetables << makeTimetable(table, "stop_times",short_name, pdf_url)
		end
	end
	#weekends
	doc.xpath('//table[preceding::text()="Saturdays" and following::a]').each do |table|
		timetables << makeTimetable(table, "stop_times_saturday",short_name, pdf_url)
	end
	doc.xpath('//table[preceding::text()="Sundays"]').each do |table|
		timetables << makeTimetable(table, "stop_times_sunday", short_name, pdf_url)
	end
	#930/934 special cases
	doc.xpath('//table[preceding::text()="Saturday" and following::h2]').each do |table|
		timetables << makeTimetable(table, "stop_times_saturday",short_name, pdf_url)
	end
	doc.xpath('//table[preceding::text()="Sunday"]').each do |table|
		timetables << makeTimetable(table, "stop_times_sunday", short_name, pdf_url)
	end
	#route 81 = Weekdays - School Holidays Only 
	doc.xpath('//table[preceding::text()="Weekdays - School Holidays Only "]').each do |table|
		timetable = makeTimetable(table, "stop_times",short_name, pdf_url)
		#TODO set active date range to only be holidays
		timetables << timetable;
	end

	
	if timetables.size > 2
		puts "WARNING: " + file + " more than 2 timetables (weekend split?):" + timetables.size.to_s
	end
	if timetables.size < 2
		puts "WARNING: " + file + " less than 2 timetables (weekday loop service?):" + timetables.size.to_s 
	elsif not (timetables[0]["time_points"] - timetables[1]["time_points"].reverse).empty?
		puts "WARNING: first pair of timetable timing points are not complementary for "+ file 
		pp(timetables[0]["time_points"] - timetables[1]["time_points"].reverse)
	end
	if timetables.size < 1
		raise "No timetables extracted from " + file
	end
}
