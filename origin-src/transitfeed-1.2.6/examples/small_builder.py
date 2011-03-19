#!/usr/bin/python2.5

# A really simple example of using transitfeed to build a Google Transit
# Feed Specification file.

import transitfeed
from optparse import OptionParser


parser = OptionParser()
parser.add_option('--output', dest='output',
                  help='Path of output file. Should end in .zip')
parser.set_defaults(output='google_transit.zip')
(options, args) = parser.parse_args()

schedule = transitfeed.Schedule()
schedule.AddAgency("Fly Agency", "http://iflyagency.com",
                   "America/Los_Angeles")

service_period = schedule.GetDefaultServicePeriod()
service_period.SetWeekdayService(True)
service_period.SetDateHasService('20070704')

field_d = {'lng': -122, 'lat': 37.2, 'name':"Suburbia", 'stop_code': "AAAZZ"}
stop1 = transitfeed.Stop(field_dict=field_d)
print stop1.__dict__
print stop1.__getattr__('stop_code')
schedule.AddStopObject(stop1)
stop2 = schedule.AddStop(lng=-122.001, lat=37.201, name="Civic Center")

route = schedule.AddRoute(short_name="22", long_name="Civic Center Express",
                          route_type="Bus")

trip = route.AddTrip(schedule, headsign="To Downtown")
trip.AddStopTime(stop1, stop_time='09:00:00')
trip.AddStopTime(stop2, stop_time='09:15:00')

trip = route.AddTrip(schedule, headsign="To Suburbia")
trip.AddStopTime(stop1, stop_time='17:30:00')
trip.AddStopTime(stop2, stop_time='17:45:00')

for s in schedule.GetStopList():
      #wtf, stop_code changes into stop_name after .find()
      virginstopCode = s.stop_code
      print s
      print s.stop_code
      #if s.stop_code.find("Wj") == -1:
     #   print (stop.stop_id, stop.stop_name, float(stop.stop_lat),
      #    float(stop.stop_lon), stop.location_type, s.stop_code)

#schedule.Validate()
schedule.WriteGoogleTransitFeed(options.output)
