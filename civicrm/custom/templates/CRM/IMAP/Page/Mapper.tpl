<div class="crm-content-block imapperbox " id="Unmatched">
	<div class='full'>
	<h1>Unmatched Messages <select class="form-select range" id="range" name="range">
				<option value="0">All Time</option>
				<option value="1">Last 24 hours</option>
				<option value="7">Last 7 days</option>
				<option value="30" selected>Last 30 days</option>
				<option value="90">Last 90 days</option>
				<option value="365">Last Year</option>
			</select></h1>
	</div>
	<div id='top'></div>
	<div class='full'>
		<div class='page_actions'>
			<input type="button" class="multi_delete" id="" value="Delete" name="delete">
			<span class="page_actions_label">With selected :</span>
		</div>
		<table id="sortable_results" class="">
			<thead>
				<tr class='list_header'>
					<th class='imap_checkbox_header' ><input class='checkbox_switch'  type="checkbox" name="" value="" /></th>
					<th class='imap_name_header'>Sender’s Info</th>
					<th class='imap_subject_header'>Subject</th>
					<th class='imap_date_header'>Date Forwarded</th>
					<th class='imap_match_type_header hidden'>Match Type</th>
					<th class='imap_forwarded_header'>Forwarded By</th>
					<th class='imap_action_headers'>Actions</th>
				</tr>
			</thead>
			<tbody id='imapper-messages-list'>
				<td valign="top" colspan="7" class="dataTables_empty"><span class="loading_row"><span class="loading_message">Loading Message data <img src="/sites/default/themes/Bluebird/images/loading.gif"/></span></span></td>
			</tbody>
		</table>
		<div class='page_actions'>
			<input type="button" class="multi_delete" id="" value="Delete" name="delete">
			<span class="page_actions_label">With selected :</span>
		</div>
	</div>
	<div id="assign-popup" title="Loading Data" style="display:none;">
		<div id="message_left">
			<div id="message_left_header">
			</div>
			<div id="message_left_email">
			</div>
		</div>
		<div id="message_right">
			<div id="tabs" class="subtab">
				<ul>
					<li><a href="#tab1" data-button="Assign" >Find Contact</a></li>
					<li><a href="#tab2" data-button="Create &amp; Assign">Add Contact</a></li>
				</ul>
				<!-- Object Id's -->
				<input type="hidden" class="hidden" id="message">
				<!-- End objects -->

				<div id="tab1">
          <input type="hidden" class="hidden" id="id" name="id">
          <label for="first_name">
            <span class="label_def">First Name: </span>
            <input type="text" placeholder="First Name" class="form-text first_name" name="first_name">
          </label>
          <label for="last_name">
            <span class="label_def">Last Name: </span>
            <input type="text" placeholder="Last Name" class="form-text last_name" name="last_name">
          </label>
          <label for="email_address">
            <span class="label_def">Email: </span>
            <input type="text" placeholder="Email Address" class="email-address email_address" name="email_address">
          </label>
          <label for="dob" class="dob">
            <span class="label_def">DOB: </span>
            <select name="DateOfBirth_Month" class="month" >
              <option> </option>
              {php}
                for ($months=1; $months <= 12; $months++) {
                  echo '<option value="'.$months.'">'.date("F",  strtotime($months.'/14/2000')).'</option>';
                }
              {/php}
            </select>
            <select name="DateOfBirth_Day" class="day">
              <option> </option>
              {php}
                for ($days=0; $days <= 31; $days++) {
                  echo '<option value="'.$days.'">'.$days.'</option>';
                }
              {/php}
            </select>
            <select name="DateOfBirth_Year" class="year">
              <option> </option>
              {php}
                for ($years=0; $years <= 120; $years++) {
                  $date = date("Y", strtotime(date("Y").' -'.$years.' year'));
                  echo '<option value="'.$date.'">'.$date.'</option>';
                }
              {/php}
            </select>
            <input type="text" placeholder="yyyy-mm-dd" class="form-text dob hidden" name="dob">
          </label>
          <label for="phone">
            <span class="label_def">Phone #: </span>
            <input type="text" placeholder="Phone Number" class="form-text phone" name="phone">
          </label>
          <label for="street_address">
            <span class="label_def">St. Address: </span>
            <input type="text" placeholder="Street Address" class="form-text street_address" name="street_address">
          </label>
          <label for="city">
            <span class="label_def">City: </span>
            <input type="text" placeholder="City" class="form-text city" name="city">
          </label>
          <label for="state">
            <span class="label_def">State: </span>
            <select class="form-select state" id="state" name="state">
              <option value=""> </option>
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
            <div class='search'>
              <input type="button" class="imapper-submit" id="search" value="Search" name="search">
	            <span class='right'></span>
            </div>
          <div id="imapper-contacts-list" class="contacts-list"></div>
        </div>
        <div id="tab2">
          <label for="prefix">
            <span class="label_def">Prefix: </span>
              <select class="form-select prefix" id="prefix" name="prefix">
                <option> </option>
                {php}
                  $prefixes = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'prefix_id');
                  foreach ($prefixes as $key => $value) {
                    echo '<option value="'.$key.'">'.$value.'</option>';
                  }
                {/php}
              </select>
            </label>
          </label>
          <label for="first_name">
            <span class="label_def">First Name: </span>
            <input type="text" placeholder="First Name" class="form-text first_name" name="first_name">
          </label>
          <label for="middle_name">
            <span class="label_def">Middle Name: </span>
            <input type="text" placeholder="Middle Name" class="form-text middle_name" name="middle_name">
          </label>
          <label for="last_name">
            <span class="label_def">Last Name: </span>
            <input type="text" placeholder="Last Name"  class="form-text last_name" name="last_name">
          </label>
          <label for="suffix">
            <span class="label_def">Suffix: </span>
            <select class="form-select suffix" id="suffix" name="suffix">
                <option> </option>
                {php}
                  $prefixes = CRM_Core_PseudoConstant::get('CRM_Contact_DAO_Contact', 'suffix_id');
                  foreach ($prefixes as $key => $value) {
                    echo '<option value="'.$key.'">'.$value.'</option>';
                  }
                {/php}
            </select>
          </label>
          <label for="email_address">
            <span class="label_def">Email: </span>
            <input type="text" placeholder="Email Address" class="email-address email_address" name="email_address">
          </label>
          <label for="dob" class="dob">
            <span class="label_def">DOB: </span>
            <select name="DateOfBirth_Month" class="month" >
              <option> </option>
              {php}
                for ($months=1; $months <= 12; $months++) {
                  echo '<option value="'.$months.'">'.date("F",  strtotime($months.'/14/2000')).'</option>';
                }
              {/php}
            </select>
            <select name="DateOfBirth_Day" class="day">
              <option> </option>
              {php}
                for ($days=0; $days <= 31; $days++) {
                  echo '<option value="'.$days.'">'.$days.'</option>';
                }
              {/php}
            </select>
            <select name="DateOfBirth_Year" class="year">
              <option> </option>
              {php}
                for ($years=0; $years <= 120; $years++) {
                  $date = date("Y", strtotime(date("Y").' -'.$years.' year'));
                  echo '<option value="'.$date.'">'.$date.'</option>';
                }
              {/php}
            </select>
            <input type="text" placeholder="yyyy-mm-dd" class="form-text dob hidden" name="dob">
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
              <option value=""> </option>
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
        </div>
 			</div>
		</div>
	</div>
	<div id="delete-confirm" title="Delete Message from Unmatched Messages?">
		<!-- Object Id's -->
		<input type="hidden" class="hidden" id="message">
		<!-- End objects -->
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

	<div id="AdditionalEmail-popup" title="Add email address to contact?"  style="display:none;">
		<p>We found the following email address. Do you want to add it to the contact's records? (You can also edit this email if needed)</p>
		<div class="add_email"  id="add_email">

		</div>
		<input type="hidden" class="hidden" id="contacts" name="contacts">
		<input type="hidden" class="hidden" id="id" name="id">
	</div>
</div>
