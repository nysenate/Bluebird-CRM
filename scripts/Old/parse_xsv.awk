#!/usr/bin/awk -f
#****************************************************************************
#
# parse_xsv - Parse a file of values that are separated by a delimiter,
#             typically a comma.
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-04-12
# Revised: 2010-04-30
# Revised: 2020-02-06 - added parse_ssv()
#
# Parameters:
#   string  = The string to parse.
#   xsv     = The array to parse the fields into.
#   sep     = The field separator character.  Typically, a comma (,).
#   quote   = The string quote character.  Typically, a double-quote (").
#   escape  = The quote escape character.  Typically, a double-quote (").
#   newline = Newline substitution char/string.  This is the text that
#             will be replaced when a newline within a quoted string is
#             encountered.  An empty value causes embedded newlines to
#             throw an error.
#   trim    = Controls how whitespace is preserved or trimmed from each field.
#             0 = no whitespace trimming is performed
#             1 = trim leading and trailing spaces from unquoted fields
#             2 = trim leading newlines from quoted fields
#             3 = combination of 1 and 2
#
# Returns:
#   On success, the number of fields that were stored is returned.
#   On failure, 'xsverr' is set to the error text and a negative value
#   is returned, as follows:
#     -1  = Unable to read the next line.
#     -2  = Missing end quote (line exhausted prior to end of quoted string)
#
# In addition, a warning issued if the last double-quote of a quoted string
# is not the final non-space character within that string.
#
#****************************************************************************

#
# This function determines if the last quote in the current field is
# definitely an end-quote (meaning that it is the final character in the
# field and it is not an escaped quote).
#
function is_end_quote(string, quote, escape,   qidx)
{
  qidx = match(string, "(("escape quote")+|[^"escape"]|^)"quote"[^"quote"]*$");
  if (qidx > 0) {
    # If the last quote in the field is not the final non-space character,
    # then issue a warning and use the entire string.
    qidx = match(string, quote"[ ]*$");
    if (qidx > 0) {
      return qidx;
    }
    else {
      print "Warning: Quoted string [" string "] is improperly terminated." >>"/dev/stderr";
      return length(string);
    }
  }
  else {
    return 0;
  }
} # is_end_quote()


#
# This is the main parsing function.  It parses a single line of delimited
# fields, and stores the value of each field into the provided array.
# This function will attempt to read the next line of input if necessary.
#
function parse_xsv(string, xsv, sep, quote, escape, newline, trim,
                   ifieldnum, ofieldnum, ifields, ifieldidx,
                   curfield, in_quote)
{
  ofieldnum = 0;
  in_quote = 0;

  do {
    ifieldnum = split(string, ifields, sep);
    ifieldidx = 1;
  
    while (ifieldidx <= ifieldnum) {
      curfield = ifields[ifieldidx];
      if (in_quote) {
        qidx = is_end_quote(curfield, quote, escape);
        if (qidx > 0) {
          curfield = substr(curfield, 1, qidx - 1);
          qfield = qfield ((ifieldidx > 1) ? sep : "") curfield;
          gsub(escape quote, quote, qfield);
          xsv[++ofieldnum] = qfield;
          in_quote = 0;
        }
        else {
          qfield = qfield ((ifieldidx > 1) ? sep : "") curfield;
        }
      }
      else {
        # Search for a quote as the first non-space character in the field.
        # If found, all leading space and the quote itself are eliminated.
        in_quote = sub("^[ ]*"quote, "", curfield);
        if (in_quote) {
          qfield = curfield;
          # Check if the entire quoted string is contained in this field.
          qidx = is_end_quote(qfield, quote, escape);
          if (qidx > 0) {
            qfield = substr(qfield, 1, qidx - 1);
            gsub(escape quote, quote, qfield);
            xsv[++ofieldnum] = qfield;
            in_quote = 0;
          }
        }
        else {
          if (trim == 1 || trim == 3) {
            gsub("^[ ]+|[ ]+$", "", curfield);
          }
          xsv[++ofieldnum] = curfield;
        }
      }
  
      ifieldidx++;
    } # end while
  
    # If the fields in the current line have been exhausted, but we're
    # still in a quoted field, we must append the next line onto this line
    # and continue processing, unless 'newline' is empty, in which case,
    # throw an error.
    if (in_quote) {
      if (newline) {
        if (getline string <= 0) {
          xsverr = "Unable to read next line";
          return -1;
        }
        else if (trim < 2 || qfield) {
          # Append the newline substitution char as long as newline trim
          # is not enabled.  If it is enabled, only trim leading newlines.
          qfield = qfield newline;
        }
      }
      else {
        xsverr = "Missing end quote";
        return -2;
      }
    }
  } while (in_quote);

  return ofieldnum;
} # parse_xsv()


function parse_csv_nl(string, csv, newline)
{
  return parse_xsv(string, csv, ",", "\"", "\"", newline, 3);
} # parse_csv_nl()


function parse_csv(string, csv)
{
  return parse_csv_nl(string, csv, "\n");
} # parse_csv()


function parse_psv(string, psv)
{
  return parse_xsv(string, psv, "|", "\"", "\"", "~", 1);
} # parse_psv()


function parse_ssv(string, ssv)
{
  return parse_xsv(string, ssv, "~", "\"", "\"", "", 1);
} # parse_ssv()


function parse_tsv(string, tsv)
{
  return parse_xsv(string, tsv, "	", "\"", "\"", "~", 1);
} # parse_tsv()
