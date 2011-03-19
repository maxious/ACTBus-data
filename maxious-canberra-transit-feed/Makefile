default: cbrfeed.zip

cbrfeed.zip: cbrtable.yml createfeed.py
	sh 06-cleanupyaml.sh
	./createfeed.py --input=cbrtable.yml --output=cbrfeed.zip

cbrtable.yml: cbrtable.yml.in indent-route.pl
	cp cbrtable.yml.in cbrtable.yml
	@$(foreach ROUTE_FILE, $(wildcard output/*),  \
		echo "Parsing $(ROUTE_FILE)"; \
		./indent-route.pl < "$(ROUTE_FILE)" >> cbrtable.yml;)

cbrtable.yml.in: cbrtable.yml.in.in
	ruby 01-extracttimes.rb	
	ruby 02-tidytimepoints.rb 
	ruby 04-generateymlinclude.rb 
	ruby 05-addbetweenpointstotimetables.rb 

clean:
	rm -f cbrtable.yml cbrtable.yml.in cbrfeed.zip *~ output/*
