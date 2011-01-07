#!/bin/sh
#
# csv2xsv.sh - Convert a CSV file to either PSV, SSV, or TSV.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-05-06
#
prog=`basename $0`
script_dir=`dirname $0`
parser="$script_dir/parse_xsv.awk"

if [ $# -ne 1 ]; then
  echo "Usage: $prog csv_file" >&2
  exit 1
fi

cfile="$1"

if [ "$prog" = "csv2ssv.sh" ]; then
  newline="|"
  delim="~"
elif [ "$prog" = "csv2tsv.sh" ]; then
  newline="|"
  delim="\t"
else
  newline="\n"
  delim="|"
fi

cat "$cfile" | $script_dir/convert_nonprintables.sh | \
awk --assign newline="$newline" --assign delim="$delim" --file "$parser" --source '
BEGIN {
}
{
  field_num = parse_csv_nl($0, csv, newline);
  if (field_num < 0) {
    print "An error was encountered at record number " FNR >"/dev/stderr";
    exit 1;
  }

  for (i = 1; i <= field_num; i++) {
    if (i > 1) {
      printf(delim);
    }
    printf("%s", csv[i]);
  }
  printf("\n");
}
END {
}'
