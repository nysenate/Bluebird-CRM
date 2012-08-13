<div class="crm-content-block imapperbox">
	<div class='full'>
	<h1>Unmatched Results <small>5 results</small></h1>
	</div>
	<div class='full'>
	<ul class='datagrid'>
		<li class='list_header'>
			<span class='checkbox checkall'> <input type="checkbox" name="checkall" value="" /></span>
			<span class='name'>Name</span>
			<span class='email'>Email Address</span>
			<span class='subject'>Message Subject</span>
			<span class='date'>Date</span>
			<span class='controlls'>
				<span class='find_match'>Actions</span>
			</span>
		</li>
		<li class='entry id#'>
			<span class='checkbox'> <input type="checkbox" name="" value="" checked /></span>
			<span class='name'>Bob Barker</span>
			<span class='email'>Thepriceiswrong@gmail.com</span>
			<span class='subject'>Did you see my last message?</span>
			<span class='date'>08/03/12</span>
			<span class='controlls'>
				<span class='find_match'><a href='#'>Find match</a></span>
				<span class='delete'><a href='#'>Delete</a></span>
			</span>
			<br/>
			<span class='hidden details'>
				<span class='hidden_content'>
					<span class='name'>Matched Name</span>
					<span class='email'>Birthday </span>
					<span class='subject'>Address</span>
				</span>
			</span>
		</li>
		<li class='entry id#'>
			<span class='checkbox'> <input type="checkbox" name="" value="" /></span>
			<span class='name'>name</span>
			<span class='email'>email</span>
			<span class='subject'>A okay well there is no data dictionary for this shapefilethere is no data dictionary for this shapefile </span>
			<span class='date'>date</span>
			<span class='controlls'>
				<span class='find_match'><a href='#'>Find match</a></span>
				<span class='delete'><a href='#'>Delete</a></span>
			</span>
			 
		</li>
		<li class='entry id#'>
			<span class='checkbox'> <input type="checkbox" name="" value="" /></span>
			<span class='name'>name</span>
			<span class='email'>email</span>
			<span class='subject'>A okay well there is no data dictionary for this shapefilethere is no data dictionary for this shapefile </span>
			<span class='date'>date</span>
			<span class='controlls'>
				<span class='find_match'><a href='#'>Find match</a></span>
				<span class='delete'><a href='#'>Delete</a></span>
			</span>
			 
		</li>
		<li class='entry id#'>
			<span class='checkbox'> <input type="checkbox" name="" value="" /></span>
			<span class='name'>name</span>
			<span class='email'>email</span>
			<span class='subject'>A okay well there is no data dictionary for this shapefilethere is no data dictionary for this shapefile </span>
			<span class='date'>date</span>
			<span class='controlls'>
				<span class='find_match'><a href='#'>Find match</a></span>
				<span class='delete'><a href='#'>Delete</a></span>
			</span>
			 
		</li>

	</ul>
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