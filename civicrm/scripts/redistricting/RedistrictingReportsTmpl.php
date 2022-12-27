<?php

// Project: BluebirdCRM
// Authors: Ash Islam
// Organization: New York State Senate
// Date: 2012-12-17

//------------------------------------------------------------------------------
// This file contains the markup to render the summary page for redistricting  |
// in HTML format. This is not intended to be standalone and is called by      |
// RedistrictingReports.php                                                    |
//------------------------------------------------------------------------------

define('RESOURCES_DIR', 'redistricting_report');

if ($format == 'text') {

//------------------------------------------------------------------------------
// TEXT FORMAT 																   |	
//------------------------------------------------------------------------------
	
	if ($mode == 'summary'){

		$label = "${senator_name} District {$senate_district}\n"
			    ."Summary of contacts that are outside district {$senate_district}\n";

		$columns = array(
			"Senate District" => 12,
			"Individuals" => 15,
			"Households" => 14,
			"Organization" => 14,
			"Total"	=> 16
		);

		$heading = $label . create_table_header($columns);

		$output_row = "";
		ksort($district_counts);

		foreach( $district_counts as $district => $counts ){
			$output_row .=  fixed_width( $district,12,false,"Unknown" )
						   .fixed_width( get($counts,'individual','0'), 15, true )
						   .fixed_width( get($counts,'household','0'), 14, true )
						   .fixed_width( get($counts,'organization','0'), 14, true )
						   .fixed_width( get($counts,'contacts','0'), 16, true ) ."\n";
		}

		print $heading . $output_row;
	}

	// Detail Report

	else if ($mode == 'detail'){
		$output = "";

		// Column names and widths
		$columns = array(
			"individual" => array(
				"Name" => 30, "Sex" => 6, "Age" => 6, "Address" => 25, "City" => 17, "Zip" => 6,
				"Email" => 20, "Source" => 9, "Cases" => 8, "Acts" => 10, "Groups" =>8, "BB Rec#" => 9 ),

			"organization" => array(
				"Organization Name" => 30, "Address" => 37, "City" => 17, "Zip" => 6, "Email" => 20,
		        "Source" => 9, "Cases" => 8, "Acts" => 10, "Groups" =>8, "BB Rec#" => 9 ),

			"household" => array(
				"Household Name" => 30, "Address" => 37, "City" => 17, "Zip" => 6, "Email" => 20,
		        "Source" => 9, "Cases" => 8, "Acts" => 10, "Groups" =>8, "BB Rec#" => 9)
		);

		// Sort by districts
		ksort($contacts_per_dist);

		// Ignore contacts in District 0. They are either out of state or
		// won't be assigned to another district regardless.
		unset($contacts_per_dist["0"]);

		foreach( $contacts_per_dist as $dist => $contact_types ){
			foreach( $contact_types as $type => $contact_array ){

				$label = "\nDistrict $dist : " . ucfirst($type) . "s\n";
				$heading = create_table_header($columns[$type]);
				$output .= $label . $heading;

				foreach($contact_array as $contact){
					if ($type == "individual"){
						$output .= fixed_width($contact['last_name'].", ".$contact['first_name'], 30)
						         . fixed_width(get_gender($contact['gender_id']),6, true)
						         . fixed_width(get_age($contact['birth_date']), 6, false)
						         . fixed_width($contact['street_address'], 25, false, "---") . " ";
					}
					else if ($type == "household"){
						$output .= fixed_width($contact['household_name'], 29) . " "
								.  fixed_width($contact['street_address'], 37, false, "---") . " ";
					}
					else if ($type == "organization"){
						$output .= fixed_width($contact['organization_name'], 29) . " "
								.  fixed_width($contact['street_address'], 37, false, "---") . " ";
					}

					$output .=  fixed_width($contact['city'], 15) . " "
						      . fixed_width($contact['postal_code'],6)
						      . fixed_width($contact['email'], 21, false, "---")
						      . fixed_width($contact['source'], 9, true )
						      . fixed_width($contact['case_count'], 9)
						      . fixed_width($contact['activity_count'], 9)
						      . fixed_width($contact['group_count'], 9)
						      . fixed_width($contact['contact_id']);
					$output .= "\n";
				}
			}
		}

		print $output . "\n\n";
	}
}
?>
<?php if ($format == 'html'):

//------------------------------------------------------------------------------
// HTML FORMAT 																   |	
//------------------------------------------------------------------------------

?>
<html>
	<head>
		<title>
			<?= $title ?>
		</title>
		<!-- Get datatables css from cdn so that images can be downloaded -->
		<link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
		<script>
			<?php include RESOURCES_DIR . '/resources.js'; ?>
		</script>
	</head>
	<body>
	
	<style>
			<?php include RESOURCES_DIR . '/main.css' ?>
	</style>
	
	<div class='header'>
		<h1>Bluebird CRM - Redistricting Report for District <?= $senate_district ?></h1>
	</div>
	
	<?php if ($mode == "summary"): ?>
	<div class='sub-header'>
		<h3>Summary Page | <?= $senator_name ?></h3>
	</div>
	<h5 style='margin:10px'>Date Created | <?= date("F j, Y, g:i a") ?></h5>
		<hr/>
	<div class='content'>

		<!-- Pie Charts -->
		<div id="summary_contacts_chart" class="active pie-chart"></div>
		<div id="summary_emails_chart" class="inactive pie-chart"></div>
		<div id="summary_cases_chart" class="inactive pie-chart"></div>
		<div id="summary_activities_chart" class="inactive pie-chart"></div>
		
		<?php

		unset($district_counts["0"]);
		ksort($district_counts);

		// Compute the total count of contact types per district
				
		?>
		
		<div id="tabs">
	  		<ul>
			    <li><a href="#contacts_table"><span>Contact Counts</span></a></li>
			    <li><a href="#emails_table"><span>Email Counts</span></a></li>
			    <li><a href="#cases_table"><span>Case Counts</span></a></li>
			    <li><a href="#activities_table"><span>Activity Counts</span></a></li>
		    </ul>

			<!-- Table to display the number of contacts, broken down by contact type -->
			<div id="contacts_table">
				<div class="quick-stats">
					<p>Summary - Individuals: <?= number_format($summary_totals['individual']) ?>&nbsp; | &nbsp; 
					   Households: <?= number_format($summary_totals['household']) ?>&nbsp; | &nbsp;
		               Organizations: <?= number_format($summary_totals['organization']) ?>&nbsp; | &nbsp;
		               Total:  <?= number_format($summary_totals['contacts']) ?> out of district contacts.
		            </p>
				</div>
				<table class='summary contacts'>
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

					foreach( $district_counts as $district => $counts ): ?>
						<tr>
							<td class='border-right'><?= $district ?></td>
							<td class='border-right'>
								<a target="_blank" href="http://www.nysenate.gov/senator/<?= get_senator_url($district) ?>"><?= get_senator_name($district) ?></a>
							</td>
							<td class='border-right'><?= get($counts,'individual','0') ?></td>
							<td class='border-right'><?= get($counts,'household','0') ?></td>
							<td class='border-right'><?= get($counts,'organization','0') ?></td>
							<td><?= get($counts,'contacts','0') ?></td>
						</tr>
					<?php endforeach; ?>			
				</tbody>
				</table>
			</div>

			<div id="emails_table">
				<!-- Table to display the number of email addresses -->
				<div class="quick-stats">
					<p>Summary - Active Email Addresses: <?= number_format($summary_totals['active_emails']) ?>&nbsp; | &nbsp; 
					   Total Email Addresses: <?= number_format($summary_totals['emails']) ?>
		            </p>
				</div>
				<table class='summary emails'>
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

					foreach( $district_counts as $district => $counts ): ?>
						<tr>
							<td class='border-right'><?= $district ?></td>
							<td class='border-right'>
								<a target="_blank" href="http://www.nysenate.gov/senator/<?= get_senator_url($district) ?>"><?= get_senator_name($district) ?></a>
							</td>
							<td class='border-right'><?= get($counts,'active_emails','0') ?></td>
							<td><?= get($counts,'emails','0') ?></td>					
						</tr>
					<?php endforeach; ?>			
				</tbody>
				</table>
			</div>

			<div id="cases_table">
				<!-- Table to display the number of cases -->
				<div class="quick-stats">
					<p>Summary - Active Cases: <?= number_format($summary_totals['active_cases']) ?>&nbsp; | &nbsp; 
					   Total Cases: <?= number_format($summary_totals['cases']) ?>
		            </p>
				</div>
				<table class='summary cases'>
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

					foreach( $district_counts as $district => $counts ): ?>
						<tr>
							<td class='border-right'><?= $district ?></td>
							<td class='border-right'>
								<a target="_blank" href="http://www.nysenate.gov/senator/<?= get_senator_url($district) ?>"><?= get_senator_name($district) ?></a>
							</td>
							<td class='border-right'><?= get($counts,'open_cases','0') ?></td>
							<td class='border-right'><?= get($counts,'urgent_cases','0') ?></td>
							<td class='border-right'><?= get($counts,'assigned_cases','0') ?></td>
							<td class='border-right'><?= get($counts,'inactive_cases','0') ?></td>
							<td><?= get($counts,'all_cases','0') ?></td>					
						</tr>
					<?php endforeach; ?>			
				</tbody>
				</table>
			</div>

			<div id="activities_table">
				<!-- Table to display the number of acitivties -->
				<div class="quick-stats">
					<p>Summary - Open Activities: <?= number_format($summary_totals['open_activities']) ?>&nbsp; | &nbsp; 
					   Total Activities: <?= number_format($summary_totals['activities']) ?>
		            </p>
				</div>
				<table class='summary activities'>
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

					foreach( $district_counts as $district => $counts ): ?>
						<tr>
							<td class='border-right'><?= $district ?></td>
							<td class='border-right'>
								<a target="_blank" href="http://www.nysenate.gov/senator/<?= get_senator_url($district) ?>"><?= get_senator_name($district) ?></a>
							</td>
							<td class='border-right'><?= get($counts,'open_activities','0') ?></td>
							<td><?= get($counts,'activities','0') ?></td>					
						</tr>
					<?php endforeach; ?>			
				</tbody>
				</table>
			</div>
		</div>								
	</div>
	<?php elseif ($mode == "detail"): ?>
		<div class='sub-header'>
			<h3>Details Page | <?= $senator_name ?></h3>
		</div>
		<h5 style='margin:10px'>Date Created | <?= date("F j, Y, g:i a") ?></h5>
			<hr/>
		<div class='content'>
			
			<p id='detail_load_text'>Please wait while the contact information loads. This may take up to a minute to complete. </p>
			<p id='pagination_toggle'><a href='#' id='toggle_pagination'>Expand / Collapse All</a> - Note: may take a moment to complete.</p>
			
			<?php

			// Table columns for contact details
			$html_columns = array(
				"individual" => array(
					"Name","Sex","Age","Address","City", "Zip", "Email", "Source", "Cases", "Acts", "Groups", "Prior District", "BB ID" ),

				"organization" => array(
					"Organization Name", "Address", "City", "Zip", "Email", "Source", "Cases", "Acts", "Groups", "Prior District", "BB ID" ),

				"household" => array(
					"Household Name", "Address", "City", "Zip", "Email", "Source", "Cases", "Acts", "Groups", "Prior District", "BB ID")
			);

			// Ignore district 0
			// Sort the detailed data by district number
			unset($contacts_per_dist['0']);
			ksort($contacts_per_dist);

			foreach( $contacts_per_dist as $dist => $contact_types ): ?>
				<div id='dist_<?= $dist ?>' class='district-view' >

		  <?php foreach( $contact_types as $type => $contact_array ): ?>
					<h4 class='contrast'>District <?= "$dist | " . get_senator_name($dist) . " | " . ucfirst($type) . "s (" . count($contact_array) . ")"  ?></h4>
					<hr/>
					<table id="dist_<?= $dist . "_" . $type ?>" class='detail'>
						<thead>
							<tr>
							<?php foreach($html_columns[$type] as $name): ?>
								<th><?= $name ?></th>
							<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
						<?php foreach($contact_array as $contact): ?>
					 	<tr>
					 		<?php if($type == "individual"): ?>
					 			<td><a target='_blank' href='<?= "http://" . $site . ".crm.nysenate.gov/civicrm/contact/view?cid=" . $contact['contact_id'] ?>'>
					 				<?= $contact['last_name'].", ".$contact['first_name']?></a></td>
					 			<td><?= get_gender($contact['gender_id']) ?></td>
					 			<td><?= get_age($contact['birth_date']) ?></td>
					 			<td><?= $contact['street_address'] ?></td>

					 	    <?php elseif($type == "household"): ?>
					 			<td><a target='_blank' href='<?= "http://" . $site . ".crm.nysenate.gov/civicrm/contact/view?cid=" . $contact['contact_id'] ?>'>
					 				<?= get($contact,'household_name','Unknown') ?></a></td>
					 			<td><?= $contact['street_address'] ?></td>

					 		<?php elseif($type == "organization"): ?>
					 			<td><a target='_blank' href='<?= "http://" . $site . ".crm.nysenate.gov/civicrm/contact/view?cid=" . $contact['contact_id'] ?>'>
					 				<?= get($contact,'organization_name','Unknown') ?></a></td>
					 			<td><?= $contact['street_address'] ?></td>

							<?php endif; ?>
								<td><?= $contact['city'] ?></td>
								<td><?= $contact['postal_code'] ?></td>
						      	<td><?= $contact['email'] ?></td>
						      	<td><?php 
						      	   if( $contact['const_source'] != null && $contact['const_source'] != "" ){
						      	   		echo $contact['const_source']; 
						      	   		if ( $contact['source'] != null && $contact['source'] != "" ){
						      	   			echo " / ";
						      	   		}
						      	   }
								   echo $contact['source'];						      	   
						      	   ?></td>
						      	<td><?= $contact['case_count'] ?></td>
						      	<td><?= $contact['activity_count'] ?></td>
						      	<td><?= $contact['group_count'] ?></td>
						      	<td><?= $contact['prior_dist'] ?></td>
						      	<td><?= $contact['contact_id'] ?></td>
						</tr>
						<?php endforeach; ?>
						</tbody>

					</table>
					<br/>
					<br/>
				<?php endforeach; ?>
				
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	</div>
	<br/>
	</body>

	<script>
		<?php
		// Include the js that initializes the charts and tables
		include RESOURCES_DIR . '/app_js.php';
		?>
	</script>

</html>
<?php endif; ?>
<?php if ($format == 'csv') {

//------------------------------------------------------------------------------
// CSV FORMAT 																   |	
//------------------------------------------------------------------------------

	$output_row = "";
	if ($mode == "summary") {

		$heading = "District, Individuals, Households, Organizations, Total\n";
		foreach( $district_counts as $dist => $dist_cnts ){
			$row = array(
				$dist, get($dist_cnts['individual'], 'total', '0'),
				get($dist_cnts['household'], 'total', '0'),
				get($dist_cnts['organization'], 'total', '0'),
			    get($dist_cnts['all'], 'total', '0')
			);
			$output_row .=  implode(',', $row) . "\n";
		}
		print $heading . $output_row;
	}
	else if ($mode == "detail") {

		$heading = array(
			"District", "Contact Type", "BB Rec#", "Name","Sex","Age","Address","City", "Zip", "Email", "Source", "Source 2", "Cases", "Acts", "Groups", "Prior District"
		);

		$output_row = "";
		foreach( $contacts_per_dist as $dist => $contact_types ){
			foreach( $contact_types as $type => $contact_array ){
				foreach( $contact_array as $contact){

					$name = "";
					if ($type == 'individual')
						$name = $contact['last_name'].". ".$contact['first_name'];
					else if ($type == "household")
						$name = get($contact,'household_name','Unknown');
					else if ($type == "organization")
						$name = get($contact,'organization_name','Unknown');

				 	$row = array(
				 		$dist, $type, $contact['contact_id'], $name, get_gender($contact['gender_id']), get_age($contact['birth_date']),
				 		$contact['street_address'], $contact['city'], $contact['postal_code'], $contact['email'],
				 		$contact['source'], $contact['const_source'], $contact['case_count'], $contact['activity_count'], $contact['group_count'],
				 		$contact['prior_dist']
				 	);

				 	// Replace commas with spaces
				 	foreach($row as $idx => $field){
				 		$row[$idx] = str_replace(",", "", $field);
				 	}

				 	$output_row .= implode(",", $row) . "\n";

				}
			}
		}

		print implode(",", $heading) . "\n" . $output_row;
	}
}
?>

<?php
function redist_summary_pie_data($district_counts, $key = "contacts", $top_n = 5){

	// Format the summary data so that it can be displayed in a pie chart.
	// $top_n specifies the number of districts to show sorted by percentage

	$total_sum = 0;
	foreach($district_counts as $district => $counts ){
		$total_sum += get($counts,$key,0);
	}

	$percentage_data = array();
	foreach($district_counts as $district => $counts ){
		if (get($counts,$key,0) > 0){
			$percentage_data[$district] = get($counts,$key,0) / $total_sum;
		}		
	}

	arsort($percentage_data);
	$percentage_data = array_slice($percentage_data, 0, $top_n, true);

	$pie_data = array();
	$percent_total = 0;
	foreach($percentage_data as $district => $percent){
		$percent_total += $percent;
		$pie_data[] = array("District $district", $percent);
	}
	$pie_data[] = array("Other Districts", 1.0 - $percent_total);

	return json_encode($pie_data);
}
?>