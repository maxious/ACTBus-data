#!/usr/bin/python2.5

import datetime
import transitfeed
import sys
import re

def clean_name(name):
    nparts = name.split()
    return nparts[0] + " " + nparts[1] + " " + nparts[2] 

def main():
  feed_filename = sys.argv[1]

  schedule = transitfeed.Schedule(problem_reporter=transitfeed.ProblemReporter())
  print 'Loading data from feed "%s"...' % feed_filename
  print '(this may take a few minutes for larger cities)'
  t0 = datetime.datetime.now()
  schedule.Load(feed_filename)
  print ("Loaded in", (datetime.datetime.now() - t0).seconds , "seconds")
  for r in schedule.GetRouteList():
    for t in schedule.GetTripList():
        if t.route_id == r.route_id:
            time_stops = t.GetTimeStops()
            fromstop = clean_name(time_stops[0][2].stop_name)
            tostop = clean_name(time_stops[-1][2].stop_name)

            r.route_long_name = u'Between %s and %s' % (fromstop, tostop)
            #print r.route_long_name
        if r.route_long_name != "":
            break
  schedule.WriteGoogleTransitFeed(feed_filename)
if __name__ == '__main__':
  main()
