#!/bin/sh
#
# convert_sd26.sh - Convert Senate Krueger's non-OMIS data to OMIS format.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-20
#

prog=`basename $0`
script_dir=`dirname $0`
district=26
common="$script_dir/common_funcs.awk"
parser="$script_dir/parse_xsv.awk"

if [ $# -ne 1 ]; then
  echo "Usage: $prog csv_file" >&2
  exit 1
fi

cfile="$1"

mkdir -p "sd${district}"

tail -n +2 "$cfile" | \
sed -e "s;www.senate.gov/~kerry;kerry.senate.gov;" | \
$script_dir/cleanup_csv.sh | \
  awk --assign district=$district --file "$common" --file "$parser" --source '
BEGIN {
  OFS="~";

  print_file_headers(district);

  contactTypeList = "Staff,Intern,Volunteer,Constituent,Elected,Contact,Friend,Press,Political,Labor,Resource,Community,VIP,School,Synagogue,Church,Arts,Health,Tenant Association";
  contactTypeCount = split(contactTypeList, contactTypes, ",");

  is_tag = "Y";
  issueTypeList = "Arts,Disability,Education,Environment,Housing/Tenant,Healthcare,LGBT,Social Services,Quality of Life,Seniors,Women,UNUSED,General,Business,Land Use,Jewish,Civil Rights,Labor,Legal,Animals,Youth,Parks,Homeless,Policy,Politics,Gun Violence,Media,Government,Transportation,Coop/Condo,Consumer Issues,Pedestrian";
  issueTypeCount = split(issueTypeList, issueTypes, ",");

  catTypeList = "Arts and Cultural Affairs>The Arts,Social Services>Temporary and Disability Assistance,Education,Environment,Housing,Health,Human Rights>LGBT Rights,Social Services,Local Government,Aging>Seniors,Human Rights>Women'\''s Issues,*NOMATCH*,General Constituent Service,Business and Economic Development,Environment>Lands and Forests,*NOMATCH*,Human Rights>Civil Rights,Labor,Judiciary,Agriculture>Animal Protection and Regulation,Social Services>Children and Family Assistance,Recreation and Tourism>State Parks,Housing>Homeless,*NOMATCH*,*NOMATCH*,Crime and Corrections>Weapons and Ammunition,Telecommunications,Government Operations,Transportation,Housing>Co-ops and Condos,Consumer Protection,Transportation>Pedestrian";
  catTypeCount = split(catTypeList, catTypes, ",");
}
{
  field_num = parse_csv_nl($0, csv, "|");
  if (field_num < 0) {
    print "An error was encountered at record number " FNR >"/dev/stderr";
    exit 1;
  }

  contactId = csv[1] + 1000000;
  title = csv[2];
  fnm = csv[3];
  minit = csv[4];
  lnm = csv[5];
  suffix = csv[6];
  haddr1 = csv[7];
  haddr2 = csv[8];
  hcity = csv[9];
  hstate = csv[10];
  hzip = csv[11];
  waddr1 = csv[12];
  waddr2 = csv[13];
  wcity = csv[14];
  wstate = csv[15];
  wzip = csv[16];
  company = csv[17];
  jobTitle = csv[18];
  wphone = csv[19];
  wext = csv[20];
  hphone = csv[21];
  mphone = csv[22];
  hfax = csv[23];
  wfax = csv[24];
  email = csv[25];
  birthDate = csv[26];
  contactTypeId = csv[27];
  notes = csv[28];
  spouseName = csv[29];
  childNames = csv[30];
  issue1 = csv[31];
  issue2 = csv[32];
  issue3 = csv[33];
  lovesLiz = csv[34];
  genNotes = csv[35];
  colLineage = csv[36];
  generation = csv[37];
  guid = csv[38];
  lineage = csv[39];
  groups = csv[40];
  website = csv[41];
  seniorList = csv[42];
  dateModified = csv[43];
  timeModified = csv[44];
  # OMIS TCODE1 set to 104 (TITLE CODE NOT ASSIGNED).
  tcode1 = 104;
  # OMIS RECTYPE defaults to 1 (individual record).
  rectype = 1;

  # If no first name, last name, and company name are specified for a given
  # record, then skip it.
  if (fnm == "" && lnm == "" && company == "") {
    print "Record " csv[1] " is being skipped due to missing firstName, lastName, and companyName; full record is [" $0 "]" >"/dev/stderr";
    next;
  }

  # If a company name was specified, then consider this to be a business record.
  if (company != "") {
    rectype = 7;
  }

  # Guess the gender from the title
  gender = "";
  if (match(title, "^[ ]*(MRS|Mrs|mrs|MS|Ms|ms)[^A-Za-z]*$")) {
    gender = "F";
  }
  else if (match(title, "^[ ]*(MR|Mr|mr|Father|FR|Fr)[^A-Za-z]*$")) {
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
  
  # Convert contactTypeId into contactType
  contactType = "";
  if (contactTypeId >= 1 && contactTypeId <= contactTypeCount) {
    contactType = contactTypes[contactTypeId];
  }

  # Split date of birth into month/day/year
  bmm = bdd = byy = "";
  n = split(birthDate, a, "/");
  if (n == 3) {
    bmm = a[1];
    bdd = a[2];
    byy = a[3];
  }
    
  longstate = "";
  if (hstate == "NY") {
    longstate = "New York";
  }

  # Save the contact record in OMIS format
  # The following SD26 fields are not saved, as they are unused:
  # genNotes, colLineage, generation, guid, lineage, timeModified 

  print contactId, lnm, fnm, minit, suffix, houseno, street, haddr2,
        hcity, hstate, hzip, "", 0, "", rectype, "", "", gender, "", "", "",
        district, "", "", "", tcode1, "", bmm, bdd, byy, hphone, dateModified,
        "", "", "", "", "", "", jobTitle, company, title, title, "", "",
        longstate,
        waddr1, waddr2, wcity, wstate, wzip, wphone, wext,
        mphone, hfax, wfax, email, contactType, spouseName, childNames,
        lovesLiz, groups, website, seniorList >>MSTFILE;

  # Up to three issues per contact are saved
  if (issue1 > 0 && issue1 <= issueTypeCount) {
    print contactId, issue1, dateModified, issueTypes[issue1],
          catTypes[issue1], is_tag >>ISSFILE;
  }
  if (issue2 > 0 && issue2 <= issueTypeCount) {
    print contactId, issue2, dateModified, issueTypes[issue2],
          catTypes[issue2], is_tag >>ISSFILE;
  }
  if (issue3 > 0 && issue3 <= issueTypeCount) {
    print contactId, issue3, dateModified, issueTypes[issue3],
          catTypes[issue3], is_tag >>ISSFILE;
  }


  # Split the notes field up into multiple history lines.
  if (notes) {
    p = 0;
    n = split(notes, nline, "|");
    do {
      i = p*15;
      print contactId, 1, p+1,
            nline[i+1], nline[i+2], nline[i+3], nline[i+4], nline[i+5],
            nline[i+6], nline[i+7], nline[i+8], nline[i+9], nline[i+10],
            nline[i+11], nline[i+12], nline[i+13], nline[i+14], nline[i+15] >>HISFILE;
      p++;
    } while (n > p*15);
  }
}
END {
}'

if [ $? -eq 0 ]; then
  mv sd$district sd${district}ext
  zip -r sd${district}ext.zip sd${district}ext
fi

