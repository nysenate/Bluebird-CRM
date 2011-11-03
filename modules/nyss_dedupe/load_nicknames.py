#!/usr/bin/env python
# -*- encoding: utf-8 -*-
import os, sys
import functools

from collections import defaultdict

rel_path = functools.partial(os.path.join, os.path.dirname(__file__))

def load_data(filename, dest, census, nick_first=False):
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
                            dest[base].add(nick)
                        else:
                            trash.add(nick)
                else:
                    trash.add(base)

def load_census():
    dest = set()

    with open(rel_path('source','data.dat'),'r') as source:
        for parts in [line.split(',') for line in source]:
            if parts[4] == '1': dest.add(parts[0].strip().lower())

    with open(rel_path('source','NAMES-F.TXT'),'r') as source:
        for parts in [line.split(',') for line in source]:
            dest.add(parts[0].strip().lower())

    with open(rel_path('source','NAMES-M.TXT'),'r') as source:
        for parts in [line.split(',') for line in source]:
            dest.add(parts[0].strip().lower())

    with open(rel_path('source','census_female.txt'),'r') as source:
        for parts in [line.split(',') for line in source]:
            dest.add(parts[0].strip().lower())

    with open(rel_path('source','census_male.txt'),'r') as source:
        for parts in [line.split(',') for line in source]:
            dest.add(parts[0].strip().lower())

    with open(rel_path('source','patch-f.txt'),'r') as source:
        for parts in [line.split(',') for line in source]:
            dest.add(parts[0].strip().lower())

    with open(rel_path('source','patch-m.txt'),'r') as source:
        for parts in [line.split(',') for line in source]:
            dest.add(parts[0].strip().lower())

    with open(rel_path('source','given_name.tsv'),'r') as source:
        for parts in [line.split('\t') for line in source]:
            dest.add(parts[0].strip().lower())

    return dest

def print_stats(source, title):
    names = len(source.keys())
    nicks = sum(len(l) for l in source.values())
    avg = nicks/float(names)

    print
    print "{0}".format(title)
    print "--------------------------"
    print "{0} Names, {1} Nicks, {2} Nicks per name (avg)".format(names, nicks, avg)
    print


if __name__ == '__main__':

    print "Initializing..."
    trash = set()
    all_in_one = defaultdict(set)
    male_nicknames = defaultdict(set)
    female_nicknames = defaultdict(set)
    unknown_nicknames = defaultdict(set)

    print "Loading Census Data..."
    census = load_census()

    print "Loading Miscellaneous internet spam..."
    load_data(rel_path('source','kknfa-female.txt'), female_nicknames, census)
    load_data(rel_path('source','censusdiggin-female.txt'), female_nicknames, census)
    load_data(rel_path('source','kknfa-male.txt'), male_nicknames, census)
    load_data(rel_path('source','censusdiggin-male.txt'), male_nicknames, census)
    load_data(rel_path('source','wikimedia.txt'), unknown_nicknames, census)
    load_data(rel_path('source','names1.1_fixed.txt'), unknown_nicknames, census)
    load_data(rel_path('source','topnames.txt'), unknown_nicknames, census, nick_first=True)
    load_data(rel_path('source','nireland.txt'), unknown_nicknames, census)
    load_data(rel_path('source','senate-mappings.txt'), unknown_nicknames, census)

    def sort_and_filter(source, dest):
        for given in source.keys():
            for nick in source[given]:
                if nick in census:
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
            #make sure its not listing itself as a nickname
            if given in nicknames and given != given2:
                all_in_one[given2] = all_in_one[given2] | all_in_one[given]
                del all_in_one[given]

    print_stats(all_in_one, "Nicknames")

    with open(rel_path('output','names.txt'), 'w') as output:
        for name in sorted(given_names | nicknames):
            output.write(name+'\n')

    #Persist the results to file, simple given-nick,nick format
    with open(rel_path('output','mapping.txt'), 'w') as output:
        for key, value in sorted(all_in_one.iteritems()):
            output.write(key+'-'+','.join(set(sorted(value)))+'\n')

    with open(rel_path('output','trash.txt'), 'w') as output:
        for item in trash:
            output.write(item+'\n')

    with open(rel_path('output','nicknames.sql'), 'w') as output:
        output.write("""\
DROP TABLE IF EXISTS fn_group;
CREATE TABLE fn_group (
    id      int         PRIMARY KEY AUTO_INCREMENT,
    given   varchar(50) UNIQUE KEY,
    new     int(1)      DEFAULT '0'
);

DROP TABLE IF EXISTS fn_group_name;
CREATE TABLE fn_group_name (
    fn_group_id  int,
    name         varchar(50),
    INDEX(fn_group_id),
    INDEX(name)
);

DROP TABLE IF EXISTS  fn_group_contact;
CREATE TABLE fn_group_contact (
    fn_group_id int,
    contact_id  int,
    INDEX(fn_group_id),
    INDEX(contact_id)
);
""")

        for given, nicknames in all_in_one.iteritems():
            output.write("INSERT INTO fn_group (given, new) VALUES ('{0}',0);\n".format(given))
            output.write("SET @last_id:=LAST_INSERT_ID();\n")

            output.write("INSERT INTO fn_group_name (fn_group_id, name) VALUES\n\t")
            output.write(', '.join("(@last_id,'{0}')".format(name) for name in sorted(set([given]) | nicknames))+';')
            output.write('\n\n')
