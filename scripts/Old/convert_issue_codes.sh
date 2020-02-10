#!/bin/sh
#
# convert_issue_codes.sh - OMIS issue code conversion script
#
# Author: Ken Zalewski
# Organization: New York State Senate
# Version: 1.1
# Created: 2010-03-30
# Revised: 2010-06-15
# Revised: 2010-07-30 - add option to keep intermediate files
# Revised: 2010-09-19 - always set IS_TAG='N' for 83xxx (merge/purge)
#                     - convert 76xxx to "Taxes" instead of "Tax and Finance"
#                     - skip 83xxx codes with blank descriptions
# Revised: 2010-01-06 - ignore issue descriptions that start with "xxx"
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

keep_tempfiles=0


usage() {
  echo "Usage: $prog [-k] SDxxISS.TXT" >&2
  echo "  -k = keep intermediate processing files, otherwise delete them" >&2
}

if [ $# -lt 1 -o $# -gt 2 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -k) keep_tempfiles=1 ;;
    -*) echo "$prog: $1: Unknown option" >&2; usage; exit 1 ;;
    *) infile="$1" ;;
  esac
  shift
done

if [ ! "$infile" ]; then
  echo "$prog: Issue code export file must be specified." >&2
  usage
  exit 1
elif [ ! -r "$infile" ]; then
  echo "$prog: $infile: File not found." >&2
  usage
  exit 1
fi

# Generate list of all unique issue code descriptions, along with the
# number of times it was used.
echo "Analyzing issue code descriptions..." >&2
tail -n +2 "$infile" | cut -d"$fsep" -f2,4 | sort | uniq -c | sed "s;^[ ]*\([0-9][0-9]*\)[ ]*;\1$fsep;" > $issdesc_file
echo "There are "`cat $issdesc_file | wc -l`" unique descriptions" >&2

export fsep

cat $issdesc_file | awk -F"$fsep" --assign fsep="$fsep" '
BEGIN {
  tag_count = 0;
  OFS = fsep;
}
{
  occurs = $1;
  code = $2;
  desc = $3;

  # The 83xxx series issue codes should not become tags.
  if (substr(code, 1, 2) == "83") {
    is_tag = "N";
  }
  else if (occurs <= 5) {
    #print "discarding (occurs): " desc > "/dev/stderr";
    is_tag = "N";
  }
  else if (occurs > 5 && occurs <= 20 && length(desc) > 10) {
    #print "discarding (occurs/length): " desc > "/dev/stderr";
    is_tag = "N";
  }
  else if (length(desc) > 40) {
    #print "discarding (length): " desc > "/dev/stderr";
    is_tag = "N";
  }
  else if (desc ~ /^xxx/) {
    #print "discarding (garbage): " desc > "/dev/stderr";
    is_tag = "N";
  }
  else {
    is_tag = "Y";
    tag_count++;
  }
  print code, desc, is_tag;
}
END {
  print "There are " tag_count " issue codes that will become tags" > "/dev/stderr";
}' | sort -t"$fsep" -k1,1 > $isscode_tags

echo "Mapping OMIS issue codes to taxonomy elements...." >&2

tail -n +2 "$infile" | sed -e 's;|$;;' | awk --assign fsep="$fsep" --file "$common" -F"$fsep" --source '
BEGIN {
  OFS = fsep;
  skipped = 0;
}
{
  id = $1;
  code = $2;
  moddate = $3;
  desc = $4;
  
  if (desc == "" && code ~ /^83/) {
    skipped++;
  }
  else {
    category = convert_isscode_to_category(code);
    moddate = convert_mmddyy_to_yyyymmdd(moddate);
    print id, code, moddate, desc, category;
  }
}
END {
  print "There are " skipped " issue codes that were skipped." > "/dev/stderr";
}' > $isscode_table

echo "Sorting issue code table on issue codes..." >&2
sort -t"$fsep" -k2,2 $isscode_table > $isscode_table_sorted

echo "Joining sorted issue code table to tag table..." >&2
echo "KEY${fsep}ISSUECODE${fsep}UPDATED${fsep}ISSUEDESCRIPTION${fsep}CATEGORY${fsep}IS_TAG"
join -t"$fsep" -1 2 -2 1 -o "1.1 1.2 1.3 1.4 1.5 2.3" $isscode_table_sorted $isscode_tags | sort -t"$fsep" -k1
rc=$?

if [ $keep_tempfiles -ne 1 ]; then
  rm -f $issdesc_file $isscode_tags $isscode_table $isscode_table_sorted
fi

exit $?
