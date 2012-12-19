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
		   be moving from this district.
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
	<?php endif; ?>
	</body>
</html>
