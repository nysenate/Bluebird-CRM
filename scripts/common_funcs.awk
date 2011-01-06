#
# common_funcs.awk - Shared AWK functions for CRM data conversions
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Created: 2010-05-11
# Revised: 2010-08-10
#

# Read the first two columns of a CSV file into a hash table.

function read_csv_hashtable(fname, htable,  rc, csv, fnum, key, val)
{
  rc = getline <fname
  while (rc == 1) {
    fnum = parse_csv($0, csv);
    key = csv[1];
    val = csv[2];
    if (key ~ "^[0-9]+$") {
      htable[key] = val;
    }
    rc = getline <fname;
  }

  if (rc < 0) {
    return rc;
  }
  else {
    return length(htable);
  }
} # read_csv_hashtable()



# Print the header line for each of the four OMIS data files.
# Side-effect: Sets the filenames for the data files as global vars.

function print_file_headers(district)
{
  MSTFILE="sd" district "/SD" district "EXTMST.TXT";
  ISSFILE="sd" district "/SD" district "EXTISSCONV.TXT";
  CASFILE="sd" district "/SD" district "EXTCAS.TXT";
  HISFILE="sd" district "/SD" district "EXTHIS.TXT";

  print "KEY", "LAST", "FIRST", "MI", "SUFFIX", "HOUSE", "STREET", "MAIL",
        "CITY", "STATE", "ZIP5", "ZIP4", "SKF", "SKEY", "RT", "MS", "RCD",
        "SEX", "WD", "TN", "CT", "SD", "SCD", "ED", "AD", "TC1", "TC2",
        "BMM", "BDD", "BYY", "PHONE", "CHG", "DEO", "REQ", "OVERFLOW", "LGD",
        "FAM1", "FAM2", "OTITLE", "OCOMPANY", "INSIDE1", "SALUTE1",
        "INSIDE2", "SALUTE2", "LONGSTATE",
        "ADDR_WORK_STREET1", "ADDR_WORK_STREET2", "ADDR_WORK_CITY",
        "ADDR_WORK_STATE", "ADDR_WORK_ZIP", "PHONE_WORK", "PHONE_WORK_EXT",
        "PHONE_MOBILE", "FAX_HOME", "FAX_WORK", "EMAIL", "CONTACT_TYPE",
        "SPOUSE", "CHILDREN", "LOVES_LIZ", "GROUPS", "WEBSITE", "SENIORS",
        "NON_DISTRICT" >MSTFILE;

  print "KEY", "CASENUM", "CSUBJECT", "CSTAFF", "COPENTIME", "COPENDATE",
        "CCLOSEDATE", "CHOMEPH", "CWORKPH", "CFAXPH", "CSNUM",
        "CLAB1", "CID1", "CLAB2", "CID2", "CISSUE", "CFORM", "CPLACE",
        "CNOTE1", "CNOTE2", "CNOTE3", "CLASTUPDATE", "LEGISLATION" >CASFILE;

  print "KEY", "ISSUECODE", "UPDATED", "ISSUEDESCRIPTION", "CATEGORY", "IS_TAG" >ISSFILE;

  print "KEY", "HNUM", "HPAG", "HL1", "HL2", "HL3", "HL4", "HL5", "HL6", "HL7",
        "HL8", "HL9", "HL10", "HL11", "HL12", "HL13", "HL14", "HL15" >HISFILE;

  return 0;
} # print_file_headers()



# Convert a numeric OMIS issue code into a CiviCRM issue category.

function convert_isscode_to_category(isscode,
                                     isscodes, category, codeprefix, normcode)
{
  isscodes[10000] = "Aging";
  isscodes[12000] = "Agriculture";
#  isscodes[14000] = "Codes";
  isscodes[14000] = "Alcohol and Substance Abuse";
  isscodes[16000] = "Arts and Cultural Affairs";
  isscodes[18000] = "Banks";
  isscodes[20000] = "Business and Economic Development";
  isscodes[22000] = "Children and Families";
  issocdes[24000] = "Recreation and Tourism";
  isscodes[26000] = "Consumer Protection";
  isscodes[28000] = "Judiciary";
  isscodes[30000] = "Crime and Corrections";
  isscodes[32000] = "Education";
  isscodes[34000] = "Higher Education";
  isscodes[36000] = "Elections";
  isscodes[38000] = "Energy";
  isscodes[40000] = "Environment";
  isscodes[42000] = "Individuals With Disabilities";
  isscodes[44000] = "Health";
  isscodes[46000] = "Transportation";
  isscodes[48000] = "Housing";
  isscodes[50000] = "Human Rights";
  isscodes[52000] = "Insurance";
  isscodes[54000] = "Labor";
  isscodes[56000] = "Legislature";
  isscodes[58000] = "Local Government";
  isscodes[60000] = "Mental Health";
  isscodes[62000] = "New York City";
  isscodes[64000] = "Professions";
#  isscodes[66000] = "Labor>Civil Service and Pensions";
  isscodes[66000] = "Civil Service and Pensions";
  isscodes[68000] = "Racing and Wagering";
#  isscodes[70000] = "Taxes>Property Tax";
  isscodes[70000] = "Property Tax";
  isscodes[72000] = "Social Services";
  isscodes[74000] = "Government Operations";
  isscodes[76000] = "Taxes";
#  isscodes[76000] = "Tax and Finance";
  isscodes[78000] = "Transportation";
  isscodes[80000] = "Military Affairs";
  isscodes[82000] = "General Constituent Service";
  isscodes[83000] = "Merge/Purge";

  category = "*NOMATCH*";

  if (isscodes[isscode]) {
    category = isscodes[isscode];
  }
  else {
    codeprefix = substr(isscode, 1, 2);
    normcode = codeprefix "000";
    if (isscodes[normcode]) {
      category = isscodes[normcode];
    }
  }

  return category;
} # convert_isscode_to_category()



# Convert a date string in mmddyy format to yyyymmdd format.

function convert_mmddyy_to_yyyymmdd(datestr,  month, day, year)
{
  month = substr(datestr, 1, 2);
  day = substr(datestr, 3, 2);
  year = substr(datestr, 5, 2);

  if (year >= 50 && year <= 99) {
    year = "19" year;
  }
  else {
    year = "20" year;
  }

  return year "-" month "-" day;
} # convert_mmddyy_to_yyyymmdd()



# Convert a phone number into the 10-digit "digits-only" format.
# Examples:
#   (123) 456-7890 => 1234567890
#   (123)456-7890 => 1234567890
#   123-456-7890 => 1234567890
#   123.456.7890 => 1234567890

function convert_phone_number(pstr)
{
  if (match(pstr, "([0-9][0-9][0-9])[)]?[ ]*[.-]?[ ]*([0-9][0-9][0-9])[ ]*[.-]?[ ]*([0-9][0-9][0-9][0-9])", a)) {
    return a[1] a[2] a[3];
  }
  else {
    return pstr;
  }
} # convert_phone_number()
