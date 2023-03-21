#!/usr/bin/env bash
set -e

EXTROOT=$(cd `dirname $0`/..; pwd)
EXTKEY="uk.co.vedaconsulting.mosaico"
XMLBUILD="$EXTROOT/build/xml/schema"

##############################
function do_help() {
  echo "usage: $0 [options]"
  echo "example: $0"
  echo "  -h     (Help)           Show this help screen"
  echo "  -a     (All)            Implies -Dg (default)"
  echo "  -D     (Download)       Download dependencies"
  echo "  -g     (GenCode)        Generate DAO files, SQL files, etc"
  echo "  -z     (Zip)            Build installable ZIP file"
}

##############################
function use_civiroot() {
  if [ -z "$CIVIROOT" ]; then
    CIVIROOT=$(cv ev 'echo $GLOBALS["civicrm_root"];')
    if [ -z "$CIVIROOT" -o ! -d "$CIVIROOT" ]; then
      do_help
      echo ""
      echo "ERROR: invalid civicrm-dir: [$CIVIROOT]"
      exit
    fi
  fi
}

##############################
## Make a tempdir, $ext/build/xml/schema; compile full XML tree
function buildXmlSchema() {
  use_civiroot

  mkdir -p "$XMLBUILD"

  ## Mix together main xml files
  cp -fr "$CIVIROOT"/xml/schema/* "$XMLBUILD/"
  cp -fr "$EXTROOT"/xml/schema/* "$XMLBUILD/"

  ## Build root xml file
  ## We build on the core Schema.xml so that we don't have to do as much work to
  ## manage inter-table dependencies
  grep -v '</database>' "$CIVIROOT"/xml/schema/Schema.xml > "$XMLBUILD"/Schema.xml
  cat "$XMLBUILD"/Schema.xml.inc >> "$XMLBUILD"/Schema.xml
  echo '</database>' >> "$XMLBUILD"/Schema.xml
}

##############################
## Run GenCode; copy out the DAOs
function buildDAO() {
  use_civiroot
  pushd $CIVIROOT/xml > /dev/null
    php GenCode.php $XMLBUILD/Schema.xml
  popd > /dev/null

  [ ! -d "$EXTROOT/CRM/Mosaico/DAO/" ] && mkdir -p "$EXTROOT/CRM/Mosaico/DAO/"
  cp -f "$CIVIROOT/CRM/Mosaico/DAO"/* "$EXTROOT/CRM/Mosaico/DAO/"
}

##############################
function cleanup() {
  use_civiroot
  for DIR in "$XMLBUILD" "$CIVIROOT/CRM/Mosaico" "$EXTROOT/CRM/Mosaico/DAO/" ; do
    if [ -e "$DIR" ]; then
      rm -rf "$DIR"
    fi
  done
}

##############################
function do_gencode() {
  cleanup
  buildXmlSchema
  buildDAO
  echo
  echo "If there have been XML schema changes, then be sure to manually update the .sql files!"
}

##############################
function do_download() {
  pushd "$EXTROOT" >> /dev/null
    composer install
  popd >> /dev/null
}

##############################
## Build installable ZIP file
function do_zipfile() {
  local canary="$EXTROOT/packages/mosaico/dist/mosaico.min.css"
  if [ ! -f "$canary" ]; then
    echo "Error: File $canary missing. Are you sure the build is ready?"
    exit 1
  fi

  local zipfile="$EXTROOT/build/$EXTKEY.zip"
  [ -f "$zipfile" ] && rm -f "$zipfile"
  [ ! -d "$EXTROOT/build" ] && mkdir "$EXTROOT/build"
  pushd "$EXTROOT" >> /dev/null
    ## Build a list of files to include.
    ## Put the files into the *.zip, using a $EXTKEY as a prefix.
    {
       ## Get any files in the project root, except for dotfiles.
       find . -mindepth 1 -maxdepth 1 -type f -o -type d | grep -v '^\./\.'
       ## Get any files in the main subfolders.
       find CRM/ Civi/ ang/ api/ bin/ css/ js/ managed/ sql/ sass/ settings/ templates/ tests/ vendor/ xml/ -type f -o -type d
       ## Get the distributable files for Mosaico.
       find packages/mosaico/{NOTICE,README,LICENSE,dist,templates}* -type f -o -type d
    } \
      | grep -v '~$' \
      | php bin/add-zip-regex.php "$zipfile" ":^:" "$EXTKEY/"
  popd >> /dev/null
  echo "Created: $zipfile"
}

##############################
## Main
HAS_ACTION=

while getopts "aDghz" opt; do
  case $opt in
    h)
      do_help
      HAS_ACTION=1
      ;;
    a)
      do_download
      do_gencode
      HAS_ACTION=1
      ;;
    D)
      do_download
      HAS_ACTION=1
      ;;
    g)
      do_gencode
      HAS_ACTION=1
      ;;
    z)
      do_zipfile
      HAS_ACTION=1
      ;;
    \?)
      do_help
      echo "Invalid option: -$OPTARG" >&2
      exit 1
      ;;
    :)
      echo "Option -$OPTARG requires an argument." >&2
      exit 1
      ;;
  esac
done

if [ -z "$HAS_ACTION" ]; then
  do_help
  exit 2
fi
