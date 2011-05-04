<?php
include ('../spyc/spyc.php');
function distance($lat1, $lng1, $lat2, $lng2, $roundLargeValues = false)
{
	$pi80 = M_PI / 180;
	$lat1*= $pi80;
	$lng1*= $pi80;
	$lat2*= $pi80;
	$lng2*= $pi80;
	$r = 6372.797; // mean radius of Earth in km
	$dlat = $lat2 - $lat1;
	$dlng = $lng2 - $lng1;
	$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
	$c = 2 * atan2(sqrt($a) , sqrt(1 - $a));
	$km = $r * $c;
	if ($roundLargeValues) {
		if ($km < 1) return floor($km * 1000);
		else return round($km, 2) . "k";
	}
	else return floor($km * 1000);
}
function getPage($url)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$page = curl_exec($ch);
	curl_close($ch);
	return $page;
}
// http://developers.cloudmade.com/wiki/geocoding-http-api/Documentation
$conn = pg_connect("dbname=bus user=postgres password=snmc");
if (!$conn) {
	echo "An error occured.\n";
	exit;
}
$path = "output/";
$dhandle = opendir($path);
if ($dhandle) {
	// loop through all of the files
	while (false !== ($fname = readdir($dhandle))) {
		if (($fname != '.') && ($fname != '..')) {
			$timetable = Spyc::YAMLLoad($path . $fname);
			$routePoints = Array();
			$routeShapePoints = Array();
			$timetable["stop_distance"] = Array();
			$distanceSum = 0.0;
			$sequenceSum = 0;
			$shape_id = $timetable["short_name"] . $timetable["long_name"] . "shape";
                        echo "Processing shape $shape_id ... <br> \n";
			foreach ($timetable["time_points"] as $timePoint) {
				$curTimePoint = preg_replace("/\(Platform.*/", "", $timePoint);
				foreach ($timetable["between_stops"] as $key => $betweenStops) {
					$keyParts = explode("-", $key);
					$startPoint = preg_replace("/\(Platform.*/", "", $keyParts[0]);
					if ($curTimePoint == $startPoint) {
						if (sizeof($routePoints) == 0 || $routePoints[sizeof($routePoints) - 1] != $startPoint) {
							$routePoints[] = $startPoint;
						}
						foreach ($betweenStops as $betweenStop) {
							$routePoints[] = $betweenStop;
						}
						$routePoints[] = preg_replace("/\(Platform.*/", "", $keyParts[1]);
						$curTimePoint = "";
						break;
					}
				}
			}
			// print_r($routePoints);
			foreach ($routePoints as $key => $startPoint) {
				if ($key + 1 < sizeof($routePoints)) {
					$endPoint = $routePoints[$key + 1];
					$timetable["stop_distance"][$startPoint] = $distanceSum;
					//set start stop distance to total distance
					$query = "SELECT * from between_shapes where between_shapes.from = '$startPoint' AND between_shapes.to = '$endPoint' ORDER BY sequence";
					$result = pg_query($conn, $query);
					$betweenShape = Array();
					$writeToCache = false;
					if ($result && pg_num_rows($result) > 0) {
						$betweenShape = pg_fetch_all($result);
						$writeToCache = false;
					}
					else { //else is not cached,
						//get lat/lng
						$writeToCache = true;
						$query = "SELECT lat, lng from stops where stops.geohash = '" . trim($startPoint) . "'
                                                    UNION ALL SELECT lat, lng from timing_point where timing_point.name = '" . trim($startPoint) . "'";
						$startLatLng = pg_fetch_assoc(pg_query($conn, $query));
						$query = "SELECT lat, lng from stops where stops.geohash = '" . trim($endPoint) . "'
                                                    UNION ALL SELECT lat, lng from timing_point where timing_point.name = '" . trim($endPoint) . "'";
						$endLatLng = pg_fetch_assoc(pg_query($conn, $query));
						//check distance
						$maxDist = 50;
						$maxLngDelta = 0.0001;
						$maxLatDelta = 0.0001;
						$startLatLng['lat'] = $startLatLng['lat'] / 10000000;
						$startLatLng['lng'] = $startLatLng['lng'] / 10000000;
						$endLatLng['lat'] = $endLatLng['lat'] / 10000000;
						$endLatLng['lng'] = $endLatLng['lng'] / 10000000;
						$distanceStartEnd = distance($startLatLng['lat'], $startLatLng['lng'], $endLatLng['lat'], $endLatLng['lng']);
						$deltaLng = abs($startLatLng['lng'] - $endLatLng['lng']);
						$deltaLat = abs($startLatLng['lat'] - $endLatLng['lat']);
						if ($distanceStartEnd > $maxDist || $deltaLat > $maxLatDelta || $deltaLng > $maxLngDelta) {
							//look up cloudmade geocoder
							$url = "http://routes.cloudmade.com/daa03470bb8740298d4b10e3f03d63e6/api/0.3/{$startLatLng['lat']},{$startLatLng['lng']},{$endLatLng['lat']},{$endLatLng['lng']}/car/shortest.js?&units=km";
							$contents = json_decode(getPage($url));
							//var_dump($contents);
							$sumBetweenDistance = 0.0;
							$nextInstruction = 0;
							foreach ($contents->route_geometry as $pointNum => $point) {
                                                            $d = 0;
								if ($nextInstruction < sizeof($contents->route_instructions) && $pointNum == $contents->route_instructions[$nextInstruction][2]) {
										$d = $contents->route_instructions[$nextInstruction][1];
										$nextInstruction++;
									
								}
								$betweenShape[] = Array(
									"from" => $startPoint,
									"to" => $endPoint,
									"lat" => $point[0],
									"lng" => $point[1],
									"distance" => $d,
									"sequence" => $pointNum
								);
							}
						}
						else {
							//fake blank value (straight line A to B)
							$betweenShape[] = Array(
								"from" => $startPoint,
								"to" => $endPoint,
								"lat" => $startLatLng['lat'],
								"lng" => $startLatLng['lng'],
								"distance" => 0.0,
								"sequence" => 0
							);
							$betweenShape[] = Array(
								"from" => $startPoint,
								"to" => $endPoint,
								"lat" => $endLatLng['lat'],
								"lng" => $endLatLng['lng'],
								"distance" => $distanceStartEnd,
								"sequence" => 1
							);
						}
					}
					foreach ($betweenShape as $shapePoint) {
						//write to cache if nessicary
						if ($writeToCache) {
							$query = "INSERT INTO between_shapes VALUES
                                                        ('{$shapePoint['from']}','{$shapePoint['to']}',{$shapePoint['lat']},{$shapePoint['lng']},{$shapePoint['distance']},{$shapePoint['sequence']})";
							$result = pg_query($conn, $query);
						}
						$shapePoint["distance"] = ($distanceSum+= $shapePoint["distance"]);
						$shapePoint["sequence"] = $sequenceSum++;
						//add distance to total distance travelled
						$routeShapePoints[] = $shapePoint;
					}

					$timetable["stop_distance"][$endPoint] = $distanceSum;
					//set end stop distance to total distance
		    		
				}
			}
                        //var_dump($routeShapePoints);
			$timetable["shape"] = $routeShapePoints;
			//print_r($timetable);
			$timetableYAML = Spyc::YAMLDump($timetable);
			file_put_contents($path . $fname,$timetableYAML);
			echo "Saved $path $fname <br>\n";
			die();
			//add routeshape to end of shapes.txt
			// shape_id is shortname+longname+"shape"*/
			
		}
	}
}
?>
