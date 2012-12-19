<?php

// Project: BluebirdCRM
// Authors: Ash Islam
// Organization: New York State Senate
// Date: 2012-12-17

//-------------------------------------------------------------------------------------
// This file contains the markup to render the summary page for redistricting
// in HTML format. This is not intended to be standalone and is used by
// RedistrictingReports.php
//-------------------------------------------------------------------------------------

?>

<html>
	<head>
		<title>
			<?= $title ?>
		</title>
		<style type="text/css">
			table {
				border-width: 1px;
				border-spacing: 2px;
				border-style: outset;
				border-color: gray;
				border-collapse: collapse;
				background-color: white;
			}
			table th {
				border-width: 1px;
				padding: 5px;
				border-style: inset;
				border-color: gray;
				background-color: white;
				-moz-border-radius: ;
			}
			table td {
				border-width: 1px;
				padding: 5px;
				border-style: inset;
				border-color: gray;
				background-color: white;
				-moz-border-radius: ;
			}
			hr {
				border:1px solid #999;
				border-style: solid none none none;
			}
		</style>
	</head>
	<body>

		<?php if ($mode == "summary"): ?>
		<h1>Redistricting Summary for Senate District <?= $senator_district ?></h1>
		<h3><?= $senator_name ?></h3>
		<hr/>
		<p>The following table indicates the number of individuals, households, and organizations that will
		   be in the districts shown in the left column.
		</p>

		<table>
			<tr>
				<th>District</th>
				<th>Individuals</th>
				<th>Households</th>
				<th>Organizations</th>
			</tr>

			<?php

			ksort($summary_cnts);
			foreach( $summary_cnts as $dist => $dist_cnts ): ?>
				<tr>
					<td><?= $dist ?></td>
					<td><?= get($dist_cnts, 'individual', '0') ?></td>
					<td><?= get($dist_cnts, 'household', '0')?></td>
					<td><?= get($dist_cnts, 'organization', '0')?></td>
				</tr>
			<?php endforeach; ?>
		</table>

	<?php elseif ($mode == "detail"): ?>
		<h1>Redistricting Details for Senate District <?= $senator_district ?></h1>
		<h3><?= $senator_name ?></h3>
		<hr/>

		<p>The tables below list the contacts that will be in the districts specified.
		</p>
		<?php
			// Table columns for contact details
			$html_columns = array(
				"individual" => array(
					"Name" => 30, "Sex" => 6, "Age" => 6, "Address" => 25, "City" => 17, "Zip" => 6,
					"Email" => 20, "Source" => 9, "Cases" => 8, "Actvities" => 10, "BB Rec#" => 9 ),

				"organization" => array(
					"Organization Name" => 30, "Address" => 37, "City" => 17, "Zip" => 6, "Email" => 20,
			        "Source" => 9, "Cases" => 8, "Actvities" => 10, "BB Rec#" => 9 ),

				"household" => array(
					"Household Name" => 30, "Address" => 37, "City" => 17, "Zip" => 6, "Email" => 20,
			        "Source" => 9, "Cases" => 8, "Actvities" => 10, "BB Rec#" => 9)
			);
		?>

		<?php

		// Sort the detailed data by district number
		ksort($detail_data);

		foreach( $detail_data as $dist => $contact_types )
			foreach( $contact_types as $type => $contact_array ): ?>

				<h3>District <?= "$dist : " . ucfirst($type) . "s" ?></h3>
				<table id="dist_<?= $dist . "_" . $type ?>">
					<tr>
					<?php foreach($html_columns[$type] as $name => $width): ?>
						<th><?= $name ?></th>
					<?php endforeach; ?>
					</tr>

					<?php foreach($contact_array as $contact): ?>
				 	<tr>
				 		<?php if($type == "individual"): ?>
				 			<td><?= $contact['last_name'].", ".$contact['first_name']?></td>
				 			<td><?= get_gender($contact['gender_id']) ?></td>
				 			<td><?= get_age($contact['birth_date']) ?></td>
				 			<td><?= $contact['street_address'] ?></td>

				 	    <?php elseif($type == "household"): ?>
				 			<td><?= $contact['household_name'] ?></td>
				 			<td><?= $contact['street_address'] ?></td>

				 		<?php elseif($type == "organization"): ?>
				 			<td><?= $contact['organization_name'] ?></td>
				 			<td><?= $contact['street_address'] ?></td>

						<?php endif; ?>

							<td><?= $contact['city'] ?></td>
							<td><?= $contact['postal_code'] ?></td>
					      	<td><?= $contact['email'] ?></td>
					      	<td><?= $contact['source'] ?></td>
					      	<td><?= $contact['case_count'] ?></td>
					      	<td><?= $contact['activity_count'] ?></td>
					      	<td><?= $contact['contact_id'] ?></td>
					</tr>
					<?php endforeach; ?>

				</table>
				<br/>
				<br/>
			<?php endforeach; ?>
	<?php endif; ?>
	</body>
</html>
