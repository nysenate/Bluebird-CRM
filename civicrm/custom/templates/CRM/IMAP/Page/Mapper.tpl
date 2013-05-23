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
				    <th class='Name'>Sender’s Info</th>
 				    <th class='Subject'>Subject</th>
				    <th class='Date'>Date Forwarded</th>
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
			<input type="button" class="multi_delete" id="" value="Delete Selected" name="delete">
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
				<input type="hidden" class="hidden" id="id" name="id">
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
					<label for="state">
						<span class="label_def">State: </span>
						<select class="form-select state" id="state" name="state">
							<option value="">- select -</option>
							<option value="1000">Alabama</option>
							<option value="1001">Alaska</option>
							<option value="1052">American Samoa</option>
							<option value="1002">Arizona</option>
							<option value="1003">Arkansas</option>
							<option value="1060">Armed Forces Americas</option>
							<option value="1059">Armed Forces Europe</option>
							<option value="1061">Armed Forces Pacific</option>
							<option value="1004">California</option>
							<option value="1005">Colorado</option>
							<option value="1006">Connecticut</option>
							<option value="1007">Delaware</option>
							<option value="1050">District of Columbia</option>
							<option value="1008">Florida</option>
							<option value="1009">Georgia</option>
							<option value="1053">Guam</option>
							<option value="1010">Hawaii</option>
							<option value="1011">Idaho</option>
							<option value="1012">Illinois</option>
							<option value="1013">Indiana</option>
							<option value="1014">Iowa</option>
							<option value="1015">Kansas</option>
							<option value="1016">Kentucky</option>
							<option value="1017">Louisiana</option>
							<option value="1018">Maine</option>
							<option value="1019">Maryland</option>
							<option value="1020">Massachusetts</option>
							<option value="1021">Michigan</option>
							<option value="1022">Minnesota</option>
							<option value="1023">Mississippi</option>
							<option value="1024">Missouri</option>
							<option value="1025">Montana</option>
							<option value="1026">Nebraska</option>
							<option value="1027">Nevada</option>
							<option value="1028">New Hampshire</option>
							<option value="1029">New Jersey</option>
							<option value="1030">New Mexico</option>
							<option value="1031">New York</option>
							<option value="1032">North Carolina</option>
							<option value="1033">North Dakota</option>
							<option value="1055">Northern Mariana Islands</option>
							<option value="1034">Ohio</option>
							<option value="1035">Oklahoma</option>
							<option value="1036">Oregon</option>
							<option value="1037">Pennsylvania</option>
							<option value="1056">Puerto Rico</option>
							<option value="1038">Rhode Island</option>
							<option value="1039">South Carolina</option>
							<option value="1040">South Dakota</option>
							<option value="1041">Tennessee</option>
							<option value="1042">Texas</option>
							<option value="1058">United States Minor Outlying Islands</option>
							<option value="1043">Utah</option>
							<option value="1044">Vermont</option>
							<option value="1057">Virgin Islands</option>
							<option value="1045">Virginia</option>
							<option value="1046">Washington</option>
							<option value="1047">West Virginia</option>
							<option value="1048">Wisconsin</option>
							<option value="1049">Wyoming</option>
						</select>
					</label>	
					<input type="button" class="imapper-submit" id="filter" value="Search" name="filter">
					<div id="imapper-contacts-list" class="contacts-list"> </div>
					<input type="button" class="imapper-submit" id="preAssign" value="Assign" name="Assign">
					<input type="button" class="hidden" id="assign" value="Assign" name="Assign">
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
					<label for="state">
						<span class="label_def">State: </span>
						<select class="form-select state" id="state" name="state">
							<option value="">- select -</option>
							<option value="1000">Alabama</option>
							<option value="1001">Alaska</option>
							<option value="1052">American Samoa</option>
							<option value="1002">Arizona</option>
							<option value="1003">Arkansas</option>
							<option value="1060">Armed Forces Americas</option>
							<option value="1059">Armed Forces Europe</option>
							<option value="1061">Armed Forces Pacific</option>
							<option value="1004">California</option>
							<option value="1005">Colorado</option>
							<option value="1006">Connecticut</option>
							<option value="1007">Delaware</option>
							<option value="1050">District of Columbia</option>
							<option value="1008">Florida</option>
							<option value="1009">Georgia</option>
							<option value="1053">Guam</option>
							<option value="1010">Hawaii</option>
							<option value="1011">Idaho</option>
							<option value="1012">Illinois</option>
							<option value="1013">Indiana</option>
							<option value="1014">Iowa</option>
							<option value="1015">Kansas</option>
							<option value="1016">Kentucky</option>
							<option value="1017">Louisiana</option>
							<option value="1018">Maine</option>
							<option value="1019">Maryland</option>
							<option value="1020">Massachusetts</option>
							<option value="1021">Michigan</option>
							<option value="1022">Minnesota</option>
							<option value="1023">Mississippi</option>
							<option value="1024">Missouri</option>
							<option value="1025">Montana</option>
							<option value="1026">Nebraska</option>
							<option value="1027">Nevada</option>
							<option value="1028">New Hampshire</option>
							<option value="1029">New Jersey</option>
							<option value="1030">New Mexico</option>
							<option value="1031">New York</option>
							<option value="1032">North Carolina</option>
							<option value="1033">North Dakota</option>
							<option value="1055">Northern Mariana Islands</option>
							<option value="1034">Ohio</option>
							<option value="1035">Oklahoma</option>
							<option value="1036">Oregon</option>
							<option value="1037">Pennsylvania</option>
							<option value="1056">Puerto Rico</option>
							<option value="1038">Rhode Island</option>
							<option value="1039">South Carolina</option>
							<option value="1040">South Dakota</option>
							<option value="1041">Tennessee</option>
							<option value="1042">Texas</option>
							<option value="1058">United States Minor Outlying Islands</option>
							<option value="1043">Utah</option>
							<option value="1044">Vermont</option>
							<option value="1057">Virgin Islands</option>
							<option value="1045">Virginia</option>
							<option value="1046">Washington</option>
							<option value="1047">West Virginia</option>
							<option value="1048">Wisconsin</option>
							<option value="1049">Wyoming</option>
						</select>
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
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Selected messages will be removed permanently. Are you sure?</p>
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
	<div id="matchCheck-popup" title="Checking Other Emails"  style="display:none;">
		<p> Currently Checking for other emails that match <span class="this_address">this address</span>.</p>
	</div>
	<div id="fileBug-popup" title="We're here to help"  style="display:none;">
		<p>Step #1. Please explain your problem in the text box and click "Report Problem".</p>
		<p>Step #2. Please contact the support line at x2011 </p>
		<textarea rows="4" name="description" id="description"></textarea>
	</div>
	<div id="AdditionalEmail-popup" title="Add email address to contact?"  style="display:none;">
		<p>We found the following email address. Do you want to add it to the contact's records? (You can also edit this email if needed)</p>
		<input type="text" class="add_email"  id="add_email" name="add_email">
		<input type="hidden" class="hidden" id="contacts" name="contacts">
		<input type="hidden" class="hidden" id="id" name="id">
	</div>
</div>
