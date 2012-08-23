<div class="crm-content-block imapperbox" id="Activities">
	<div class='full'>
	<h1>Matched Messages <small id='total_results'><span id="total_number">Loading</span> Activities</small></h1>
	</div>
	<div class='full'>
		<table id="sortable_results" class=""> 
			<thead> 
				<tr class='list_header'> 
				    <th class='checkbox' ><input class='checkbox_switch'  type="checkbox" name="" value="" /></th> 
				    <th class='Name'>Name</th> 
				    <th class='Email'>Email</th> 
				    <th class='Forwarded'>Forwarded by</th> 
				    <th class='Subject'>Subject</th> 
				    <th class='Date'>Date</th> 
				   	<th class='Actions'>Actions</th> 
				</tr> 
			</thead> 
			<tbody id='imapper-messages-list' > 
				<tr><td>Loading Message data</td></tr>	
			</tbody> 
		</table> 
		<hr/>
		<div class='page_actions'>
			<input type="button" class="multi_tag" id="add-contact" value="Add Tag" name="add-contact"> | 
			<input type="button" class="multi_clear" id="add-contact" value="Clear" name="add-contact"> | 
			<input type="button" class="multi_delete" id="delete" value="Delete" name="delete">

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
					<li><a href="#tabs-1">Find Contact</a></li>
					<li><a href="#tabs-2">Add Contact</a></li>
				</ul>
				<div id="tabs-1">
					<form onsbumit="return false;">
						<input type="hidden" class="hidden" id="email_id" name="email_id">
						<input type="hidden" class="hidden" id="imap_id" name="imap_id">

						<input type="text" placeholder="First name" class="form-text" id="first_name" name="first_name">
						<input type="text" placeholder="Last Name"  class="form-text" id="last_name" name="last_name">
						<input type="text" placeholder="Email Address" class="email-address" id="email-address" name="email_address">

						<input type="text" placeholder="Phone Number" class="form-text" id="phone" name="phone">
						<input type="text" placeholder="Street Address"  class="form-text" id="street_address" name="street_address">
						<input type="text" placeholder="City" class="form-text" id="city" name="city">
						<input type="button" class="imapper-submit" id="filter" value="Search" name="filter">

				    </form>
					<div id="imapper-contacts-list" class="contacts-list">
					</div>
					<input type="button" class="imapper-submit" id="assign" value="Assign" name="Assign">
				</div>
				<div id="tabs-2">
					<form onsbumit="return false;">
						<input type="hidden" class="hidden" id="email_id" name="email_id">
						<input type="hidden" class="hidden" id="imap_id" name="imap_id">

						<input type="text" placeholder="First name" class="form-text" id="first_name" name="first_name">
						<input type="text" placeholder="Last Name"  class="form-text" id="last_name" name="last_name">
						<input type="text" placeholder="Email Address" class="email-address" id="email-address" name="email_address">

						<input type="text" placeholder="Phone Number" class="form-text" id="phone" name="phone">
						<input type="text" placeholder="Street Address"  class="form-text" id="street_address" name="street_address">
						<input type="text" placeholder="City" class="form-text" id="city" name="city">
						<input type="button" class="imapper-submit" id="add-contact" value="Add Contact" name="add-contact">
				    </form>
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
	<div id="tagging-popup" title="Tagging">
		<p> Loading message details.</p>
	</div> 

</div>
