<div class="crm-content-block imapperbox " id="Reports">
	<div class='full'>
	<h1>Inbox Reports<small id='total_results'><span id="total_number">Loading</span> results</small></h1>
	</div>
	<div id='top'></div>
	<div class='full'>
		<table id="sortable_results" class="">
			<thead>
				<tr class='list_header'>
				    <th class='checkbox' ><input type="checkbox" name="" value="" class="checkbox_switch" /></th>
				    <th class='Name'>Sender’s Info</th>
 				    <th class='Subject'>Subject</th>
				    <th class='Date'>Date Sent</th>
				    <th class='Match_type hidden'>Match Type</th>
				    <th class='Forwarded'>Forwarded By</th>
				   	<th class='Actions'>Actions</th>
				</tr>
			</thead>
			<tbody id='imapper-messages-list'>
				<td valign="top" colspan="7" class="dataTables_empty"><span class="loading_row"><span class="loading_message">Loading Message data <img src="/sites/default/themes/Bluebird/images/loading.gif"/></span></span></td>
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
					<label for="first_name">
						<span class="label_def">First Name: </span>
						<input type="text" placeholder="First Name" class="form-text first_name" name="first_name">
					</label>
					<label for="last_name">
						<span class="label_def">Last Name: </span>
						<input type="text" placeholder="Last Name"  class="form-text last_name" name="last_name">
					</label>
					<label for="email_address">
						<span class="label_def">Email: </span>
						<input type="text" placeholder="Email Address" class="form-text email_address" name="email_address">
					</label>
					<label for="dob">
						<span class="label_def">DOB: </span>
						<input type="text" placeholder="yyyy-mm-dd" class="form-text dob" name="dob">
					</label>
					<label for="phone">
						<span class="label_def">Phone #: </span>
						<input type="text" placeholder="Phone Number" class="form-text phone" name="phone">
					</label>
					<label for="street_address">
						<span class="label_def">St. Address: </span>
						<input type="text" placeholder="Street Address"  class="form-text street_address" name="street_address">
					</label>
					<label for="city">
						<span class="label_def">City: </span>
						<input type="text" placeholder="City" class="form-text city" name="city">
					</label>
					<input type="button" class="imapper-submit" id="filter" value="Search" name="filter">
					<div id="imapper-contacts-list" class="contacts-list"> </div>
					<input type="button" class="imapper-submit" id="assign" value="Assign" name="Assign">
				</div>
				<div id="tab2">
						<label for="first_name">
						<span class="label_def">First Name: </span>
						<input type="text" placeholder="First Name" class="form-text first_name" name="first_name">
					</label>
					<label for="last_name">
						<span class="label_def">Last Name: </span>
						<input type="text" placeholder="Last Name"  class="form-text last_name" name="last_name">
					</label>
					<label for="email_address">
						<span class="label_def">Email: </span>
						<input type="text" placeholder="Email Address" class="email-address email_address" name="email_address">
					</label>
					<label for="dob">
						<span class="label_def">DOB: </span>
						<input type="text" placeholder="yyyy-mm-dd" class="form-text dob" name="dob">
					</label>
					<label for="phone">
						<span class="label_def">Phone #: </span>
						<input type="text" placeholder="Phone Number" class="form-text phone" name="phone">
					</label>
					<label for="street_address">
						<span class="label_def">St. Address: </span>
						<input type="text" placeholder="Street Address"  class="form-text street_address" name="street_address">
					</label>
					<label for="street_address">
						<span class="label_def">St. Add 2: </span>
						<input type="text" placeholder="Street Address (2)"  class="form-text street_address_2" name="street_address_2">
					</label>
					<label for="city">
						<span class="label_def">City: </span>
						<input type="text" placeholder="City" class="form-text city" name="city">
					</label>
					<label for="zip">
						<span class="label_def">Zip Code: </span>
					<input type="text" placeholder="Zip Code"  class="form-text zip" name="zip">
					</label>
					<input type="button" class="imapper-submit" id="add-contact" value="Add Contact" name="add-contact">
				</div>
			</div>
		</div>
	</div>
	<div id="help-popup" title="Search Help" style="display:none;">
		<h3>Hidden Columns</h3>
		<ul>
			<li><strong>NoMatch : </strong><span class="matchbubble marginL5 empty" title="">0</span> When Imapper can't find a person with a matching email address</li>
			<li><strong>MultiMatch : </strong> <span class="matchbubble marginL5 multi" title="">>1</span> When Imapper finds several people a matching email address</li>
			<li><strong>ProcessError : </strong> <span class="matchbubble marginL5 warn" title="">1</span> When Imapper Couldn't proccess the email and it directly matched a contact</li>
		</ul>
	</div>
	<div id="delete-confirm" title="Delete Message from Unmatched Messages?">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>These items will be removed permanently. Are you sure?</p>
	</div>
	<div id="loading-popup" title="please wait">
		<p> Loading message details.</p>
	</div>
	<div id="reloading-popup" title="please wait" style="display:none;">
		<p> <img src="/sites/default/themes/Bluebird/nyss_skin/images/header-search-active.gif"/>  ReLoading messages.</p>
	</div>
	<div id="no_find_match" title="This Message was already matched" style="display:none;">
		<p> We will automatically assign this message in the next 5 mins.</p>
	</div>
	<div id="matchCheck-popup" title="Checking Other Emails">
		<p> Currently Checking for other emails that match this address.</p>
	</div>
</div>
