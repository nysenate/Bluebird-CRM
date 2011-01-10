#!/bin/sh
#
# cleanup_csv.sh - Convert non-printable characters to printable equivalents,
#                  and eliminate all pipes, backslashes, and tildes.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-05-06
#
prog=`basename $0`
script_dir=`dirname $0`

# This script converts all non-printable characters to printable equivalents.
# In addition, it eliminates all pipes, backslashes, and tildes.
# This allows the converted output to use the tilde as the field separator
# (same as OMIS output format), and the pipe as the newline character.
#
# Remove all pipes that occur immediately before a comma
# Remove all backslashes
# Convert tilde to dash

$script_dir/convert_nonprintables.sh | sed -e "s;|,;,;" -e 's;\\;;' -e 's;~;-;'

