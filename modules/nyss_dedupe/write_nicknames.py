import os, sys
import functools

rel_path = functools.partial(os.path.join, os.path.dirname(__file__))

def load_census():

	def add_name(name, gender, dest):
		if gender:
			if name in dest:
				dest[name].add(gender)
			else:
				dest[name] = set(gender)
		else:
			dest[name] = set()

	def load_names(source_file, dest, delim, gender=None, accept=lambda line: True):
		with open(source_file,'r') as source:
			for line in source:
				if accept(line):
					add_name(line.split(delim)[0].strip().lower(), gender, names)

	names = dict()
	load_names(rel_path('source','NAMES-F.TXT'), names, ',', 'F')
	load_names(rel_path('source','NAMES-M.TXT'), names, ',', 'M')
	load_names(rel_path('source','census_male.txt'), names, ' ', 'M')
	load_names(rel_path('source','census_female.txt'), names, ' ', 'F')
	load_names(rel_path('source','data.dat'), names, '\t', accept=lambda line: line.split(',')[4]==1)

	# This particular file is more special than the others
	with open(rel_path('source','given_name.tsv'), 'r') as source:
		for parts in [line.lower().split('\t') for line in source]:
			for gender in parts[2].split(','):
				if gender:
					if gender in ('male','female'):
						add_name(parts[0].strip(), gender[0].upper(), names)

	# Senate patches
	add_name('ander', 'M', names)
	add_name('yvonne', 'F', names)
	add_name('ziggy', 'M', names)
	add_name('julias', 'M', names)
	add_name('topher', 'M', names)
	add_name('zedediah', 'M', names)
	add_name('dickson', 'M', names)
	add_name('alphonzo', 'M', names)
	add_name('magdelina', 'F', names)
	add_name('jebediah', 'M', names)
	add_name('pocahontas', 'F', names)
	add_name('juda', None, names)
	return names

def load_data(filename, dest, census, nick_first=False, gender=None):
    #Different files order them differently
    if nick_first:  NICKPART, BASEPART = 0, 1
    else:           NICKPART, BASEPART = 1, 0

    with open(filename,'r') as source:
        for parts in [line.split('-') for line in source]:

            #Skip bad lines, bad lines don't have a '-' to split on
            if len(parts) < 2: continue

            #Both sides here can be split and need to be looped over
            #Save all the discarded names in the trash for later review
            for base in [b.strip().lower() for b in parts[BASEPART].split(',')]:
                if base in census:
                    for nick in [n.strip().lower() for n in parts[NICKPART].split(',')]:
                        if nick in census:
                            # If the both have genders, make sure the nick matches
                    	    if not census[base] or not census[nick] or (census[nick] & census[base]):
                                dest[base].add(nick)
                            else:
                        	    gender_trash[nick].add(filename)

                        elif nick:
                            trash[nick].add(filename)
                elif base:
                    trash[base].add(filename)


from collections import defaultdict
trash = defaultdict(set)
gender_trash = defaultdict(set)
all_in_one = defaultdict(set)
male_nicknames = defaultdict(set)
female_nicknames = defaultdict(set)
unknown_nicknames = defaultdict(set)

census = load_census()

load_data(rel_path('source','kknfa-female.txt'), female_nicknames, census, gender='F')
load_data(rel_path('source','censusdiggin-female.txt'), female_nicknames, census, gender='F')
load_data(rel_path('source','kknfa-male.txt'), male_nicknames, census, gender='M')
load_data(rel_path('source','censusdiggin-male.txt'), male_nicknames, census, gender='M')
load_data(rel_path('source','wikimedia.txt'), unknown_nicknames, census)
load_data(rel_path('source','names1.1_fixed.txt'), unknown_nicknames, census)
load_data(rel_path('source','topnames.txt'), unknown_nicknames, census, nick_first=True)
load_data(rel_path('source','nireland.txt'), unknown_nicknames, census)
load_data(rel_path('source','senate-mappings.txt'), unknown_nicknames, census)

def print_stats(source, title):
    names = len(source.keys())
    nicks = sum(len(l) for l in source.values())
    avg = nicks/float(names)

    print
    print "{0}".format(title)
    print "--------------------------"
    print "{0} Names, {1} Nicks, {2} Nicks per name (avg)".format(names, nicks, avg)
    print

def sort_and_filter(source, dest):
    for given in source.keys():
        for nick in source[given]:
            dest[given].add(nick)

print "Sorting/Filtering Unknown Nicknames..."
sort_and_filter(unknown_nicknames, all_in_one)
print "Sorting/Filtering Female Nicknames..."
sort_and_filter(female_nicknames, all_in_one)
print "Sorting/Filtering Male Nicknames..."
sort_and_filter(male_nicknames, all_in_one)

#If a nickname is listed as a given name, pull it into the original group
nicknames = set(sum([list(i) for i in all_in_one.values()],[]))
given_names = set(all_in_one.keys())

for given in given_names:
    for given2 in all_in_one.keys():
        nicknames = all_in_one[given2]
        # make sure its not listing itself as a nickname
        # also make sure that genders overlap where applicable
        if given in nicknames and given != given2 and (not census[given] or not census[given2] or (census[given] & census[given2])):
            all_in_one[given2] = all_in_one[given2] | all_in_one[given]
            del all_in_one[given]

print_stats(all_in_one, "Nicknames")

# write the trash to file...
with open(rel_path('output','trash.txt'), 'w') as output:
    for item in sorted(trash.keys()):
    	files = ', '.join(trash[item])
        output.write("{0: <20} {1}\n".format(item, files))

with open(rel_path('output','gender_trash.txt'), 'w') as output:
    for item in sorted(gender_trash.keys()):
    	files = ', '.join(gender_trash[item])
        output.write("{0: <20} {1}\n".format(item, files))

# write the genders to file...
with open(rel_path('output','total_name_gender_map.csv'), 'w') as output:
	for key in sorted(census.keys()):
		gender = ",".join(census[key])
		output.write(key+','+gender+'\n')

# Additionally write the subset initially used in nickname groups
with open(rel_path('output','used_name_gender_map.csv'), 'w') as output:
    for name in sorted(set(sum((list(i) for i in all_in_one.values()), all_in_one.keys()))):
    	gender = ",".join(census[name])
        output.write(name+','+gender+'\n')

#Persist the final results to csv file, simple given,nick,nick format
with open(rel_path('output','nickname_mappings.csv'), 'w') as output:
    for key, value in sorted(all_in_one.iteritems()):
        output.write(key+','+','.join(sorted(set(value)))+'\n')