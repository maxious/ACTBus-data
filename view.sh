# input location (via GPS or favourites or search) and destination (via searchable list, optional)
# http://10.0.1.153:8765/json/boundboxstops?n=-35.27568499917103&e=149.1346514225006&s=-35.279495003493516&w=149.12622928619385&limit=50
# http://10.0.1.153:8765/json/stoptrips?stop=43&time=64440 # recursively call to show all services nearby, sort by distance, need to filter by service period
# Hey, can pick destination again from a list filtered to places these stops go if you're curious!
# http://10.0.1.153:8765/json/tripstoptimes?trip=2139 # Can recursively call and parse based on intended destination to show ETA
# http://10.0.1.153:8765/json/triprows?trip=2139 # For pretty maps
python ../origin-src/transitfeed-1.2.6/schedule_viewer.py --feed=cbrfeed.zip --key=ABQIAAAA95XYXN0cki3Yj_Sb71CFvBTPaLd08ONybQDjcH_VdYtHHLgZvRTw2INzI_m17_IoOUqH3RNNmlTk1Q
