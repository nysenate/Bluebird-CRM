<?php

// Project: BluebirdCRM
// Authors: Ash Islam, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-12-17
// Revised: 2023-02-17

//-----------------------------------------------------------------------------
// This file contains the markup to render the summary page for redistricting
// in TEXT format. This is not intended to be standalone and is called by
// RedistrictingReports.php
//-----------------------------------------------------------------------------

//-----------------------------------------------------------------------------
// TEXT FORMAT
//-----------------------------------------------------------------------------

function get_summary_output($district_counts, $crm_dist, $name)
{
  $label = "$name - District $crm_dist\n"
          ."Summary of contacts that are outside Senate District $crm_dist\n";

  $columns = [
    'Senate District' => 12,
    'Individuals' => 15,
    'Households' => 14,
    'Organizations' => 14,
    'Total' => 16
  ];

  $output = '';
  $heading = $label . create_table_header($columns);

  ksort($district_counts);

  foreach ($district_counts as $dist => $counts) {
    $output .=
      fixed_width($dist, 12, false, 'Unknown')
      . fixed_width(get($counts, 'individual', '0'), 15, true)
      . fixed_width(get($counts, 'household', '0'), 14, true)
      . fixed_width(get($counts, 'organization', '0'), 14, true)
      . fixed_width(get($counts, 'contacts', '0'), 16, true) . "\n";
  }

  return $heading . $output;
} // get_summary_output()


function get_detail_output($contacts_per_dist)
{
  // Detail Report

  // Column names and widths
  $columns = [
    'individual' => [
      'Name' => 30, 'Sex' => 6, 'Age' => 6, 'Address' => 25,
      'City' => 17, 'Zip' => 6, 'Email' => 20, 'Source' => 9,
      'Cases' => 8, 'Acts' => 10, 'Groups' => 8, 'BB Rec#' => 9
    ],
    'organization' => [
      'Organization Name' => 30, 'Address' => 37,
      'City' => 17, 'Zip' => 6, 'Email' => 20, 'Source' => 9,
      'Cases' => 8, 'Acts' => 10, 'Groups' => 8, 'BB Rec#' => 9
    ],
    'household' => [
      'Household Name' => 30, 'Address' => 37,
      'City' => 17, 'Zip' => 6, 'Email' => 20, 'Source' => 9,
      'Cases' => 8, 'Acts' => 10, 'Groups' => 8, 'BB Rec#' => 9
    ]
  ];

  $output = '';

  // Sort by districts
  ksort($contacts_per_dist);

  // Ignore contacts in District 0. They are either out of state or
  // won't be assigned to another district regardless.
  unset($contacts_per_dist['0']);

  foreach ($contacts_per_dist as $dist => $contact_types) {
    foreach ($contact_types as $type => $contact_array) {
      $label = "\nDistrict $dist : " . ucfirst($type) . "s\n";
      $heading = create_table_header($columns[$type]);
      $output .= $label . $heading;

      foreach ($contact_array as $contact) {
        if ($type == 'individual') {
          $output .=
            fixed_width($contact['last_name'].', '.$contact['first_name'], 30)
            . fixed_width(get_gender($contact['gender_id']), 6, true)
            . fixed_width(get_age($contact['birth_date']), 6, false)
            . fixed_width($contact['street_address'], 25, false, '---');
        }
        else if ($type == 'household') {
          $output .=
            fixed_width($contact['household_name'], 29) . ' '
            . fixed_width($contact['street_address'], 37, false, '---');
        }
        else if ($type == 'organization') {
          $output .=
            fixed_width($contact['organization_name'], 29) . ' '
            . fixed_width($contact['street_address'], 37, false, '---');
        }

        $output .= ' '
          . fixed_width($contact['city'], 15) . ' '
          . fixed_width($contact['postal_code'], 6)
          . fixed_width($contact['email'], 21, false, '---')
          . fixed_width($contact['source'], 9, true)
          . fixed_width($contact['case_count'], 9)
          . fixed_width($contact['activity_count'], 9)
          . fixed_width($contact['group_count'], 9)
          . fixed_width($contact['contact_id']);
        $output .= "\n";
      }
    }
  }

  return $output . "\n\n";
} // get_detail_output()

