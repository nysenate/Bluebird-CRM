#!/bin/sh
#
# convert_nonprintables.sh - Convert all non-printable characters to
#                            printable equivalents.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-05-06
#
prog=`basename $0`

# Convert 0x85 to "..."
# Convert 0x92 to "'" (apostrophe)
# Convert 0x93 to "''" (double single-quotes)
# Convert 0x94 to "''" (double single-quotes)
# Convert 0x96 to "-" (dash)

sed -e "s;\x85;...;g" -e "s;\x92;';g" \
    -e "s;\x93;'';g" -e "s;\x94;'';g" -e "s;\x96;-;g"

