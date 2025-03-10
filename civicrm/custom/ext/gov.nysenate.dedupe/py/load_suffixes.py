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
#   sh scripts/execSql.sh INSTANCE -c "`modules/nyss_dedupe/load_suffixes.py`"

import os

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
sets = list()
for row in container.findAll('tr'):
    cells = row.findAll('td')

    if cells[0].text == 'Primary Street Suffix': continue
    if cells[1].text != '&nbsp;':

        #Catch and correct a type on their page
        if cells[1].text == 'CT' and cells[2].text == 'CTS':
            sets.append(('courts','cts','cts'))

        #The rest of the values should be correct
        else:
            long_form = cells[0].text.lower()
            raw_value = cells[1].text.lower()
            normalized = cells[2].text.lower()
            sets.append((long_form, raw_value, normalized))

# Don't worry about connecting to the database, just construct the SQL
values = ["('{0}','{1}','{2}')".format(a,b,c) for a,b,c in sets]

with open(os.path.join(os.path.dirname(__file__),"output","suffixes.sql"),'w') as output:
    output.write("""
-- Our address lookup table
DROP table IF EXISTS address_abbreviations;
CREATE TABLE address_abbreviations (
  raw_value varchar(255) PRIMARY KEY,
  long_form varchar(255),
  normalized varchar(255),
  INDEX (long_form)
);

-- Our official USPS abreviation mappings
-- They have some duplicate listings, ignore those errors
INSERT IGNORE INTO address_abbreviations
  (long_form, raw_value, normalized)
VALUES
  """+',\n  '.join(values)+";");
