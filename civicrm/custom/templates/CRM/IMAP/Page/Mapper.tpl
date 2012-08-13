<div class="crm-content-block imapperbox">
	<div class='full'>
	<h1>Unmatched Results <small id='total_results'>Loading results</small></h1>
	</div>
	<div class='full'>
		<table id="sortable_results" class=""> 
			<thead> 
				<tr class='list_header'> 
				    <th class='checkbox' ><input type="checkbox" name="" value="" /></th> 
				    <th class='Name'>Name</th> 
				    <th class='Email'>Email</th> 
				    <th class='Subject'>Subject</th> 
				    <th class='Date'>Date</th> 
				   	<th class='Actions'>Actions</th> 
				</tr> 
			</thead> 
			<tbody id='imapper-messages-list' > 
				<tr><td>Loading Message data</td></tr>	
			</tbody> 
		</table> 
	</div>




	<div id="imapper-filter"> &nbsp;
	<!--	<div class="imapper-title">Contact Filters edited</div>
		<form>
			<div id="reset_container" class="imapper-input-container">
				<input type="submit" name="reset" value="Reset"  id="reset" class="imapper-submit"/></div>
    		<div id="first_name_container" class="imapper-input-container">
    			{$form.first_name.label}<br/>{$form.first_name.html}</div>
    		<div id="last_name_container" class="imapper-input-container">
    			{$form.last_name.label}<br/>{$form.last_name.html}</div>
			<div id="phone_container" class="imapper-input-container">
    			{$form.phone.label}<br/>{$form.phone.html}</div>
    		<div id="street_address_container" class="imapper-input-container">
    			{$form.street_address.label}<br/>{$form.street_address.html}</div>
    		<div id="city_name_container" class="imapper-input-container">
    			{$form.city.label}<br/>{$form.city.html}</div>
			<div id="search_container" class="imapper-input-container">
				<input type="submit" name="filter" value="Search" id="filter" class="imapper-submit"/></div>
			<div class="imapper-clear"></div>
		</form> -->
	</div>
	<div id="imapper-contacts"> <!--
		<div class="imapper-title"><h2>Contacts</h2></div>
		<div id="imapper-contacts-list"></div> -->
	</div>
	<div id="imapper-messages"><!--
		<div class="imapper-title">Messages</div>
    	{foreach from=$messages item=message}
        	<div class="imapper-message" id="imapper_message_{$message->uid}">
        		<div class="imapper-header">
            		<span class="imapper-message-toggle"></span>{$message->subject}, {$message->to}, {$message->from},
        		</div>
        		<div class="imapper-body"></div>
        	</div>
        {/foreach} -->
	</div>
	<div class="imapper-clear"></div>
</div>