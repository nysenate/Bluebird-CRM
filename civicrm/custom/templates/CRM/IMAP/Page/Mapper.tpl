<div class="crm-content-block">
	<div id="imapper-filter">
		<div class="imapper-title">Contact Filters</div>
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
				<input type="submit" name="filter" value="Filter" id="filter" class="imapper-submit"/></div>
			<div class="imapper-clear"></div>
		</form>
	</div>
	<div id="imapper-contacts">
		<div class="imapper-title">Contacts</div>
		<div id="imapper-contacts-list"></div>
	</div>
	<div id="imapper-messages">
		<div class="imapper-title">Messages</div>
    	{foreach from=$messages item=message}
        	<div class="imapper-message" id="imapper_message_{$message->uid}">
        		<div class="imapper-header">
            		<span class="imapper-message-toggle"></span>{$message->subject}
        		</div>
        		<div class="imapper-body"></div>
        	</div>
        {/foreach}
	</div>
	<div class="imapper-clear"></div>
</div>