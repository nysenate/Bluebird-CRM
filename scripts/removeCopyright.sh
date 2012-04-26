#!/bin/sh
#
# removeCopyright.sh - Remove any Copyright statement from e-mail footers
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2012-04-24
# Revised: 2012-04-26
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--ok] [--dry-run] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

force_ok=0
dry_run=0

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    --dry-run|-n) dry_run=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

html_regexp='<tr><td colspan="[0-9]"><div mc:edit="std_footer"><em>Copyright &copy;[0-9]{4} New York State Senate, All rights reserved.</em></div></td></tr>[\r\n]*'
text_regexp='[\r\n]*Copyright [0-9]{4} New York State Senate, All rights reserved.[\r\n]*'
sqlend="from civicrm_mailing_component where component_type='Footer' and ( body_html like '%copyright%' or body_text like '%copyright%' )"

sql="select count(*) $sqlend;"
cnt=`$execSql -q -i $instance -c "$sql"`
echo "Footer records with 'copyright': $cnt"

if [ $cnt -gt 0 ]; then
  if [ $dry_run -eq 0 ]; then
    if [ $force_ok -eq 0 ]; then
      echo -n "Are you sure that you wish to proceed ([N]/y)? "
      read ch
      case "$ch" in
        [yY]*) ;;
        *) echo "Aborted."; exit 0 ;;
      esac
    fi

    echo "Removing Copyright notices from all footers..."

    sql="
update civicrm_mailing_component
set body_html=preg_replace('|$html_regexp|', '', body_html),
    body_text=preg_replace('|$text_regexp|', '', body_text)
where component_type='Footer';
"
  else
    echo "Before fix-up:"
    sql="select body_html, body_text $sqlend;"
    $execSql -q -i $instance -c "$sql"
    sql="
select preg_replace('|$html_regexp|', '', body_html),
       preg_replace('|$text_regexp|', '', body_text)
from civicrm_mailing_component
where component_type='Footer';
"
    echo "After fix-up:"
  fi   

  $execSql -q -i $instance -c "$sql"
else
  echo "$prog: There are no footer records to update"
fi

exit 0
