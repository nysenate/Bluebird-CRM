<div class="crm-content-block imapperbox" id="Activities">
	<div class='full'>
	<h1>Matched Messages <small id='total_results'><span id="total_number">Loading</span> results</small></h1>
	</div>
	<div id='top'></div>
	<div class='full'>
		<table id="sortable_results" class="">
			<thead>
				<tr class='list_header'>
				    <th class='checkbox' ><input class='checkbox_switch'  type="checkbox" name="" value="" /></th>
			   		<th class='Name'>Sender’s Name</th> 
				    <th class='Email'>Sender’s Address</th> 
				    <th class='Subject'>Subject</th> 
				    <th class='Date'>Date Sent</th> 
				    <th class='Forwarded'>Forwarded By</th> 
				   	<th class='Actions'>Actions</th> 
				</tr>
			</thead>
			<tbody id='imapper-messages-list'>
				<tr><td>Loading Message data</td></tr>
			</tbody>
		</table>
		<div class='page_actions'>
			<input type="button" class="multi_tag" value="Add Tag" name="multi_tag">
			<input type="button" class="multi_clear" value="Clear" name="multi_clear">
			<input type="button" class="multi_delete" value="Delete" name="multi_delete">
		</div>
	</div>
	<div id="find-match-popup" title="Loading Data">
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
					<!-- <li><a href="#tabs-2">Add Contact</a></li> -->
				</ul>
				<div id="tab1">
						<input type="hidden" class="hidden" id="email_id" name="email_id">
						<input type="hidden" class="hidden" id="imap_id" name="imap_id">
						<input type="text" placeholder="First name" class="form-text first_name" name="first_name">
						<input type="text" placeholder="Last Name"  class="form-text last_name" name="last_name">
						<input type="text" placeholder="Email Address" class="email-address email-address" name="email_address">
						<input type="text" placeholder="yyyy-mm-dd" class="form-text dob" name="dob">
						<input type="text" placeholder="Phone Number" class="form-text phone" name="phone">
						<input type="text" placeholder="Street Address"  class="form-text street_address" name="street_address">
						<input type="text" placeholder="City" class="form-text city" name="city">
						<input type="button" class="imapper-submit" id="filter" value="Search" name="filter">
					<div id="imapper-contacts-list" class="contacts-list"></div>
					<input type="button" class="imapper-submit" id="reassign" value="Assign" name="reassign">
				</div>
			</div>
		</div>
	</div>
	<div id="delete-confirm" title="Delete Message from Matched Messages?">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>These items will be removed permanently. Are you sure?</p>
	</div>
	<div id="loading-popup" title="please wait">
 		<p> <img src="/sites/default/themes/Bluebird/nyss_skin/images/header-search-active.gif"/> Loading message details.
     </p>
	</div>
	<div id="tagging-popup" title="Tagging">
		<div id="tagging-popup-header"> </div>
		<hr/>
		<input type='text' id='autocomplete_tag'/><br/>  <hr/>
		<div class="autocomplete-tags-bank" style=""></div>
		<div class="autocomplete-dropdown" style=""></div>
		<hr/><strong>Add to: </strong> <br/> <input type="checkbox" name="Contact" class="Contact-checkbox" value="Contact">Contact<br/><input type="checkbox" class="Activity-checkbox" name="Activity" value="Activity"> Activity<br>
		<input type="button" class="tagger-submit push_tag" id="add-tag" value="Add Tag" name="add-tag">
	</div> 
</div>
