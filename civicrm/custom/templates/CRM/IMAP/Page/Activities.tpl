<div class="crm-content-block imapperbox" id="Activities">
  <div class='full'>
  <h1>Matched Messages  <select class="form-select range" id="range" name="range">
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
      <input type="button" class="multi_delete" value="Delete" name="multi_delete">
      <input type="button" class="multi_clear" value="Clear" name="multi_clear">
      <!-- <input type="button" class="multi_process" value="Process" name="multi_process"> -->
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
      <input type="button" class="multi_delete" value="Delete" name="multi_delete">
      <input type="button" class="multi_clear" value="Clear" name="multi_clear">
      <!-- <input type="button" class="multi_process" value="Process" name="multi_process"> -->
      <span class="page_actions_label">With selected :</span>
    </div>
  </div>

  <div id="process-popup" title="Loading Data"  style="display:none;">
     <!-- Object Id's -->
    <input type="hidden" class="hidden" id="message">
    <input type="hidden" class="hidden" id="activity">
    <input type="hidden" class="hidden" id="contact">
    <!-- End objects -->

    <!-- tagging id's -->
    <input type="hidden" class="hidden" id="contact_tag">
    <input type="hidden" class="hidden" id="contact_position">
    <input type="hidden" class="hidden" id="activity_tag">
    <!-- End new -->

    <!-- edit activity -->
    <input type="hidden" class="hidden" id="contact_name">
    <!-- End new -->

    <div id="message_left">
      <div id="message_left_header"></div>
      <div id="message_left_email"></div>
    </div>
    <div id="message_right">
      <div id="tabs">
        <ul>
          <li><a href="#tab1">Edit Match </a></li>
          <li><a href="#tab2">Tag</a></li>
          <li><a href="#tab3">Edit Activity</a></li>
        </ul>
        <div id="tab1">
          <div id="tabs_edit" class="subtab">
            <ul>
              <li><a href="#tab1_edit" data-button="Assign" >Find Contact</a></li>
							<li><a href="#tab2_edit" data-button="Create &amp; Assign">Add Contact</a></li>
            </ul>

            <div id="tab1_edit">
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
	          <div id="tab2_edit">
	            <label for="prefix">
	              <span class="label_def">Prefix: </span>
	                <select class="form-select prefix" id="prefix" name="prefix">
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

        <div id="tab2">
          <div id="tabs_tag" class="subtab">
            <ul>
              <li><a href="#tab1_tag">Tag Contact</a></li>
              <li><a href="#tab2_tag">Tag Activity</a></li>
            </ul>

            <div id="tab1_tag">
              <div id='TagContact'>
                <span class="label_def">Keywords: </span>
                <input type="text" id="contact_keyword_input" maxlength="64" placeholder="Type here to search Keywords" autocomplete="off">

                <span class="label_def">Issue Codes: </span>
                <input type="text" id="contact-issue-codes-search" maxlength="64" placeholder="Type here to search issue codes" autocomplete="off">
                <div id="contact-issue-codes" class="TreeWrap">
                </div>

                <span class="label_def">Positions: </span>
                <input type="text" id="contact_position_input" maxlength="64" placeholder="Type here to search Keywords" autocomplete="off">

              </div>
            </div>
            <div id="tab2_tag">
              <div id='TagActivity'>
                <span class="label_def">Keywords: </span>
                <input type="text" id="activity_keyword_input" placeholder="Type here to search Keywords" autocomplete="off">
              </div>
            </div>
          </div>
        </div>

        <div id="tab3">
          <div id='TagContact'>

            <label for="contact_name_input">
	            <span class="label_def">Assign to: </span>
	            <input type="text" id="contact_name_input" maxlength="64" placeholder="Type here to search Contacts" autocomplete="off">
            </label>

            <label for="status_id">
	            <span class="label_def">Status: </span>
							<select class="form-select required" id="status_id" name="status_id">
								<option value="1">Scheduled</option>
								<option selected="selected" value="2">Completed</option>
								<option value="3">Cancelled</option>
								<option value="4">Left Message</option>
								<option value="5">Unreachable</option>
								<option value="6">Not Required</option>
								<option value="7">Draft</option>
								<option value="8">Available</option>
								<option value="9">No-show</option>
							</select>
            </label>

            <label for="date">
              <span class="label_def">Date: </span>
              <input type="text" placeholder="Date" class="form-text date" id="activity_date" name="activity_date">
            </label>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div id="delete-confirm" title="Delete Message from Matched Messages?" style="display:none;">
		<!-- Object Id's -->
		<input type="hidden" class="hidden" id="message">
		<!-- End objects -->
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Selected messages will be removed permanently. Are you sure?</p>
  </div>
  <div id="clear-confirm" title="Remove Message from From List?" style="display:none;">
  	<!-- Object Id's -->
		<input type="hidden" class="hidden" id="message">
		<!-- End objects -->
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Selected messages will be removed from this list. They will not be deleted from Bluebird.</p>
  </div>
  <div id="loading-popup" title="please wait" style="display:none;">
    <p> <img src="/sites/default/themes/Bluebird/nyss_skin/images/header-search-active.gif"/> Loading message details.</p>
  </div>
  <div id="reloading-popup" title="please wait" style="display:none;">
    <p> <img src="/sites/default/themes/Bluebird/nyss_skin/images/header-search-active.gif"/>  ReLoading messages.</p>
  </div>


</div>
