#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
#
# load_suffixes.py - Scrapes the USPS website for the official abbreviation list
#                    and outputs a set of SQL queries for creating the table
#                    populating it with the USPS official mappings.
#
# Project: BluebirdCRM
# Author: Graylin Kim
# Organization: New York State Senate
# Date: 2011-09-28
# Revised: 2011-09-28
#
#
# Requires BeautifulSoup to run.
#
# BeautifulSoup can be installed with pip or with easy_install:
#   sudo pip install BeautifulSoup
#   sudo easy_install BeautifulSoup
#
# If you don't have either, pip comes with an simple install script:
#
#       curl -O https://raw.github.com/pypa/pip/master/contrib/get-pip.py
#       python get-pip.py
#
# Tada! Pip is installed. How you can install BeautifulSoup and run the script.
#
# python load_suffixes.py
#
# Even better, inject the SQL right into the target SQL instance with bash!
#
#   sh scripts/execSql.sh -i testing -c "`scripts/load_suffixes.py`"


from urllib2 import urlopen
from BeautifulSoup import BeautifulSoup

# The best source for the information that I've been able to find.
html = urlopen('https://www.usps.com/send/official-abbreviations.htm').read()

# The tab with the content we need is nested 2 id's deep.
# The id's on the website aren't unique!!
container = BeautifulSoup(html).find(id=1310379235729).find(id="tab-content-2")


# Each table row has 3 columns as follows:
#   Official Long Form  |  Common Form  |  Official Short Form
#
# Contruct a map of common form to short form for transformation
#
# NOTE: Some columns are for alphabet headings and use &nbsp; to fill the row
pairs = dict()
for row in container.findAll('tr'):
    cells = row.findAll('td')
    if cells[1].text != '&nbsp;':

        #Catch and correct a type on their page
        if cells[1].text == 'CT' and cells[2].text == 'CTS':
            pairs['cts'] = 'cts'

        #The rest of the values should be correct
        else:
            pairs[cells[1].text.lower()] = cells[2].text.lower()

# Don't worry about connecting to the database, just construct the SQL
values = [" ('{0}','{1}')".format(a, b) for a, b in pairs.iteritems()]
print """
-- Our address lookup table
DROP table IF EXISTS address_abbreviations;
CREATE TABLE address_abbreviations (
    raw_value varchar(255) PRIMARY KEY,
    normalized varchar(255)
);

-- Our official USPS abreviation mappings
INSERT INTO address_abbreviations
    (raw_value, normalized)
VALUES """+', '.join(values)+";"

