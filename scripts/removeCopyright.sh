#!/bin/sh
#
# removeCopyright.sh - Remove any Copyright statement from e-mail footers
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2012-04-24
# Revised: 2012-08-03
#

prog=`basename $0`
script_dir=`dirname $0`
execSql=$script_dir/execSql.sh
readConfig=$script_dir/readConfig.sh
force_ok=0
dry_run=0
verbose=0

. $script_dir/defaults.sh

usage() {
  echo "Usage: $prog [--dry-run] [--verbose] [--ok] instanceName" >&2
}

if [ $# -lt 1 ]; then
  usage
  exit 1
fi

while [ $# -gt 0 ]; do
  case "$1" in
    --ok) force_ok=1 ;;
    -n|--dry-run) dry_run=1 ;;
    -v|--verbose) verbose=1 ;;
    -*) echo "$prog: $1: Invalid option" >&2; usage; exit 1 ;;
    *) instance="$1" ;;
  esac
  shift
done

if ! $readConfig --instance $instance --quiet; then
  echo "$prog: $instance: Instance not found in config file" >&2
  exit 1
fi

echo "==> Processing CRM instance [$instance]" >&2

html_regexp='<tr><td colspan="[0-9]"><div mc:edit="std_footer"><em>Copyright &copy;[0-9]{4}(-[0-9]{4})? New York State Senate, All rights reserved.</em></div></td></tr>[\r\n]*'
text_regexp='[\r\n]*Copyright [0-9]{4}(-[0-9]{4})? New York State Senate, All rights reserved.[\r\n]*'
sqlend="from civicrm_mailing_component where component_type='Footer' and ( body_html like '%copyright%' or body_text like '%copyright%' )"

sql="select count(*) $sqlend;"
cnt=`$execSql -q $instance -c "$sql"`
echo "Footer records with 'copyright': $cnt" >&2

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

    echo "Removing Copyright notices from all footers..." >&2

    sql="
update civicrm_mailing_component
set body_html=regexp_replace(body_html, '$html_regexp', ''),
    body_text=regexp_replace(body_text, '$text_regexp', '')
where component_type='Footer';
"
    $execSql -q $instance -c "$sql"
  elif [ $verbose -eq 1 ]; then
    echo "Before fix-up:"
    sql="select body_html, body_text $sqlend;"
    $execSql -q $instance -c "$sql"
    echo "After fix-up:"
    sql="
select regexp_replace(body_html,'$html_regexp', ''),
       regexp_replace(body_text,'$text_regexp', '')
from civicrm_mailing_component
where component_type='Footer';
"
    $execSql -q $instance -c "$sql"
  fi   
else
  echo "$prog: There are no footer records to update" >&2
fi

exit 0
