require 'rubygems'
require 'pp'
require 'yaml'
class Array
 def to_yaml_style
  :inline
 end
end
Dir.chdir("routes")

def getTimePoints()
	$time_points = []
	$time_points_sources = Hash.new([])
	Dir.glob("*.yml") { |file|
		timetable = YAML::load_file(file)
		$time_points = $time_points | timetable["time_points"]
		timetable["time_points"].each do |timepoint| 
			$time_points_sources[timepoint] = $time_points_sources[timepoint] | [ file ]
		end
	}
end
def correctTimePoints()
time_point_corrections = {"North Lynehamham" => "North Lyneham",
 "Woden Bus Station Platform 10)" => "Woden Bus Station (Platform 10)",
 "Westfield Bus Station Platfrom 1" => "Westfield Bus Station (Platform 1)",
 "Saint AndrewsVillage Hughes"=>"Saint Andrews Village Hughes",
 "Flemmington Road / Sandford St"=>"Flemington Road / Sandford St",
 "City Interchange"=>"City Bus Station",
 "City Interchange (Platform 9)"=>"City Bus Station (Platform 9)",
 "City Bus Station Platfrom 9"=>"City Bus Station (Platform 9)",
 "Belconnen Community Bus StationPlatform 2)"=>"Belconnen Community Bus Station (Platform 2)",
 "Bridbabella Gardens Nursing Home"=>"Brindabella Gardens Nursing Home",
 "Bridbabella GardensNursing Home"=> "Brindabella Gardens Nursing Home",
 "BrindabellaBusiness Park"=> "Brindabella Business Park",
 "NarrabundahTerminus"=>"Narrabundah Terminus",
 "Narrabundah"=>"Narrabundah Terminus",
 "Railway StationKingston"=>"Railway Station Kingston",
 "Saint AndrewsVillage Hughes"=>"Saint Andrews Village Hughes",
 "Cohen St Bus Station (Platform 3)" => "Cohen Street Bus Station (Platform 3)",
 "Cohen St Bus Station (Platform 6)" => "Cohen Street Bus Station (Platform 6)",
 "Newcastle Streetafter Isa Street" => "Newcastle Street after Isa Street",
 "Newcastle St after Isa St" => "Newcastle Street after Isa Street",
 "Newcastle Street after Isa St" => "Newcastle Street after Isa Street",
 "Northbourne Ave / Antill St" => "Northbourne Avenue / Antill St",
 "Macarthur / Northbourne" => "Macarthur / Northbourne Ave",
 "Macarthur Ave / Northbourne" => "Macarthur / Northbourne Ave",
 "Kings Ave / National Cct"=> "Kings Ave / National Circuit",
 "Kosciuszco Ave / Everard Street"=>"Kosciuszko / Everard",
 "Hospice Menindee Dr" => "Hospice / Menindee Dr",
 "Gungahlin Market Place"=> "Gungahlin Marketplace",
 "Gwyder Square Kaleen"=> "Gwydir Square Kaleen",
 "Flemington Road / Nullabor Ave"=>"Flemington Rd / Nullabor Ave",
 "Flemington Road / Sandford St"=>"Flemington Rd / Sandford St",
 "Heagney Cres Clift Cres Richardson"=>  "Heagney / Clift Richardson",
 "Charnwood (Tillyard Drive)"=> "Charnwood",
 "Charnwood Tillyard Dr"=> "Charnwood",
 "charnwood"=> "Charnwood",
 "Black Moutain- Telstra Tower"=>"Black Mountain Telstra Tower",
 "Bonython Primary"=> "Bonython Primary School",
 "Athllon Drive / Sulwood Dr Kambah"=>"Athllon / Sulwood Kambah",
 "Alexander Machonochie Centre Hume"=>"Alexander Maconochie Centre",
 "Alexander Maconochie Centre Hume"=>"Alexander Maconochie Centre",
 "Anthony Rolfe Ave / Moonight Ave" =>"Anthony Rolfe Av / Moonlight Av",
 "Australian National Botanic Gardens"=>"Botanic Gardens",
 "Calwell shops"=> "Calwell", 
 "Chuculba / William Slim Drive"=>"Chuculba / William Slim Dr",
 "Kaleen Village / Maibrynong"=>"Kaleen Village / Maribrynong",
 "Kaleen Village / Marybrynong Ave"=>"Kaleen Village / Maribrynong",
 "National Aquarium"=>"National Zoo and Aquarium",
 "chisholm"=>"Chisholm",
 "O'connor"=>"O'Connor",
 "Mckellar"=>"McKellar",
 "William Web / Ginninderra Drive"=>"William Webb / Ginninderra Drive",
 "Procor / Mead"=>"Proctor / Mead",
 "Fyshwick direct Factory Outlet"=>"Fyshwick Direct Factory Outlet",
 "Fyshwick DirectFactory Outlet"=>"Fyshwick Direct Factory Outlet",
 "Lithgow St Terminus Fyshwick"=>"Lithgow St Terminus",
 "Yarrulumla"=>"Yarralumla",
 "Paul Coe / Mirrebei Dr"=>"Paul Coe / Mirrabei Dr",
 "Mirrebei Drive / Dam Wall"=>"Mirrabei Drive / Dam Wall",
 "Tharwa / Knoke" => "Tharwa Drive / Pockett Ave",
 "Tharwa Drive / Knoke Ave" => "Tharwa Drive / Pockett Ave",
 "Tharwa / Pocket" => "Tharwa Drive / Pockett Ave",
 'Tharwa Dr / Pockett Ave' => "Tharwa Drive / Pockett Ave",
 "Tharwa Dr / Pocket Ave"=>"Tharwa Dr / Pockett Ave",
 "Outrim / Duggan" => "Outtrim / Duggan",
 "ANU Burton and Garran Hall Daley Rd" => "Burton and Garran Hall Daley Road",
 "Farrer Primary"=>"Farrer Primary School",
 "St Thomas More Campbell"=>"St Thomas More's Campbell",
 "Lyneham"=>"Lyneham / Wattle St",
 "Lyneham Wattle Street"=>"Lyneham / Wattle St",
 "Dickson" => "Dickson / Cowper St",
 'Dickson Antill Street' => 'Dickson / Antill St',
 "DicksonAntill Street"=> 'Dickson / Antill St',
 "Livingston / Kambah" => "Kambah / Livingston St",
 'Melba shops' => 'Melba',
 'St Clare of Assisi' => 'St Clare of Assisi Primary',
 'War Memorial Limestone Ave' => 'War Memorial / Limestone Ave',
 'Flynn' => 'Kingsford Smith / Companion'
 
}
	time_point_corrections.each do |wrong, right|
		$time_points_sources[wrong].each do |wrongfile|
			badtimetable = YAML::load_file(wrongfile)
			badentrynumber = badtimetable["time_points"].index wrong
			badtimetable["time_points"][badentrynumber] = right
			puts "Corrected '" + wrong + "' to '" + right + "' in " + wrongfile
			File.open(wrongfile, "w") do |f|
	 			f.write badtimetable.to_yaml
			end
		end
	end
end

getTimePoints()
#pp $time_points.sort!
#pp $time_points_sources.sort


correctTimePoints()
getTimePoints()
correctTimePoints()
getTimePoints()
pp $time_points.sort!
