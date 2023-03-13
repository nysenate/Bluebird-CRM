<?php

// Project: BluebirdCRM
// Authors: Ash Islam, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-12-17
// Revised: 2023-02-17

//-----------------------------------------------------------------------------
// This file contains functions to render the summary page for redistricting
// in CSV format. This is not intended to be standalone and is called by
// RedistrictingReports.php
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// CSV FORMAT
//-----------------------------------------------------------------------------


function get_summary_output($district_counts)
{
  $output = '';
  $heading = [
    'District', 'Individuals', 'Households', 'Organizations', 'Total'
  ];

  // Sort by district number
  ksort($district_counts);

  foreach ($district_counts as $dist => $counts) {
    $row = [
      $dist,
      get($counts, 'individual', '0'),
      get($counts, 'household', '0'),
      get($counts, 'organization', '0'),
      get($counts, 'contacts', '0')
    ];
    $output .= implode(',', $row) . "\n";
  }
  return implode(',', $heading) . "\n" . $output;
} // get_summary_output()


function get_detail_output($contacts_per_dist)
{
  $output = '';
  $heading = [
    'District', 'Contact Type', 'BB Rec#', 'Name', 'Sex', 'Age',
    'Address', 'City', 'Zip', 'Email', 'Source', 'Source 2',
    'Cases', 'Acts', 'Groups', 'Prior District'
  ];

  ksort($contacts_per_dist);

  foreach ($contacts_per_dist as $dist => $contact_types) {
    foreach ($contact_types as $type => $contact_array) {
      foreach ($contact_array as $contact) {
        $name = '';
        if ($type == 'individual') {
          $name = $contact['last_name'].', '.$contact['first_name'];
        }
        else if ($type == 'household') {
          $name = get($contact, 'household_name', 'Unknown');
        }
        else if ($type == 'organization') {
          $name = get($contact, 'organization_name', 'Unknown');
        }

        $row = [
           $dist, $type, $contact['contact_id'], $name, get_gender($contact['gender_id']), get_age($contact['birth_date']),
           $contact['street_address'], $contact['city'], $contact['postal_code'], $contact['email'],
           $contact['source'], $contact['const_source'], $contact['case_count'], $contact['activity_count'], $contact['group_count'],
           $contact['prior_dist']
        ];

        // Replace commas with spaces
        foreach ($row as $idx => $field) {
          if (strpos($field, ',')) {
            $row[$idx] = '"' . $field . '"';
          }
        }
        $output .= implode(',', $row) . "\n";
      }
    }
  }
  return implode(',', $heading) . "\n" . $output;
} // get_detail_output()

