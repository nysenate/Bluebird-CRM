<div class="crm-content-block imapperbox" id="Activities">
	<div class='full'>
	<h1>Matched Messages  <select class="form-select range" id="range" name="range">
				<option value="0">All Time</option>
				<option value="1">Last 24 hours</option>
				<option value="7">Last 7 days</option>
				<option value="30">Last 30 days</option>
				<option value="90">Last 90 days</option>
				<option value="365">Last Year</option>
			</select></h1>
	</div>
	<div id='top'></div>
	<div class='full'>
		<div class='page_actions'>
			<input type="button" class="multi_tag" value="Tag " name="multi_tag">
			<input type="button" class="multi_clear" value="Remove From List" name="multi_clear">
			<span class="page_actions_label">With selected :</span>
		</div>
		<table id="sortable_results" class="">
			<thead>
				<tr class='list_header'>
					<th class='imap_checkbox_header checkbox' ><input class='checkbox_switch'  type="checkbox" name="" value="" /></th>
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
			<input type="button" class="multi_tag" value="Tag " name="multi_tag">
			<input type="button" class="multi_clear" value="Remove From List" name="multi_clear">
			<span class="page_actions_label">With selected :</span>
		</div>
	</div>
	<div id="find-match-popup" title="Loading Data"  style="display:none;">
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
						<select name="DateOfBirth_Month" class="month" >
							<option>-</option>
							<option value="1">Jan</option>
							<option value="2">Feb</option>
							<option value="3">Mar</option>
							<option value="4">Apr</option>
							<option value="5">May</option>
							<option value="6">June</option>
							<option value="7">July</option>
							<option value="8">Aug</option>
							<option value="9">Sept</option>
							<option value="10">Oct</option>
							<option value="11">Nov</option>
							<option value="12">Dec</option>
						</select>
						<select name="DateOfBirth_Day" class="day">
							<option>-</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
							<option value="13">13</option>
							<option value="14">14</option>
							<option value="15">15</option>
							<option value="16">16</option>
							<option value="17">17</option>
							<option value="18">18</option>
							<option value="19">19</option>
							<option value="20">20</option>
							<option value="21">21</option>
							<option value="22">22</option>
							<option value="23">23</option>
							<option value="24">24</option>
							<option value="25">25</option>
							<option value="26">26</option>
							<option value="27">27</option>
							<option value="28">28</option>
							<option value="29">29</option>
							<option value="30">30</option>
							<option value="31">31</option>
						</select>
						<select name="DateOfBirth_Year" class="year">
							<option>-</option>
							<option value="2014">2014</option>
							<option value="2013">2013</option>
							<option value="2012">2012</option>
							<option value="2011">2011</option>
							<option value="2010">2010</option>
							<option value="2009">2009</option>
							<option value="2008">2008</option>
							<option value="2007">2007</option>
							<option value="2006">2006</option>
							<option value="2005">2005</option>
							<option value="2004">2004</option>
							<option value="2003">2003</option>
							<option value="2002">2002</option>
							<option value="2001">2001</option>
							<option value="2000">2000</option>
							<option value="1999">1999</option>
							<option value="1998">1998</option>
							<option value="1997">1997</option>
							<option value="1996">1996</option>
							<option value="1995">1995</option>
							<option value="1994">1994</option>
							<option value="1993">1993</option>
							<option value="1992">1992</option>
							<option value="1991">1991</option>
							<option value="1990">1990</option>
							<option value="1989">1989</option>
							<option value="1988">1988</option>
							<option value="1987">1987</option>
							<option value="1986">1986</option>
							<option value="1985">1985</option>
							<option value="1984">1984</option>
							<option value="1983">1983</option>
							<option value="1982">1982</option>
							<option value="1981">1981</option>
							<option value="1980">1980</option>
							<option value="1979">1979</option>
							<option value="1978">1978</option>
							<option value="1977">1977</option>
							<option value="1976">1976</option>
							<option value="1975">1975</option>
							<option value="1974">1974</option>
							<option value="1973">1973</option>
							<option value="1972">1972</option>
							<option value="1971">1971</option>
							<option value="1970">1970</option>
							<option value="1969">1969</option>
							<option value="1968">1968</option>
							<option value="1967">1967</option>
							<option value="1966">1966</option>
							<option value="1965">1965</option>
							<option value="1964">1964</option>
							<option value="1963">1963</option>
							<option value="1962">1962</option>
							<option value="1961">1961</option>
							<option value="1960">1960</option>
							<option value="1959">1959</option>
							<option value="1958">1958</option>
							<option value="1957">1957</option>
							<option value="1956">1956</option>
							<option value="1955">1955</option>
							<option value="1954">1954</option>
							<option value="1953">1953</option>
							<option value="1952">1952</option>
							<option value="1951">1951</option>
							<option value="1950">1950</option>
							<option value="1949">1949</option>
							<option value="1948">1948</option>
							<option value="1947">1947</option>
							<option value="1946">1946</option>
							<option value="1945">1945</option>
							<option value="1944">1944</option>
							<option value="1943">1943</option>
							<option value="1942">1942</option>
							<option value="1941">1941</option>
							<option value="1940">1940</option>
							<option value="1939">1939</option>
							<option value="1938">1938</option>
							<option value="1937">1937</option>
							<option value="1936">1936</option>
							<option value="1935">1935</option>
							<option value="1934">1934</option>
							<option value="1933">1933</option>
							<option value="1932">1932</option>
							<option value="1931">1931</option>
							<option value="1930">1930</option>
							<option value="1929">1929</option>
							<option value="1928">1928</option>
							<option value="1927">1927</option>
							<option value="1926">1926</option>
							<option value="1925">1925</option>
							<option value="1924">1924</option>
							<option value="1923">1923</option>
							<option value="1922">1922</option>
							<option value="1921">1921</option>
							<option value="1920">1920</option>
							<option value="1919">1919</option>
							<option value="1918">1918</option>
							<option value="1917">1917</option>
							<option value="1916">1916</option>
							<option value="1915">1915</option>
							<option value="1914">1914</option>
							<option value="1913">1913</option>
							<option value="1912">1912</option>
							<option value="1911">1911</option>
							<option value="1910">1910</option>
							<option value="1909">1909</option>
							<option value="1908">1908</option>
							<option value="1907">1907</option>
							<option value="1906">1906</option>
							<option value="1905">1905</option>
							<option value="1904">1904</option>
							<option value="1903">1903</option>
							<option value="1902">1902</option>
							<option value="1901">1901</option>
							<option value="1900">1900</option>
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
					<div id="imapper-contacts-list" class="contacts-list"></div>
					<input type="button" class="imapper-submit" id="reassign" value="Reassign" name="reassign">
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
					<label for="dob" class="dob">
						<span class="label_def">DOB: </span>
						<select name="DateOfBirth_Month" class="month" >
							<option>-</option>
							<option value="1">Jan</option>
							<option value="2">Feb</option>
							<option value="3">Mar</option>
							<option value="4">Apr</option>
							<option value="5">May</option>
							<option value="6">June</option>
							<option value="7">July</option>
							<option value="8">Aug</option>
							<option value="9">Sept</option>
							<option value="10">Oct</option>
							<option value="11">Nov</option>
							<option value="12">Dec</option>
						</select>
						<select name="DateOfBirth_Day" class="day">
							<option>-</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
							<option value="13">13</option>
							<option value="14">14</option>
							<option value="15">15</option>
							<option value="16">16</option>
							<option value="17">17</option>
							<option value="18">18</option>
							<option value="19">19</option>
							<option value="20">20</option>
							<option value="21">21</option>
							<option value="22">22</option>
							<option value="23">23</option>
							<option value="24">24</option>
							<option value="25">25</option>
							<option value="26">26</option>
							<option value="27">27</option>
							<option value="28">28</option>
							<option value="29">29</option>
							<option value="30">30</option>
							<option value="31">31</option>
						</select>
						<select name="DateOfBirth_Year" class="year">
							<option>-</option>
							<option value="2014">2014</option>
							<option value="2013">2013</option>
							<option value="2012">2012</option>
							<option value="2011">2011</option>
							<option value="2010">2010</option>
							<option value="2009">2009</option>
							<option value="2008">2008</option>
							<option value="2007">2007</option>
							<option value="2006">2006</option>
							<option value="2005">2005</option>
							<option value="2004">2004</option>
							<option value="2003">2003</option>
							<option value="2002">2002</option>
							<option value="2001">2001</option>
							<option value="2000">2000</option>
							<option value="1999">1999</option>
							<option value="1998">1998</option>
							<option value="1997">1997</option>
							<option value="1996">1996</option>
							<option value="1995">1995</option>
							<option value="1994">1994</option>
							<option value="1993">1993</option>
							<option value="1992">1992</option>
							<option value="1991">1991</option>
							<option value="1990">1990</option>
							<option value="1989">1989</option>
							<option value="1988">1988</option>
							<option value="1987">1987</option>
							<option value="1986">1986</option>
							<option value="1985">1985</option>
							<option value="1984">1984</option>
							<option value="1983">1983</option>
							<option value="1982">1982</option>
							<option value="1981">1981</option>
							<option value="1980">1980</option>
							<option value="1979">1979</option>
							<option value="1978">1978</option>
							<option value="1977">1977</option>
							<option value="1976">1976</option>
							<option value="1975">1975</option>
							<option value="1974">1974</option>
							<option value="1973">1973</option>
							<option value="1972">1972</option>
							<option value="1971">1971</option>
							<option value="1970">1970</option>
							<option value="1969">1969</option>
							<option value="1968">1968</option>
							<option value="1967">1967</option>
							<option value="1966">1966</option>
							<option value="1965">1965</option>
							<option value="1964">1964</option>
							<option value="1963">1963</option>
							<option value="1962">1962</option>
							<option value="1961">1961</option>
							<option value="1960">1960</option>
							<option value="1959">1959</option>
							<option value="1958">1958</option>
							<option value="1957">1957</option>
							<option value="1956">1956</option>
							<option value="1955">1955</option>
							<option value="1954">1954</option>
							<option value="1953">1953</option>
							<option value="1952">1952</option>
							<option value="1951">1951</option>
							<option value="1950">1950</option>
							<option value="1949">1949</option>
							<option value="1948">1948</option>
							<option value="1947">1947</option>
							<option value="1946">1946</option>
							<option value="1945">1945</option>
							<option value="1944">1944</option>
							<option value="1943">1943</option>
							<option value="1942">1942</option>
							<option value="1941">1941</option>
							<option value="1940">1940</option>
							<option value="1939">1939</option>
							<option value="1938">1938</option>
							<option value="1937">1937</option>
							<option value="1936">1936</option>
							<option value="1935">1935</option>
							<option value="1934">1934</option>
							<option value="1933">1933</option>
							<option value="1932">1932</option>
							<option value="1931">1931</option>
							<option value="1930">1930</option>
							<option value="1929">1929</option>
							<option value="1928">1928</option>
							<option value="1927">1927</option>
							<option value="1926">1926</option>
							<option value="1925">1925</option>
							<option value="1924">1924</option>
							<option value="1923">1923</option>
							<option value="1922">1922</option>
							<option value="1921">1921</option>
							<option value="1920">1920</option>
							<option value="1919">1919</option>
							<option value="1918">1918</option>
							<option value="1917">1917</option>
							<option value="1916">1916</option>
							<option value="1915">1915</option>
							<option value="1914">1914</option>
							<option value="1913">1913</option>
							<option value="1912">1912</option>
							<option value="1911">1911</option>
							<option value="1910">1910</option>
							<option value="1909">1909</option>
							<option value="1908">1908</option>
							<option value="1907">1907</option>
							<option value="1906">1906</option>
							<option value="1905">1905</option>
							<option value="1904">1904</option>
							<option value="1903">1903</option>
							<option value="1902">1902</option>
							<option value="1901">1901</option>
							<option value="1900">1900</option>
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
					<input type="button" class="imapper-submit" id="add-contact-reassign" value="Reassign to New Contact" name="add-contact-reassign">
				</div>
			</div>
		</div>
	</div>
	<div id="help-popup" title="Search Help" style="display:none;">
		<h3>Hidden Columns</h3>
		<ul>
			<li><strong>ManuallyMatched : </strong> <span class="matchbubble marginL5 A" title="This email was manually matched">M</span> When an Matched message was manually matched by a user</li>
			<li><strong>AutomaticallyMatched : </strong> <span class="matchbubble marginL5 H" title="This email was automatically matched">H</span> When an Matched message was automatically matched by imapper </li>
		</ul>
	</div>
	<div id="delete-confirm" title="Delete Message from Matched Messages?" style="display:none;">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Selected messages will be removed permanently. Are you sure?</p>
	</div>
	<div id="clear-confirm" title="Remove Message from From List?" style="display:none;">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Selected messages will be removed from this list. They will remain matched to the assigned Sender, and will not be deleted from Bluebird. Proceed?</p>
	</div>
	<div id="loading-popup" title="please wait" style="display:none;">
		<p> <img src="/sites/default/themes/Bluebird/nyss_skin/images/header-search-active.gif"/> Loading message details.</p>
	</div>
	<div id="reloading-popup" title="please wait" style="display:none;">
		<p> <img src="/sites/default/themes/Bluebird/nyss_skin/images/header-search-active.gif"/>  ReLoading messages.</p>
	</div>
	<div id="fileBug-popup" title="We're here to help"  style="display:none;">
		<p>Step #1. Please explain your problem in the text box and click "Report Problem".</p>
		<p>Step #2. Please contact the support line at x2011</p>
		<textarea rows="4" name="description" id="description"></textarea>
	</div>
	<div id="tagging-popup" title="Tagging" style="display:none;">
		<div id="message_left_tag">
			<div id="message_left_header_tag"> over
			</div>
			<div id="message_left_email_tag">
			</div>
		</div>
		<div id="message_right_tag">
			<div id="tabs_tag">
				<ul>
					<li><a href="#tab1_tag">Tag Contact</a></li>
					<li><a href="#tab2_tag">Tag Activity</a></li>
				</ul>
				<input type="hidden" class="hidden" id="contact_tag_ids" name="contact_tag_ids">
				<input type="hidden" class="hidden" id="contact_ids" name="contact_ids">
				<input type="hidden" class="hidden" id="activity_tag_ids" name="activity_tag_ids">
				<input type="hidden" class="hidden" id="activity_ids" name="activity_ids">
				<div id="tab1_tag">
					<div id='TagContact'>
						<input type="text" id="contact_tag_name"/>
					</div>
				</div>
				<div id="tab2_tag">
					<div id='TagActivity'>
						<input type="text" id="activity_tag_name"/>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
