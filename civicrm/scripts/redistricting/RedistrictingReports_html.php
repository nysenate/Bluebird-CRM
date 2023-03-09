<?php

// Project: BluebirdCRM
// Authors: Ash Islam, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-12-17
// Revised: 2023-02-17

//-----------------------------------------------------------------------------
// This file contains the markup to render the summary page for redistricting
// in HTML format. This is not intended to be standalone and is called by
// RedistrictingReports.php
//-----------------------------------------------------------------------------

define('RESOURCES_DIR', realpath(dirname(__FILE__)).'/report');

//-----------------------------------------------------------------------------
// HTML FORMAT
//-----------------------------------------------------------------------------


function generate_html_start($dist)
{
  $title = 'Redistricting '. REDIST_YEAR . ' Summary';
?>
<html>
  <head>
    <title>
      <?= $title ?>
    </title>
    <!-- Get datatables css from cdn so that images can be downloaded -->
    <link rel="stylesheet" type="text/css" href="https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
    <script>
      <?php include RESOURCES_DIR . '/resources.js'; ?>
    </script>
  </head>
  <body>
  <style>
      <?php include RESOURCES_DIR . '/main.css' ?>
  </style>

  <div class="header">
    <h1>Bluebird CRM - Redistricting Report for District <?= $dist ?></h1>
  </div>
<?php
} // generate_html_start()


function generate_html_end($mode = 'summary')
{
?>
  <br/>
  </body>

  <script>
    <?php
    // Include the js that initializes the charts and tables
    include RESOURCES_DIR . "/{$mode}_report.js";
    ?>
  </script>
</html>
<?php
} // generate_html_end()


function get_summary_output($district_counts, $crm_dist, $name)
{
  // Compute the total count of contact types per district
  unset($district_counts['0']);
  ksort($district_counts);
  $summary_totals = compute_summary_totals($district_counts);
  ob_start();
  generate_html_start($crm_dist);
?>
  <div class="sub-header">
    <h3>Summary Page | <?= $name ?></h3>
  </div>
  <h5 style="margin:10px">Date Created | <?= date('F j, Y, g:i a') ?></h5>
    <hr/>
  <div class="content">
    <!-- Pie Charts -->
    <div id="summary_contacts_chart" class="active pie-chart"></div>
    <div id="summary_emails_chart" class="inactive pie-chart"></div>
    <div id="summary_cases_chart" class="inactive pie-chart"></div>
    <div id="summary_activities_chart" class="inactive pie-chart"></div>

    <div id="piechart_data"
         data-contacts="<?= redist_summary_pie_data($district_counts, 'contacts', 5) ?>"
         data-emails="<?= redist_summary_pie_data($district_counts, 'active_emails', 5) ?>"
         data-activities="<?= redist_summary_pie_data($district_counts, 'all_activities', 5) ?>"
         data-cases="<?= redist_summary_pie_data($district_counts, 'all_cases', 5) ?>"></div>

    <div id="tabs">
      <ul>
        <li><a href="#contacts_table"><span>Contact Counts</span></a></li>
        <li><a href="#emails_table"><span>Email Counts</span></a></li>
        <li><a href="#cases_table"><span>Case Counts</span></a></li>
        <li><a href="#activities_table"><span>Activity Counts</span></a></li>
      </ul>

      <!-- Table to display number of contacts, broken down by contact type -->
      <div id="contacts_table">
        <div class="quick-stats">
          <p>Summary - Individuals: <?= number_format($summary_totals['individual']) ?>&nbsp; | &nbsp;
             Households: <?= number_format($summary_totals['household']) ?>&nbsp; | &nbsp;
             Organizations: <?= number_format($summary_totals['organization']) ?>&nbsp; | &nbsp;
             Total:  <?= number_format($summary_totals['contacts']) ?> out-of-district contacts.
          </p>
        </div>
        <table class="summary contacts">
          <thead>
          <tr>
            <th>District</th>
            <th>Senator</th>
            <th>Individuals</th>
            <th>Households</th>
            <th>Organizations</th>
            <th>Total</th>
          </tr>
          </thead>
          <tbody>
          <?php
          foreach ($district_counts as $district => $counts):
          ?>
            <tr>
              <td class="border-right"><?= $district ?></td>
              <td class="border-right">
                <a target="_blank" href="<?= get_senator_url($district) ?>"><?= get_senator_name($district) ?></a>
              </td>
              <td class="border-right"><?= get($counts, 'individual', '0') ?></td>
              <td class="border-right"><?= get($counts, 'household', '0') ?></td>
              <td class="border-right"><?= get($counts, 'organization', '0') ?></td>
              <td><?= get($counts, 'contacts','0') ?></td>
            </tr>
          <?php
          endforeach;
          ?>
        </tbody>
        </table>
      </div>

      <div id="emails_table">
        <!-- Table to display the number of email addresses -->
        <div class="quick-stats">
          <p>Summary - Active Email Addresses: <?= number_format($summary_totals['active_emails']) ?>&nbsp; | &nbsp;
             Total Email Addresses: <?= number_format($summary_totals['all_emails']) ?>
          </p>
        </div>
        <table class="summary emails">
          <thead>
          <tr>
            <th>District</th>
            <th>Senator</th>
            <th>Active Email Addresses</th>
            <th>Total Email Addresses</th>
          </tr>
          </thead>
          <tbody>
          <?php
          foreach ($district_counts as $district => $counts):
          ?>
            <tr>
              <td class="border-right"><?= $district ?></td>
              <td class="border-right">
                <a target="_blank" href="<?= get_senator_url($district) ?>"><?= get_senator_name($district) ?></a>
              </td>
              <td class="border-right"><?= get($counts, 'active_emails', '0') ?></td>
              <td><?= get($counts, 'all_emails', '0') ?></td>
            </tr>
          <?php
          endforeach;
          ?>
        </tbody>
        </table>
      </div>

      <div id="cases_table">
        <!-- Table to display the number of cases -->
        <div class="quick-stats">
          <p>Summary - Active Cases: <?= number_format($summary_totals['active_cases']) ?>&nbsp; | &nbsp;
             Total Cases: <?= number_format($summary_totals['all_cases']) ?>
          </p>
        </div>
        <table class="summary cases">
          <thead>
          <tr>
            <th>District</th>
            <th>Senator</th>
            <th>Open Cases</th>
            <th>Urgent Cases</th>
            <th>Assigned Cases</th>
            <th>Inactive Cases</th>
            <th>Total Cases</th>
          </tr>
          </thead>
          <tbody>
          <?php
          foreach ($district_counts as $district => $counts):
          ?>
            <tr>
              <td class="border-right"><?= $district ?></td>
              <td class="border-right">
                <a target="_blank" href="<?= get_senator_url($district) ?>"><?= get_senator_name($district) ?></a>
              </td>
              <td class="border-right"><?= get($counts, 'open_cases', '0') ?></td>
              <td class="border-right"><?= get($counts, 'urgent_cases', '0') ?></td>
              <td class="border-right"><?= get($counts, 'assigned_cases', '0') ?></td>
              <td class="border-right"><?= get($counts, 'inactive_cases', '0') ?></td>
              <td><?= get($counts, 'all_cases', '0') ?></td>
            </tr>
          <?php
          endforeach;
          ?>
        </tbody>
        </table>
      </div>

      <div id="activities_table">
        <!-- Table to display the number of acitivties -->
        <div class="quick-stats">
          <p>Summary - Open Activities: <?= number_format($summary_totals['open_activities']) ?>&nbsp; | &nbsp;
             Total Activities: <?= number_format($summary_totals['all_activities']) ?>
          </p>
        </div>
        <table class="summary activities">
          <thead>
          <tr>
            <th>District</th>
            <th>Senator</th>
            <th>Open Activities</th>
            <th>Total Activities</th>
          </tr>
          </thead>
          <tbody>
          <?php
          foreach ($district_counts as $district => $counts):
          ?>
            <tr>
              <td class="border-right"><?= $district ?></td>
              <td class="border-right">
                <a target="_blank" href="<?= get_senator_url($district) ?>"><?= get_senator_name($district) ?></a>
              </td>
              <td class="border-right"><?= get($counts, 'open_activities', '0') ?></td>
              <td><?= get($counts, 'all_activities', '0') ?></td>
            </tr>
          <?php
          endforeach;
          ?>
        </tbody>
        </table>
      </div>
    </div>
  </div>
<?php
  generate_html_end('summary');
  return ob_get_clean();
} // get_summary_output()


function get_detail_output($contacts_per_dist, $dist, $name, $site)
{
  // Table columns for contact details
  $html_columns = [
    'individual' => [
      'Name', 'Sex', 'Age', 'Address', 'City', 'Zip', 'Email', 'Source', 'Cases', 'Acts', 'Groups', 'Prior District', 'BB ID'
    ],
    'organization' => [
      'Organization Name', 'Address', 'City', 'Zip', 'Email', 'Source', 'Cases', 'Acts', 'Groups', 'Prior District', 'BB ID'
    ],
    'household' => [
      'Household Name', 'Address', 'City', 'Zip', 'Email', 'Source', 'Cases', 'Acts', 'Groups', 'Prior District', 'BB ID'
    ]
  ];

  // Ignore district 0
  // Sort the detailed data by district number
  unset($contacts_per_dist['0']);
  ksort($contacts_per_dist);
  ob_start();
  generate_html_start($dist);
?>
  <div class="sub-header">
    <h3>Details Page | <?= $name ?></h3>
  </div>
  <h5 style="margin:10px">Date Created | <?= date('F j, Y, g:i a') ?></h5>
  <hr/>
  <div class="content">
    <p id="detail_load_text">Please wait while the contact information loads. This may take up to a minute to complete. </p>
    <p id="pagination_toggle"><a href='#' id="toggle_pagination">Expand / Collapse All</a> - Note: may take a moment to complete.</p>

    <?php
    foreach ($contacts_per_dist as $dist => $contact_types):
    ?>
      <div id="dist_<?= $dist ?>" class="district-view">

      <?php
      foreach ($contact_types as $type => $contact_array):
      ?>
        <h4 class="contrast">District <?= "$dist | " . get_senator_name($dist) . ' | ' . ucfirst($type) . 's (' . count($contact_array) . ')' ?></h4>
        <hr/>
        <table id="dist_<?= $dist . '_' . $type ?>" class="detail">
          <thead>
            <tr>
            <?php
            foreach ($html_columns[$type] as $name):
            ?>
              <th><?= $name ?></th>
            <?php
            endforeach;
            ?>
            </tr>
          </thead>
          <tbody>
          <?php
          foreach ($contact_array as $contact):
          ?>
            <tr>
            <?php
            if ($type == 'individual'):
            ?>
              <td><a target="_blank" href="<?= 'http://' . $site . '.crm.nysenate.gov/civicrm/contact/view?cid=' . $contact['contact_id'] ?>">
              <?= $contact['last_name'].', '.$contact['first_name']?></a></td>
              <td><?= get_gender($contact['gender_id']) ?></td>
              <td><?= get_age($contact['birth_date']) ?></td>
              <td><?= $contact['street_address'] ?></td>

            <?php
            elseif ($type == 'household'):
            ?>
              <td><a target="_blank" href="<?= 'http://' . $site . '.crm.nysenate.gov/civicrm/contact/view?cid=' . $contact['contact_id'] ?>">
              <?= get($contact, 'household_name', 'Unknown') ?></a></td>
              <td><?= $contact['street_address'] ?></td>
            <?php
            elseif ($type == 'organization'):
            ?>
              <td><a target="_blank" href="<?= 'http://' . $site . '.crm.nysenate.gov/civicrm/contact/view?cid=' . $contact['contact_id'] ?>">
              <?= get($contact, 'organization_name', 'Unknown') ?></a></td>
              <td><?= $contact['street_address'] ?></td>

            <?php
            endif;
            ?>
              <td><?= $contact['city'] ?></td>
              <td><?= $contact['postal_code'] ?></td>
              <td><?= $contact['email'] ?></td>
              <td><?= ($contact['const_source'] ?? 'None').'/'.($contact['source'] ?? 'None') ?></td>
              <td><?= $contact['case_count'] ?></td>
              <td><?= $contact['activity_count'] ?></td>
              <td><?= $contact['group_count'] ?></td>
              <td><?= $contact['prior_dist'] ?? 'None' ?></td>
              <td><?= $contact['contact_id'] ?></td>
            </tr>
          <?php
          endforeach;
          ?>
          </tbody>
        </table>
        <br/>
        <br/>
      <?php
      endforeach;
      ?>
    <?php
    endforeach;
    ?>
  </div>
<?php
  generate_html_end('detail');
  return ob_get_clean();
} // get_detail_output()


function redist_summary_pie_data($district_counts, $key = 'contacts', $top_n = 5)
{
  // Format the summary data so that it can be displayed in a pie chart.
  // $top_n specifies the number of districts to show sorted by percentage

  $total_sum = 0;
  foreach ($district_counts as $counts) {
    $total_sum += get($counts, $key, 0);
  }

  $percentage_data = [];
  foreach ($district_counts as $dist => $counts) {
    if (get($counts, $key, 0) > 0) {
      $percentage_data[$dist] = get($counts, $key, 0) / $total_sum;
    }
  }

  arsort($percentage_data);
  $percentage_data = array_slice($percentage_data, 0, $top_n, true);

  $pie_data = [];
  $percent_total = 0;
  foreach ($percentage_data as $dist => $percent) {
    $percent_total += $percent;
    $pie_data[] = ["District $dist", $percent];
  }
  $pie_data[] = ["Other Districts", 1.0 - $percent_total];

  return json_encode($pie_data);
} // redist_summary_pie_data()

