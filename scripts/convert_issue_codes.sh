#!/bin/sh
#
# convert_issue_codes.sh - OMIS issue code conversion script
#
# Author: Ken Zalewski
# Organization: New York State Senate
# Version: 1.1
# Created: 2010-03-30
# Revised: 2010-06-15
#
# Note: This script assumes that the first line of each OMIS export file
#       contains the column headers.  It trims the first line automatically.
#

prog=`basename "$0"`
script_dir=`dirname "$0"`
common="$script_dir/common_funcs.awk"
fsep="~"
issdesc_file=issue_desc_analysis.txt
isscode_tags=issue_code_tags.txt
isscode_table=issue_code_tab.tsv
isscode_table_sorted=issue_code_tab.tsv.sorted


usage() {
  echo "Usage: $prog SDxxISS.TXT" >&2
}

if [ $# -ne 1 ]; then
  usage
  exit 1
fi

infile="$1"

if [ ! -r "$infile" ]; then
  echo "$prog: $infile: File not found" >&2
  usage
  exit 1
fi

# Generate list of all unique issue code descriptions, along with the
# number of times it was used.
echo "Analyzing issue code descriptions..." >&2
tail -n +2 "$infile" | cut -d"$fsep" -f4 | sort | uniq -c > $issdesc_file
echo "There are "`cat $issdesc_file | wc -l`" unique descriptions" >&2

export fsep

cat $issdesc_file | awk --assign fsep="$fsep" '
BEGIN {
  tag_count = 0;
  OFS = fsep;
}
{
  occurs = $1;
  text = substr($0, index($0, $2));

  if (occurs <= 5) {
    #print "discarding (occurs): " text > "/dev/stderr";
    print text, "N" ;
  }
  else if (occurs > 5 && occurs <= 20 && length(text) > 10) {
    #print "discarding (occurs/length): " text > "/dev/stderr";
    print text, "N" ;
  }
  else if (length(text) > 40) {
    #print "discarding (length): " text > "/dev/stderr";
    print text, "N" ;
  }
  else if (text ~ /^xxx+$/) {
    #print "discarding (garbage): " text > "/dev/stderr";
    print text, "N" ;
  }
  else {
    print text, "Y" ;
    tag_count++;
  }
}
END {
  print "There are " tag_count " issue codes that will become tags" > "/dev/stderr";
}' | sort -t"$fsep" -k1,1 > $isscode_tags

echo "Mapping OMIS issue codes to taxonomy elements...." >&2

tail -n +2 "$infile" | sed -e 's;|$;;' | awk --assign fsep="$fsep" --file "$common" -F"$fsep" --source '
BEGIN {
  OFS = fsep;
}
{
  id = $1;
  code = $2;
  moddate = $3;
  desc = $4;

  category = convert_isscode_to_category(code);
  moddate = convert_mmddyy_to_yyyymmdd(moddate);
  print id, code, moddate, desc, category;
}
END {
}' > $isscode_table

echo "Sorting issue code table on descriptions..." >&2
sort -t"$fsep" -k4,4 $isscode_table > $isscode_table_sorted

echo "Joining sorted issue code table to tag table..." >&2
echo "KEY${fsep}ISSUECODE${fsep}UPDATED${fsep}ISSUEDESCRIPTION${fsep}CATEGORY${fsep}IS_TAG"
join -t"$fsep" -1 4 -2 1 -o "1.1 1.2 1.3 1.4 1.5 2.2" $isscode_table_sorted $isscode_tags | sort -t"$fsep" -k1

exit $?

