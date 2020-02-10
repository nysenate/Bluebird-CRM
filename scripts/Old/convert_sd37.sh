#!/bin/sh
#
# convert_sd37.sh - Convert Senator Oppenheimer's non-OMIS data to OMIS format.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-08-26
#

prog=`basename $0`
script_dir=`dirname $0`
district=37
common="$script_dir/common_funcs.awk"
parser="$script_dir/parse_xsv.awk"

if [ $# -ne 2 ]; then
  echo "Usage: $prog contacts_file issues_file" >&2
  exit 1
fi

cfile="$1"
ifile="$2"

mkdir -p "sd$district"

tail -n +2 "$cfile" | \
$script_dir/cleanup_csv.sh | \
  awk --assign district=$district --assign ifile="$ifile" \
      --file "$common" --file "$parser" --source '
BEGIN {
  # Load the SD37 issue codes
  fnum = read_csv_hashtable(ifile, issues);

##  if (fnum > 0) {
##    i=0;
##    for (iss in issues) {
##      print "issues[" iss "]="issues[iss];
##      i++;
##    }
##    print i, fnum;
##  }

  OFS="~";

  print_file_headers(district);

  lastContactId = 0;
}
{
  field_num = parse_csv_nl($0, csv, "|");
  if (field_num < 0) {
    print "An error was encountered at record number " FNR >"/dev/stderr";
    exit 1;
  }

  contactId = csv[1] + 1000000;
  contactDate = csv[2];
  contactTime = "09:30:00";
  recvdBy = csv[3];
  contactForm = tolower(csv[4]);
  prefix = csv[5];
  fnm = csv[6];
  minit = csv[7];
  lnm = csv[8];
  suffix = "";
  hphone = convert_phone_number(csv[9]);
  wphone = convert_phone_number(csv[10]);
  wext = "";
  mphone = csv[11];
  organization = csv[12];
  haddr1 = csv[13];
  haddr2 = "";
  hcity = csv[14];
  hstate = "NY";
  hzip = csv[15];
  tcode1 = 104;
  bmm = "";
  bdd = "";
  byy = "";
  dateModified = "";
  jobTitle = "";
  waddr1 = "";
  waddr2 = "";
  wcity = "";
  wstate = "";
  wzip = "";
  hfax = "";
  wfax = "";
  email = "";
  contactType = "";
  spouseName = "";
  childNames = "";

  lovesLiz = "";  
  groups = "";
  website = "";
  seniorList = "";

  ood = csv[16];
  isscode = csv[17];
  issdesc = csv[18];
  legis = csv[19];
  action = csv[20];
  resource = csv[21];
  resolution = csv[22];
  ssn = "";

  # Rearrange contact date into yymmdd format.
  if (contactDate) {
    n = split(contactDate, a, "/");
    if (n = 3) {
      month = a[1];
      day = a[2];
      year = a[3];
      if (length(year) == 4) {
        year = substr(year, 3);
      }
      contactDate = sprintf("%02d%02d%02d", year, month, day);
      dateModified = sprintf("%02d%02d%02d", month, day, year);
      lastUpdate = sprintf("%02d/%02d/%02d", month, day, year);
    }
  }

  # Get the proper CFORM value from the contactForm
  if (contactForm ~ "letter") {
    cform = "L";
  }
  else if (contactForm ~ "e[-]?mail") {
    cform = "E";
  }
  else if (contactForm ~ "person") {
    cform = "I";
  }
  else if (contactForm ~ "fax") {
    cform = "F";
  }
  else if (contactForm ~ "phone") {
    cform = "P";
  }
  else if (contactForm ~ "website") {
    cform = "W";
  }
  else {
    cform = "O";
  }

  note_count = 0;
  if (action != "") {
    cnotes[note_count++] = "Action: " action;
  }
  if (resource != "") {
    cnotes[note_count++] = "Resource: " resource;
  }
  if (resolution != "") {
    cnotes[note_count++] = "Resolution: " resolution;
  }

  cnote1 = cnote2 = cnote3 = "";
  if (note_count >= 1) {
    cnote1 = cnotes[0];
  }
  if (note_count >= 2) {
    cnote2 = cnotes[1];
  }
  if (note_count >= 3) {
    cnote3 = cnotes[2];
  }

  # If a home phone number contains "@", then it is an e-mail address.
  if (match(hphone, "@")) {
    email = hphone;
    hphone = "";
  }

  # Guess the gender from the title
  gender = "";
  if (match(prefix, "^[ ]*(MRS|Mrs|mrs|MS|Ms|ms|Sister|Sr)[^A-Za-z]*$")) {
    gender = "F";
  }
  else if (match(prefix, "^[ ]*(MR|Mr|mr|Father|FR|Fr)[^A-Za-z]*$")) {
    gender = "M";
  }

  # Split street address into house number and street name.
  hpos = match(haddr1, "^[0-9][0-9A-Da-d-]*([ ]+1/2)?");
  if (hpos > 0) {
    houseno = substr(haddr1, 1, RLENGTH);
    street = substr(haddr1, RLENGTH + 1);
    sub("^[,. ]+", "", street);
  }
  else {
    houseno = "";
    street = haddr1;
  }

  if (ood == "X") {
    ood = "T";
  }
  else {
    ood = "F";
  }

  # Save the contact record in OMIS format

  if (contactId != lastContactId) {
    casenum = 1;
    print contactId, lnm, fnm, minit, suffix, houseno, street, haddr2,
          hcity, hstate, hzip, "", 0, "", 1, "S", "", gender, "", "", "",
          district, "", "", "", tcode1, "", bmm, bdd, byy, hphone, dateModified,
          "", "", "", "", "", "",
          jobTitle, organization, prefix, prefix, "", "", "",
          waddr1, waddr2, wcity, wstate, wzip, wphone, wext,
          mphone, hfax, wfax, email, contactType, spouseName, childNames,
          lovesLiz, groups, website, seniorList, ood >>MSTFILE;
  }
  else {
    casenum++;
  }

  # Save issue info
  if (isscode) {
    desc = issues[isscode];
    is_tag = (length(desc) > 50) ? "N" : "Y";
    category = convert_isscode_to_category(isscode);
    modDate = convert_mmddyy_to_yyyymmdd(dateModified);
    print contactId, isscode, modDate, issues[isscode], category, is_tag >>ISSFILE;
  }

  # Save case info
  print contactId, casenum, issdesc, rcvdBy, contactTime, contactDate,
        contactDate, hphone, wphone, wfax, "", "", "", "", "",
        isscode, cform, "", cnote1, cnote2, cnote3, lastUpdate, legis >>CASFILE;

  lastContactId = contactId;
}
END {
}'

if [ $? -eq 0 ]; then
  mv sd$district sd${district}ext
  zip -r sd${district}ext.zip sd${district}ext
fi

