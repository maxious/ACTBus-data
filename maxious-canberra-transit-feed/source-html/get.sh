wget -nd -np -r -I /routes_by_number.html,*101001* --random-wait -c http://www.action.act.gov.au/Routes_101001/index.htm
wget http://www.action.act.gov.au/interchange_maps.html
grep "Page not found" *

