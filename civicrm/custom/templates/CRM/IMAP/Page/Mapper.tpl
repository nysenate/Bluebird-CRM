<div class="crm-content-block imapperbox " id="Unmatched">
	<div class='full'>
	<h1>Unmatched Messages <small id='total_results'><span id="total_number">Loading</span> results</small></h1>
	</div>
	<div id='top'></div>
	<div class='full'>
		<table id="sortable_results" class=""> 
			<thead> 
				<tr class='list_header'> 
				    <th class='checkbox' ><input type="checkbox" name="" value="" class="checkbox_switch" /></th> 
				    <th class='Name'>Sender’s Name</th> 
 				    <th class='Subject'>Subject</th> 
				    <th class='Date'>Date Sent</th> 
				    <th class='Forwarded'>Forwarded By</th> 
				   	<th class='Actions'>Actions</th> 
				</tr> 
			</thead> 
			<tbody id='imapper-messages-list' > 
				<tr><td>Loading Message data <img src="/sites/default/themes/Bluebird/images/loading.gif"/></td></tr>
			</tbody> 
		</table>		
		<div class='page_actions'>
			<input type="button" class="multi_delete" id="" value="Delete" name="delete">
		</div>
	</div>
	<div id="find-match-popup" title="Loading Data" style="display:none;">
		<div id="message_left">
			<div id="message_left_header">
			</div>
			<div id="message_left_email">
			</div>
		</div>
		<div id="message_right">
			<div id="tabs">
				<ul>
					<li><a href="#tab1">Find Contact</a></li>
					<li><a href="#tab2">Add Contact</a></li>
				</ul>
				<input type="hidden" class="hidden" id="email_id" name="email_id">
				<input type="hidden" class="hidden" id="imap_id" name="imap_id">
				<div id="tab1">
					<input type="text" placeholder="First Name" class="form-text first_name" name="first_name">
					<input type="text" placeholder="Last Name"  class="form-text last_name" name="last_name">
					<input type="text" placeholder="Email Address" class="form-text email_address" name="email_address">
					<input type="text" placeholder="yyyy-mm-dd" class="form-text dob" name="dob">
					<input type="text" placeholder="Phone Number" class="form-text phone" name="phone">
					<input type="text" placeholder="Street Address"  class="form-text street_address" name="street_address">
					<input type="text" placeholder="City" class="form-text city" name="city">
					<input type="button" class="imapper-submit" id="filter" value="Search" name="filter">
					<div id="imapper-contacts-list" class="contacts-list"> </div>
					<input type="button" class="imapper-submit" id="assign" value="Assign" name="Assign">
				</div>
				<div id="tab2">
					<input type="text" placeholder="First Name" class="form-text first_name" name="first_name">
					<input type="text" placeholder="Last Name"  class="form-text last_name" name="last_name">
					<input type="text" placeholder="Email Address" class="email-address email_address" name="email_address">
					<input type="text" placeholder="Phone Number" class="form-text phone" name="phone">
					<input type="text" placeholder="Street Address"  class="form-text street_address" name="street_address">
					<input type="text" placeholder="Street Address (2)"  class="form-text street_address_2" name="street_address_2">
					<input type="text" placeholder="Zip Code"  class="form-text zip" name="zip">
					<input type="text" placeholder="City" class="form-text city" name="city">
					<input type="button" class="imapper-submit" id="add-contact" value="Add Contact" name="add-contact">
				</div>
			</div>
		</div>
	</div>
	<div id="delete-confirm" title="Delete Message from Unmatched Messages?">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>These items will be removed permanently. Are you sure?</p>
	</div>
	<div id="loading-popup" title="please wait">
 		<p> Loading message details.</p>
	</div>
</div>